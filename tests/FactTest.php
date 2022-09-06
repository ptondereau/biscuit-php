<?php

namespace Biscuit\Tests;

use Biscuit\Auth\Fact;
use Biscuit\Exception\InvalidFact;
use PHPUnit\Framework\TestCase;

class FactTest extends TestCase
{
    public function testGoodTermConversion(): void
    {
        self::markTestSkipped();
        $fact = new Fact('allow if resource($test)');
        $fact->set('test', true);
    }

    public function testExcpetionWhenBadFact(): void
    {
        $this->expectException(InvalidFact::class);
        $this->expectExceptionMessage('error generating Datalog: datalog parsing error: ParseErrors { errors: [ParseError { input: "", message: None }] }');

        new Fact('wrong');
    }
}
