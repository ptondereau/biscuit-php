<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Message;

use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RevokeToken::class)]
final class RevokeTokenTest extends TestCase
{
    #[Test]
    public function itCarriesDatesAsRfc3339StringsRatherThanObjects(): void
    {
        $message = RevokeToken::fromEntry(new RevocationEntry(
            revocationId: 'abc',
            expiresAt: new DateTimeImmutable('2026-08-03T12:30:45Z'),
            revokedAt: new DateTimeImmutable('2026-08-01T09:00:00Z'),
        ));

        self::assertSame('2026-08-03T12:30:45.000+00:00', $message->expiresAt);
        self::assertSame('2026-08-01T09:00:00.000+00:00', $message->revokedAt);
    }

    #[Test]
    public function itRoundTripsEveryFieldOfAnEntry(): void
    {
        $entry = new RevocationEntry(
            revocationId: 'abc123',
            expiresAt: new DateTimeImmutable('2026-08-03T12:30:45Z'),
            revokedAt: new DateTimeImmutable('2026-08-01T09:00:00Z'),
            subject: 'alice',
            reason: 'logout',
            metadata: ['device' => 'phone', 'attempts' => 3, 'trusted' => false, 'note' => null],
        );

        $restored = RevokeToken::fromEntry($entry)->toEntry();

        self::assertSame('abc123', $restored->revocationId);
        self::assertEquals($entry->expiresAt, $restored->expiresAt);
        self::assertEquals($entry->revokedAt, $restored->revokedAt);
        self::assertSame('alice', $restored->subject);
        self::assertSame('logout', $restored->reason);
        self::assertSame(['device' => 'phone', 'attempts' => 3, 'trusted' => false, 'note' => null], $restored->metadata);
    }

    #[Test]
    public function itKeepsAMissingExpirationMissingInsteadOfInventingOne(): void
    {
        $message = RevokeToken::fromEntry(new RevocationEntry('abc'));

        self::assertNull($message->expiresAt);
        self::assertNull($message->toEntry()->expiresAt);
    }

    #[Test]
    public function itPreservesSubSecondPrecisionAcrossTheWire(): void
    {
        $expiresAt = new DateTimeImmutable('2026-08-03T12:30:45.123456Z');

        $restored = RevokeToken::fromEntry(new RevocationEntry('abc', $expiresAt))->toEntry();

        self::assertSame('2026-08-03T12:30:45.123', $restored->expiresAt?->format('Y-m-d\TH:i:s.v'));
    }

    #[Test]
    public function itKeepsANonUtcOffsetRatherThanShiftingTheInstant(): void
    {
        $expiresAt = new DateTimeImmutable('2026-08-03T14:30:45+02:00');

        $message = RevokeToken::fromEntry(new RevocationEntry('abc', $expiresAt));

        self::assertSame('2026-08-03T14:30:45.000+02:00', $message->expiresAt);
        self::assertSame($expiresAt->getTimestamp(), $message->toEntry()->expiresAt?->getTimestamp());
    }

    #[Test]
    public function itRejectsAnEmptyRevocationIdFromTheWire(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty revocation id');

        (new RevokeToken(''))->toEntry();
    }

    #[Test]
    public function itRejectsAnUnparseableExpirationNamingTheField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"expiresAt" is not a valid date');

        (new RevokeToken('abc', expiresAt: 'last tuesday-ish'))->toEntry();
    }

    #[Test]
    public function itRejectsABlankExpirationRatherThanReadingItAsNow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"expiresAt" carries an empty date');

        (new RevokeToken('abc', expiresAt: ''))->toEntry();
    }

    #[Test]
    public function itRejectsAnUnparseableRevocationDateNamingTheField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"revokedAt" is not a valid date');

        (new RevokeToken('abc', revokedAt: 'nope'))->toEntry();
    }
}
