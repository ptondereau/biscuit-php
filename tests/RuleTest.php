<?php

namespace Biscuit\Tests;

use Biscuit\Auth\Rule;
use Biscuit\Exception\InvalidRule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        $rule = new Rule('right({test}, "read") <- resource({test}), operation("read")');
        $rule->set('test', 15);

        self::assertEquals(
            'right(15, "read") <- resource(15), operation("read")',
            (string) $rule,
        );
    }

    public function testExcpetionWhenBadRule(): void
    {
        $this->expectException(InvalidRule::class);
        $this->expectExceptionMessage('error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "", message: None }] }');

        new Rule('wrong');
    }
}
