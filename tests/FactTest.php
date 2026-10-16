<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Bytes;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;
use Biscuit\Exception\FactException;
use Biscuit\Exception\TermException;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testTermsReadQueryResults(): void
    {
        $builder = new AuthorizerBuilder('user({name}, 42); allow if true;', ['name' => 'Doe, "J"']);
        $fact = $builder->buildUnauthenticated()->query(new Rule('u($n, $a) <- user($n, $a)'))[0];

        static::assertSame(['Doe, "J"', 42], $fact->terms());
    }

    public function testTermsConvertEachKindToPhp(): void
    {
        $fact = new Fact('f(42, true, "alice", hex:00ff, 2024-01-01T00:00:00Z, {"a", "b"})');

        [$integer, $boolean, $string, $bytes, $date, $set] = $fact->terms();

        static::assertSame(42, $integer);
        static::assertTrue($boolean);
        static::assertSame('alice', $string);
        static::assertInstanceOf(Bytes::class, $bytes);
        static::assertSame("\x00\xff", (string) $bytes);
        static::assertInstanceOf(DateTimeImmutable::class, $date);
        static::assertSame('2024-01-01T00:00:00+00:00', $date->format(DATE_ATOM));
        static::assertSame(['a', 'b'], $set);
    }

    public function testTermsRoundTripThroughParams(): void
    {
        $fact = new Fact('f(42, true, "alice", hex:0102, 2024-01-01T00:00:00Z, {1, 2})');
        $params = array_combine(['integer', 'boolean', 'string', 'bytes', 'date', 'set'], $fact->terms());

        $copy = new Fact('f({integer}, {boolean}, {string}, {bytes}, {date}, {set})', $params);

        static::assertSame((string) $fact, (string) $copy);
    }

    public function testDateTimeParamBindsDate(): void
    {
        $fact = new Fact('f({date})', ['date' => new DateTime('2024-01-01T01:00:00+01:00')]);

        static::assertSame('f(2024-01-01T00:00:00Z)', (string) $fact);
    }

    public function testBytesParamBindsBytes(): void
    {
        $fact = new Fact('f({bytes})', ['bytes' => new Bytes("\x01\x02\x03")]);

        static::assertSame('f(hex:010203)', (string) $fact);
    }

    public function testListOfSmallIntsParamBindsSet(): void
    {
        $fact = new Fact('f({set})', ['set' => [1, 2, 3]]);

        static::assertSame('f({1, 2, 3})', (string) $fact);
    }

    public function testEmptyListParamBindsEmptySet(): void
    {
        $fact = new Fact('f({set})', ['set' => []]);

        static::assertSame('f({,})', (string) $fact);
    }

    #[Test]
    public function dateBeforeEpochThrowsTermException(): void
    {
        $this->expectException(TermException::class);
        $this->expectExceptionMessage('date out of range');

        new Fact('f({date})', ['date' => new DateTimeImmutable('1969-12-31T23:59:59Z')]);
    }

    #[Test]
    #[DataProvider('unsupportedTerms')]
    public function unsupportedTermThrowsTermException(Fact $fact): void
    {
        $this->expectException(TermException::class);
        $this->expectExceptionMessage('unsupported term');

        $fact->terms();
    }

    public static function unsupportedTerms(): iterable
    {
        yield 'null' => [new Fact('f(null)')];
        yield 'array' => [new Fact('f([1, 2])')];
        yield 'map' => [new Fact('f({"a": 1})')];
        yield 'unbound parameter' => [new Fact('f({p})')];
    }
}
