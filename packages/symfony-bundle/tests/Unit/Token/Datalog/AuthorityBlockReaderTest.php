<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Datalog;

use Biscuit\BiscuitBundle\Token\Datalog\AuthorityBlockReader;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorityBlockReader::class)]
final class AuthorityBlockReaderTest extends TestCase
{
    private AuthorityBlockReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AuthorityBlockReader();
    }

    #[Test]
    public function itReadsAStringFact(): void
    {
        self::assertSame('alice', $this->reader->readFact('user("alice");', 'user'));
    }

    #[Test]
    public function itReadsAnIntegerFact(): void
    {
        self::assertSame('42', $this->reader->readFact('user(42);', 'user'));
    }

    #[Test]
    public function itPrefersTheStringFormWhenBothArePresent(): void
    {
        $source = 'user(42);' . "\n" . 'user("alice");';

        self::assertSame('alice', $this->reader->readFact($source, 'user'));
    }

    #[Test]
    public function itReturnsNullWhenTheFactIsAbsent(): void
    {
        self::assertNull($this->reader->readFact('right("read");', 'user'));
    }

    #[Test]
    public function itDoesNotConfuseAFactWithASimilarName(): void
    {
        self::assertNull($this->reader->readFact('service("api");', 'user'));
    }

    #[Test]
    public function itReadsAnExpiryFromATimeCheck(): void
    {
        $source = 'check if time($time), $time <= 2026-08-03T12:00:00Z;';

        $expiry = $this->reader->readExpiry($source);

        self::assertNotNull($expiry);
        self::assertSame('2026-08-03T12:00:00+00:00', $expiry->format(DateTimeImmutable::ATOM));
    }

    #[Test]
    public function itReadsAnExpiryWithAnOffsetAndStrictComparison(): void
    {
        $source = 'check if time($date), $date < 2026-08-03T12:00:00+02:00;';

        $expiry = $this->reader->readExpiry($source);

        self::assertNotNull($expiry);
        self::assertSame('2026-08-03T10:00:00+00:00', $expiry->setTimezone(new DateTimeZone('UTC'))->format(DateTimeImmutable::ATOM));
    }

    #[Test]
    public function itReturnsTheEarliestExpiryWhenSeveralAreDeclared(): void
    {
        $source = <<<'DATALOG'
            check if time($time), $time <= 2027-01-01T00:00:00Z;
            check if time($time), $time <= 2026-08-03T12:00:00Z;
            DATALOG;

        $expiry = $this->reader->readExpiry($source);

        self::assertNotNull($expiry);
        self::assertSame('2026-08-03T12:00:00+00:00', $expiry->format(DateTimeImmutable::ATOM));
    }

    #[Test]
    public function itReturnsNullWhenTheBlockSetsNoDeadline(): void
    {
        self::assertNull($this->reader->readExpiry('user("alice");' . "\n" . 'right("read");'));
    }

    #[Test]
    public function itIgnoresATimeCheckWithoutADateLiteral(): void
    {
        self::assertNull($this->reader->readExpiry('check if time($time), $time <= $deadline;'));
    }
}
