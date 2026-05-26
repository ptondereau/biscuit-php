<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Fact;
use Biscuit\Exception\FactException;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function badFactThrowsFactException(): void
    {
        $this->expectException(FactException::class);
        $this->expectExceptionMessage('datalog parsing error');

        new Fact('wrong');
    }
}
