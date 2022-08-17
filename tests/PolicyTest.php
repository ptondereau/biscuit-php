<?php

namespace Biscuit\Tests;

use Biscuit\Auth\Policy;
use Biscuit\Exception\InvalidPolicy;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        self::markTestSkipped();
        $policy = new Policy('allow if resource($test)');
        $policy->set('test', true);
    }

    public function testExcpetionWhenBadPolicy(): void
    {
        $this->expectException(InvalidPolicy::class);
        $this->expectExceptionMessage('error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "wrong", message: None }] }');

        new Policy('wrong');
    }
}
