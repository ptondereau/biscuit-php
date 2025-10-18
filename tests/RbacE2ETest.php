<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\{
    Authorizer,
    AuthorizerBuilder,
    Biscuit,
    BiscuitBuilder,
    KeyPair,
    Rule
};
use PHPUnit\Framework\TestCase;

/**
 * End-to-End RBAC Test
 *
 * This test demonstrates a complete Role-Based Access Control implementation
 * using Biscuit tokens, following the pattern from:
 * https://www.biscuitsec.org/docs/recipes/role-based-access-control/
 *
 * Scenario:
 * - Three users: alice (admin), bob (editor), charlie (viewer)
 * - Three roles with different permissions
 * - Two resources: posts and comments
 * - Operations: read, write, delete
 */
class RbacE2ETest extends TestCase
{
    private KeyPair $rootKeyPair;

    // Role definitions: role -> permissions mapping
    private array $roleMappings = [
        'admin' => ['read', 'write', 'delete'],
        'editor' => ['read', 'write'],
        'viewer' => ['read'],
    ];

    protected function setUp(): void
    {
        $this->rootKeyPair = new KeyPair();
    }

    /**
     * Test complete RBAC flow with multiple users, roles, and resources
     */
    public function testCompleteRbacScenario(): void
    {
        // Scenario 1: Admin user can read, write, and delete posts
        $aliceToken = $this->createUserToken('alice', ['admin']);

        $this->assertTrue(
            $this->authorize($aliceToken, 'alice', 'read', 'posts'),
            'Admin should be able to read posts'
        );
        $this->assertTrue(
            $this->authorize($aliceToken, 'alice', 'write', 'posts'),
            'Admin should be able to write posts'
        );
        $this->assertTrue(
            $this->authorize($aliceToken, 'alice', 'delete', 'posts'),
            'Admin should be able to delete posts'
        );

        // Scenario 2: Editor user can read and write, but NOT delete posts
        $bobToken = $this->createUserToken('bob', ['editor']);

        $this->assertTrue(
            $this->authorize($bobToken, 'bob', 'read', 'posts'),
            'Editor should be able to read posts'
        );
        $this->assertTrue(
            $this->authorize($bobToken, 'bob', 'write', 'posts'),
            'Editor should be able to write posts'
        );
        $this->assertFalse(
            $this->authorize($bobToken, 'bob', 'delete', 'posts'),
            'Editor should NOT be able to delete posts'
        );

        // Scenario 3: Viewer user can ONLY read posts
        $charlieToken = $this->createUserToken('charlie', ['viewer']);

        $this->assertTrue(
            $this->authorize($charlieToken, 'charlie', 'read', 'posts'),
            'Viewer should be able to read posts'
        );
        $this->assertFalse(
            $this->authorize($charlieToken, 'charlie', 'write', 'posts'),
            'Viewer should NOT be able to write posts'
        );
        $this->assertFalse(
            $this->authorize($charlieToken, 'charlie', 'delete', 'posts'),
            'Viewer should NOT be able to delete posts'
        );
    }

    /**
     * Test multi-role user (user with both admin and editor roles)
     */
    public function testUserWithMultipleRoles(): void
    {
        // User with both admin and editor roles should have admin permissions
        $token = $this->createUserToken('alice', ['admin', 'editor']);

        $this->assertTrue(
            $this->authorize($token, 'alice', 'delete', 'posts'),
            'User with multiple roles should have highest permission level'
        );
    }

    /**
     * Test resource-specific permissions
     */
    public function testResourceSpecificPermissions(): void
    {
        // Create user with editor role for posts, viewer role for comments
        $token = $this->createUserTokenWithResourceRoles(
            'bob',
            [
                'posts' => ['editor'],
                'comments' => ['viewer'],
            ]
        );

        // Should be able to write posts
        $this->assertTrue(
            $this->authorize($token, 'bob', 'write', 'posts'),
            'User should be able to write posts with editor role'
        );

        // Should NOT be able to write comments (only viewer)
        $this->assertFalse(
            $this->authorize($token, 'bob', 'write', 'comments'),
            'User should NOT be able to write comments with only viewer role'
        );

        // Should be able to read both
        $this->assertTrue(
            $this->authorize($token, 'bob', 'read', 'posts'),
            'User should be able to read posts'
        );
        $this->assertTrue(
            $this->authorize($token, 'bob', 'read', 'comments'),
            'User should be able to read comments'
        );
    }

    /**
     * Test querying user permissions
     */
    public function testQueryUserPermissions(): void
    {
        $token = $this->createUserToken('alice', ['admin', 'editor']);

        $biscuit = Biscuit::fromBase64($token, $this->rootKeyPair->public());

        $authBuilder = new AuthorizerBuilder();

        // Add role definitions
        $this->addRoleDefinitions($authBuilder);

        // Add RBAC authorization rules
        $this->addRbacRules($authBuilder);

        // Query all permissions for this user
        $authBuilder->addCode('operation("read"); resource("posts");');
        $authorizer = $authBuilder->build($biscuit);

        // Query what operations are allowed
        $rule = new Rule('allowed_operation($op) <- right($id, $principal, $op, $priority)');
        $facts = $authorizer->query($rule);

        $this->assertGreaterThan(0, count($facts), 'User should have at least one permission');

        // Verify we can extract the allowed operations
        $operations = array_map(fn($fact) => $fact->name(), $facts);
        $this->assertContains('allowed_operation', $operations);
    }

    /**
     * Test authorization with attenuated token (restricted permissions)
     */
    public function testAttenuatedToken(): void
    {
        // Create admin token
        $adminToken = $this->createUserToken('alice', ['admin']);

        // Verify admin can delete
        $this->assertTrue(
            $this->authorize($adminToken, 'alice', 'delete', 'posts'),
            'Admin should be able to delete before attenuation'
        );

        // Attenuate token: restrict to read-only operations
        $readOnlyToken = $this->attenuateToReadOnly($adminToken);

        // Verify attenuated token can still read
        $this->assertTrue(
            $this->authorize($readOnlyToken, 'alice', 'read', 'posts'),
            'Attenuated token should still allow read'
        );

        // Verify attenuated token CANNOT delete anymore
        $this->assertFalse(
            $this->authorize($readOnlyToken, 'alice', 'delete', 'posts'),
            'Attenuated token should NOT allow delete'
        );
    }

    /**
     * Test unauthorized access (user with no roles)
     */
    public function testUnauthorizedAccess(): void
    {
        $token = $this->createUserToken('guest', []); // No roles

        $this->assertFalse(
            $this->authorize($token, 'guest', 'read', 'posts'),
            'User with no roles should not have any permissions'
        );
    }

    /**
     * Create a biscuit token for a user with specified roles
     */
    private function createUserToken(string $userId, array $roles): string
    {
        $builder = new BiscuitBuilder();

        // Add user fact
        $builder->addCodeWithParams('user({id})', ['id' => $userId], []);

        // user_roles(user_id, principal, priority, roles)
        foreach (['posts', 'comments', 'files', 'default'] as $resourceType) {
            $builder->addCodeWithParams(
                'user_roles({id}, "api", {resource}, {roles})',
                [
                    'id' => $userId,
                    'resource' => $resourceType,
                    'roles' => $roles,
                ],
                []
            );
        }

        $biscuit = $builder->build($this->rootKeyPair->private());

        return $biscuit->toBase64();
    }

    /**
     * Create a token with resource-specific roles
     */
    private function createUserTokenWithResourceRoles(string $userId, array $resourceRoles): string
    {
        $builder = new BiscuitBuilder();

        $builder->addCodeWithParams('user({id})', ['id' => $userId], []);

        foreach ($resourceRoles as $resource => $roles) {
            $builder->addCodeWithParams(
                'user_roles({id}, "api", {resource}, {roles})',
                [
                    'id' => $userId,
                    'resource' => $resource,
                    'roles' => $roles,
                ],
                []
            );
        }

        $biscuit = $builder->build($this->rootKeyPair->private());

        return $biscuit->toBase64();
    }

    /**
     * Attenuate token to read-only access
     */
    private function attenuateToReadOnly(string $tokenBase64): string
    {
        $biscuit = Biscuit::fromBase64($tokenBase64, $this->rootKeyPair->public());

        // Add a block that restricts operations to read-only
        $block = new \Biscuit\Auth\BlockBuilder();
        $block->addCode('check if operation("read")');

        $attenuatedBiscuit = $biscuit->append($block);

        return $attenuatedBiscuit->toBase64();
    }

    /**
     * Authorize a user operation on a resource using RBAC rules
     *
     * This implements the RBAC pattern from the Biscuit recipe:
     * https://www.biscuitsec.org/docs/recipes/role-based-access-control/
     */
    private function authorize(
        string $tokenBase64,
        string $userId,
        string $operation,
        string $resource
    ): bool {
        try {
            // Parse and verify token
            $biscuit = Biscuit::fromBase64($tokenBase64, $this->rootKeyPair->public());

            // Build authorizer with RBAC rules
            $authBuilder = new AuthorizerBuilder();

            // Add role definitions for the requested resource
            // (role definitions map permissions for each resource type)
            $this->addRoleDefinitions($authBuilder, $resource);

            // Add RBAC authorization logic (from Biscuit recipe)
            $this->addRbacRules($authBuilder);

            // Add facts for current operation
            $authBuilder->addCodeWithParams(
                'operation({op}); resource({res});',
                [
                    'op' => $operation,
                    'res' => $resource,
                ],
                []
            );

            // Build authorizer and check authorization
            $authorizer = $authBuilder->build($biscuit);
            $policy = $authorizer->authorize();

            // Policy 0 = allow
            return $policy === 0;

        } catch (\Exception $e) {
            // Authorization failed
            return false;
        }
    }

    /**
     * Add role definitions to authorizer
     *
     * Defines: role(priority, role_name, [permissions])
     * Note: We add role definitions for all possible resource types
     */
    private function addRoleDefinitions(AuthorizerBuilder $authBuilder, ?string $resourceType = null): void
    {
        $resources = $resourceType ? [$resourceType] : ['default', 'posts', 'comments'];

        foreach ($resources as $resource) {
            foreach ($this->roleMappings as $role => $permissions) {
                $authBuilder->addCodeWithParams(
                    'role({resource}, {role}, {permissions})',
                    [
                        'resource' => $resource,
                        'role' => $role,
                        'permissions' => $permissions,
                    ],
                    []
                );
            }
        }
    }

    /**
     * Add RBAC authorization rules
     *
     * This is the core RBAC logic from the Biscuit recipe:
     *
     * 1. Derive rights: user + role + permissions -> right(user, principal, operation, resource)
     * 2. Allow policy: if user has right for operation on resource
     */
    private function addRbacRules(AuthorizerBuilder $authBuilder): void
    {
        // RBAC Rule: Derive user rights from roles and permissions
        //
        // right(user_id, principal, operation, priority) is derived when:
        // - user(user_id) exists
        // - operation(operation) is requested
        // - resource(priority) is being accessed
        // - user_roles(user_id, principal, priority, roles) defines user's roles
        // - role(priority, role, permissions) defines role's permissions
        // - user has the role (roles.contains(role))
        // - role grants the permission (permissions.contains(operation))
        $authBuilder->addCode('
            right($id, $principal, $operation, $priority) <-
                user($id),
                operation($operation),
                resource($priority),
                user_roles($id, $principal, $priority, $roles),
                role($priority, $role, $permissions),
                $roles.contains($role),
                $permissions.contains($operation);
        ');

        // Authorization Policy: Allow if user has right for operation on resource
        $authBuilder->addCode('
            allow if
                operation($op),
                resource($priority),
                right($id, $principal, $op, $priority);
        ');
    }
}
