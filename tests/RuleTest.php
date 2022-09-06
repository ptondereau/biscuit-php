<?php

namespace Biscuit\Tests;

use Biscuit\Auth\Rule;
use Biscuit\Exception\InvalidRule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        self::markTestSkipped();
        $rule = new Rule('allow if resource($test)');
        $rule->set('test', true);
    }

    public function testExcpetionWhenBadRule(): void
    {
        $this->expectException(InvalidRule::class);
        $this->expectExceptionMessage('error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "", message: None }] }');

        new Rule('wrong');
    }
}
