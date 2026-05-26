<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Policy;
use Biscuit\Exception\PolicyException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testGoodPolicy(): void
    {
        $policy = new Policy('allow if resource({test})');
        $policy->set('test', true);

        static::assertSame('allow if resource(true)', (string) $policy);
    }

    #[Test]
    public function badPolicyThrowsPolicyException(): void
    {
        $this->expectException(PolicyException::class);
        $this->expectExceptionMessage('datalog parsing error');

        new Policy('wrong');
    }
}
