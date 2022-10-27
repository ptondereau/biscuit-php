<?php

namespace Biscuit\Tests;

use Biscuit\Auth\Policy;
use Biscuit\Exception\InvalidPolicy;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testGoodPolicy(): void
    {
        $policy = new Policy('allow if resource({test})');
        $policy->set('test', true);

        self::assertEquals(
            'allow if resource(true)',
            $policy->toString(),
        );
    }

    public function testExcpetionWhenBadPolicy(): void
    {
        $this->expectException(InvalidPolicy::class);
        $this->expectExceptionMessage('error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "wrong", message: None }] }');

        new Policy('wrong');
    }
}
