<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Exception\AuthorizerError;
use PHPUnit\Framework\TestCase;

/**
 * Simplified RBAC Example for Easy Understanding
 *
 * This test shows a minimal, hardcoded RBAC implementation following the
 * Biscuit recipe pattern: https://doc.biscuitsec.org/recipes/role-based-access-control.html
 *
 * RBAC Pattern Explanation:
 * ========================
 *
 * The Biscuit recipe uses "priority" to represent priority levels for operations:
 * - "critical" = system-critical operations (migrations, config changes)
 * - "high" = important operations (user data modifications)
 * - "normal" = standard operations (regular CRUD)
 * - "low" = background operations (read-only, caching)
 *
 * This implementation adapts the pattern for inter-API microservices communication:
 * - Services have roles (service-admin, service-writer, service-reader)
 * - Roles grant permissions scoped by priority level
 * - A service can have different roles for different priority levels
 *
 * Example: payment-api might be admin for "critical" operations but reader for "low" operations
 */
class RbacExampleTest extends TestCase
{
    public function testAdminServiceCanPerformCriticalOperations(): void
    {
        $keyPair = new KeyPair();

        // 1. CREATE TOKEN: payment-api has service-admin role for critical priority
        $tokenBuilder = new BiscuitBuilder();
        $tokenBuilder->addCode('user({id})', ['id' => 'payment-api']);
        $tokenBuilder->addCode('user_roles({id}, "api", {priority}, {roles})', [
            'id' => 'payment-api',
            'priority' => 'critical',
            'roles' => ['service-admin'],
        ]);

        $token = $tokenBuilder->build($keyPair->getPrivateKey());

        // 2. VERIFY TOKEN: Check if payment-api can perform api:delete at critical priority
        $authBuilder = new AuthorizerBuilder();

        // Define what "service-admin" role can do at "critical" priority level
        $authBuilder->addCode('role({priority}, {role}, {permissions})', [
            'priority' => 'critical',
            'role' => 'service-admin',
            'permissions' => ['api:read', 'api:write', 'api:delete', 'api:admin'],
        ]);

        // RBAC Logic: Derive rights from user_roles + role definitions
        $authBuilder->addCode('
            right($user_id, $principal, $operation, $priority) <-
                user($user_id),
                operation($operation),
                resource($priority),
                user_roles($user_id, $principal, $priority, $roles),
                role($priority, $role, $permissions),
                $roles.contains($role),
                $permissions.contains($operation);

            allow if
                operation($op),
                resource($priority),
                right($id, $principal, $op, $priority);
        ');

        // Set the operation context: checking if service can "api:delete" at "critical" priority
        $authBuilder->addCode('operation("api:delete"); resource("critical");');

        $authorizer = $authBuilder->build($token);
        $authorizer->authorize();

        static::assertTrue(true, 'Admin service should be able to perform critical operations');
    }

    public function testWriterServiceCannotPerformCriticalOperations(): void
    {
        $keyPair = new KeyPair();

        // 1. CREATE TOKEN: notification-api has service-writer role for normal priority only
        $tokenBuilder = new BiscuitBuilder();
        $tokenBuilder->addCode('user({id})', ['id' => 'notification-api']);
        $tokenBuilder->addCode('user_roles({id}, "api", {priority}, {roles})', [
            'id' => 'notification-api',
            'priority' => 'normal',
            'roles' => ['service-writer'],
        ]);

        $token = $tokenBuilder->build($keyPair->getPrivateKey());

        // 2. VERIFY TOKEN: Check if notification-api can perform api:delete at critical priority
        $authBuilder = new AuthorizerBuilder();

        // Define what "service-writer" role can do at "normal" priority (no critical!)
        $authBuilder->addCode('role({priority}, {role}, {permissions})', [
            'priority' => 'normal',
            'role' => 'service-writer',
            'permissions' => ['api:read', 'api:write'],
        ]);

        // Same RBAC logic
        $authBuilder->addCode('
            right($user_id, $principal, $operation, $priority) <-
                user($user_id),
                operation($operation),
                resource($priority),
                user_roles($user_id, $principal, $priority, $roles),
                role($priority, $role, $permissions),
                $roles.contains($role),
                $permissions.contains($operation);

            allow if operation($op), resource($priority), right($id, $principal, $op, $priority);
        ');

        // Try to perform api:delete at critical priority (should fail!)
        $authBuilder->addCode('operation("api:delete"); resource("critical");');

        $authorizer = $authBuilder->build($token);

        // Authorization should fail because writer doesn't have critical priority role
        $this->expectException(AuthorizerError::class);
        $this->expectExceptionMessage('authorization failed');
        $authorizer->authorize();
    }

    public function testPriorityScopedRoles(): void
    {
        $keyPair = new KeyPair();

        // CREATE TOKEN: payment-api is admin for critical, writer for normal
        $tokenBuilder = new BiscuitBuilder();
        $tokenBuilder->addCode('user({id})', ['id' => 'payment-api']);

        // Admin for critical priority
        $tokenBuilder->addCode('user_roles({id}, "api", {priority}, {roles})', [
            'id' => 'payment-api',
            'priority' => 'critical',
            'roles' => ['service-admin'],
        ]);

        // Writer for normal priority
        $tokenBuilder->addCode('user_roles({id}, "api", {priority}, {roles})', [
            'id' => 'payment-api',
            'priority' => 'normal',
            'roles' => ['service-writer'],
        ]);

        $token = $tokenBuilder->build($keyPair->getPrivateKey());

        // TEST 1: payment-api CAN perform api:delete at critical priority (admin role)
        $authBuilder1 = new AuthorizerBuilder();
        $authBuilder1->addCode('role("critical", "service-admin", {perms})', ['perms' => [
            'api:read',
            'api:write',
            'api:delete',
            'api:admin',
        ]]);
        $authBuilder1->addCode('
            right($id, $p, $op, $priority) <-
                user($id), operation($op), resource($priority),
                user_roles($id, $p, $priority, $roles),
                role($priority, $role, $permissions),
                $roles.contains($role), $permissions.contains($op);
            allow if operation($op), resource($priority), right($id, $p, $op, $priority);
        ');
        $authBuilder1->addCode('operation("api:delete"); resource("critical");');

        $authBuilder1->build($token)->authorize();
        static::assertTrue(true, 'payment-api can perform api:delete at critical priority (admin)');

        // TEST 2: payment-api CANNOT perform api:delete at normal priority (only writer role)
        $authBuilder2 = new AuthorizerBuilder();
        $authBuilder2->addCode('role("normal", "service-writer", {perms})', ['perms' => ['api:read', 'api:write']]);
        $authBuilder2->addCode('
            right($id, $p, $op, $priority) <-
                user($id), operation($op), resource($priority),
                user_roles($id, $p, $priority, $roles),
                role($priority, $role, $permissions),
                $roles.contains($role), $permissions.contains($op);
            allow if operation($op), resource($priority), right($id, $p, $op, $priority);
        ');
        $authBuilder2->addCode('operation("api:delete"); resource("normal");');

        $this->expectException(AuthorizerError::class);
        $this->expectExceptionMessage('authorization failed');
        $authBuilder2->build($token)->authorize();
    }
}
