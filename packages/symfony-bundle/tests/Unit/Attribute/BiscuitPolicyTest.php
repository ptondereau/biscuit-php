<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Attribute;

use Attribute;
use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(BiscuitPolicy::class)]
final class BiscuitPolicyTest extends TestCase
{
    #[Test]
    public function itStoresPolicyName(): void
    {
        $attribute = new BiscuitPolicy('admin_access');

        self::assertSame('admin_access', $attribute->policy);
    }

    #[Test]
    public function itHasEmptyParamsByDefault(): void
    {
        $attribute = new BiscuitPolicy('admin_access');

        self::assertSame([], $attribute->params);
    }

    #[Test]
    public function itStoresParams(): void
    {
        $params = ['resource' => 'article', 'action' => 'read'];
        $attribute = new BiscuitPolicy('resource_access', $params);

        self::assertSame($params, $attribute->params);
    }

    #[Test]
    public function itIsAnAttribute(): void
    {
        $reflection = new ReflectionClass(BiscuitPolicy::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        self::assertCount(1, $attributes);
    }

    #[Test]
    public function itCanTargetClassAndMethod(): void
    {
        $reflection = new ReflectionClass(BiscuitPolicy::class);
        $attributes = $reflection->getAttributes(Attribute::class);
        $instance = $attributes[0]->newInstance();

        $expectedFlags = Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE;
        self::assertSame($expectedFlags, $instance->flags);
    }

    #[Test]
    public function itIsRepeatable(): void
    {
        $reflection = new ReflectionClass(BiscuitPolicy::class);
        $attributes = $reflection->getAttributes(Attribute::class);
        $instance = $attributes[0]->newInstance();

        self::assertTrue(($instance->flags & Attribute::IS_REPEATABLE) !== 0);
    }
}
