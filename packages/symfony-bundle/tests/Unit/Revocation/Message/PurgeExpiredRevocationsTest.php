<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Message;

use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PurgeExpiredRevocations::class)]
final class PurgeExpiredRevocationsTest extends TestCase
{
    #[Test]
    public function itCarriesTheCutoffAsAnRfc3339String(): void
    {
        $message = PurgeExpiredRevocations::fromDate(new DateTimeImmutable('2026-08-03T12:30:45Z'));

        self::assertSame('2026-08-03T12:30:45.000+00:00', $message->before);
    }

    #[Test]
    public function itRoundTripsTheCutoffInstant(): void
    {
        $before = new DateTimeImmutable('2026-08-03T12:30:45Z');

        self::assertEquals($before, PurgeExpiredRevocations::fromDate($before)->toDate());
    }

    #[Test]
    public function itRejectsABlankCutoffRatherThanPurgingUpToNow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"before" carries an empty date');

        (new PurgeExpiredRevocations(''))->toDate();
    }

    #[Test]
    public function itRejectsAnUnparseableCutoffNamingTheField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"before" is not a valid date');

        (new PurgeExpiredRevocations('whenever'))->toDate();
    }
}
