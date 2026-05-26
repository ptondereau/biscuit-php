<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Rule;
use Biscuit\Exception\RuleException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        $rule = new Rule('right({test}, "read") <- resource({test}), operation("read")');
        $rule->set('test', 15);

        static::assertSame('right(15, "read") <- resource(15), operation("read")', (string) $rule);
    }

    #[Test]
    public function badRuleThrowsRuleException(): void
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage('datalog parsing error');

        new Rule('wrong');
    }
}
