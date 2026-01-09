<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Fact;
use Biscuit\Exception\InvalidFact;
use PHPUnit\Framework\TestCase;

class FactTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        $fact = new Fact('user({id})');
        $fact->set('id', 15);

        static::assertSame('user(15)', (string) $fact);
    }

    public function testConstructorWithParams(): void
    {
        $fact = new Fact('user({id})', ['id' => 'alice']);
        static::assertSame('user("alice")', (string) $fact);
    }

    public function testConstructorWithoutParams(): void
    {
        $fact = new Fact('user("bob")');
        static::assertSame('user("bob")', (string) $fact);
    }

    public function testExcpetionWhenBadFact(): void
    {
        $this->expectException(InvalidFact::class);
        $this->expectExceptionMessage(
            'error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "", message: None }] }',
        );

        new Fact('wrong');
    }
}
