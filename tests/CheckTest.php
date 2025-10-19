<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Check;
use Biscuit\Exception\InvalidCheck;
use PHPUnit\Framework\TestCase;

class CheckTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        $check = new Check('check if resource({id}), operation("read") or admin("authority")');
        $check->set('id', 'uuid');

        static::assertSame('check if resource("uuid"), operation("read") or admin("authority")', (string) $check);
    }

    public function testExcpetionWhenBadCheck(): void
    {
        $this->expectException(InvalidCheck::class);
        $this->expectExceptionMessage(
            'error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "wrong", message: None }] }',
        );

        new Check('wrong');
    }
}
