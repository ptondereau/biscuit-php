<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Policy;

use Biscuit\Auth\Policy;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PolicyRegistry::class)]
final class PolicyRegistryTest extends TestCase
{
    #[Test]
    public function itReturnsTrueForRegisteredPolicies(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if user($id), role($id, "admin")',
        ]);

        self::assertTrue($registry->has('admin_access'));
    }

    #[Test]
    public function itReturnsTrueForInlinePoliciesStartingWithAllow(): void
    {
        $registry = new PolicyRegistry();

        self::assertTrue($registry->has('allow if true'));
    }

    #[Test]
    public function itReturnsTrueForInlinePoliciesStartingWithDeny(): void
    {
        $registry = new PolicyRegistry();

        self::assertTrue($registry->has('deny if false'));
    }

    #[Test]
    public function itReturnsFalseForUnknownPolicies(): void
    {
        $registry = new PolicyRegistry();

        self::assertFalse($registry->has('unknown_policy'));
    }

    #[Test]
    public function itReturnsPolicyWithCorrectString(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if user($id), role($id, "admin")',
        ]);

        $policy = $registry->get('admin_access');

        self::assertInstanceOf(Policy::class, $policy);
    }

    #[Test]
    public function itReturnsInlinePolicyWhenNotRegistered(): void
    {
        $registry = new PolicyRegistry();
        $inlinePolicy = 'allow if resource("article"), operation("read")';

        $policy = $registry->get($inlinePolicy);

        self::assertInstanceOf(Policy::class, $policy);
    }

    #[Test]
    public function itPassesParamsToPolicyConstructor(): void
    {
        $registry = new PolicyRegistry([
            'resource_access' => 'allow if resource({resource}), operation({action})',
        ]);

        $params = ['resource' => 'article', 'action' => 'read'];
        $policy = $registry->get('resource_access', $params);

        self::assertInstanceOf(Policy::class, $policy);
    }

    #[Test]
    public function itAddsNewPolicy(): void
    {
        $registry = new PolicyRegistry();

        self::assertFalse($registry->has('new_policy'));

        $registry->add('new_policy', 'allow if true');

        self::assertTrue($registry->has('new_policy'));
    }

    #[Test]
    public function itReturnsAddedPolicyWhenGet(): void
    {
        $registry = new PolicyRegistry();
        $registry->add('new_policy', 'allow if user($id)');

        $policy = $registry->get('new_policy');

        self::assertInstanceOf(Policy::class, $policy);
    }

    #[Test]
    public function itReturnsAllRegisteredPolicies(): void
    {
        $policies = [
            'admin_access' => 'allow if role("admin")',
            'user_access' => 'allow if role("user")',
        ];
        $registry = new PolicyRegistry($policies);

        self::assertSame($policies, $registry->all());
    }

    #[Test]
    public function itReturnsEmptyArrayWhenNoPoliciesRegistered(): void
    {
        $registry = new PolicyRegistry();

        self::assertSame([], $registry->all());
    }

    #[Test]
    public function itIncludesAddedPoliciesInAll(): void
    {
        $registry = new PolicyRegistry([
            'existing' => 'allow if true',
        ]);
        $registry->add('new_policy', 'deny if false');

        $all = $registry->all();

        self::assertArrayHasKey('existing', $all);
        self::assertArrayHasKey('new_policy', $all);
        self::assertSame('allow if true', $all['existing']);
        self::assertSame('deny if false', $all['new_policy']);
    }
}
