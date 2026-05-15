<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Check;
use Biscuit\Exception\CheckException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CheckTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        $check = new Check('check if resource({id}), operation("read") or admin("authority")');
        $check->set('id', 'uuid');

        static::assertSame('check if resource("uuid"), operation("read") or admin("authority")', (string) $check);
    }

    #[Test]
    public function badCheckThrowsCheckException(): void
    {
        $this->expectException(CheckException::class);
        $this->expectExceptionMessage('datalog parsing error');

        new Check('wrong');
    }
}
