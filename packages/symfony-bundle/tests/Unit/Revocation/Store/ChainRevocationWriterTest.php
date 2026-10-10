<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Event\BiscuitTokenRevokedEvent;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(ChainRevocationWriter::class)]
final class ChainRevocationWriterTest extends TestCase
{
    #[Test]
    public function itWritesToEveryStore(): void
    {
        $first = new ArrayRevocationStore();
        $second = new ArrayRevocationStore();

        $writer = new ChainRevocationWriter(['first' => $first, 'second' => $second]);
        $writer->revoke(new RevocationEntry('abc'));

        self::assertSame('abc', $first->findRevoked(['abc']));
        self::assertSame('abc', $second->findRevoked(['abc']));
    }

    #[Test]
    public function itUnrevokesFromEveryStore(): void
    {
        $first = new ArrayRevocationStore([new RevocationEntry('abc')]);
        $second = new ArrayRevocationStore([new RevocationEntry('abc')]);

        $writer = new ChainRevocationWriter(['first' => $first, 'second' => $second]);
        $writer->unrevoke('abc');

        self::assertNull($first->findRevoked(['abc']));
        self::assertNull($second->findRevoked(['abc']));
    }

    #[Test]
    public function itSumsPurgedCountsAcrossStores(): void
    {
        $now = new DateTimeImmutable('2026-08-03T12:00:00Z');
        $expired = new DateTimeImmutable('2026-08-01T12:00:00Z');

        $first = new ArrayRevocationStore([new RevocationEntry('abc', $expired)]);
        $second = new ArrayRevocationStore([
            new RevocationEntry('def', $expired),
            new RevocationEntry('ghi', $expired),
        ]);

        $writer = new ChainRevocationWriter(['first' => $first, 'second' => $second]);

        self::assertSame(3, $writer->purgeExpired($now));
    }

    #[Test]
    public function itStillWritesToHealthyStoresWhenOneFails(): void
    {
        $healthy = new ArrayRevocationStore();

        $writer = new ChainRevocationWriter([
            'broken' => $this->failingWriter(),
            'healthy' => $healthy,
        ]);

        try {
            $writer->revoke(new RevocationEntry('abc'));
            self::fail('Expected the failing store to surface.');
        } catch (RevocationStoreUnavailableException) {
        }

        self::assertSame('abc', $healthy->findRevoked(['abc']));
    }

    #[Test]
    public function itDoesNotDispatchWhenAStoreFailed(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $writer = new ChainRevocationWriter(['broken' => $this->failingWriter()], $eventDispatcher);

        $this->expectException(RevocationStoreUnavailableException::class);

        $writer->revoke(new RevocationEntry('abc'));
    }

    #[Test]
    public function itDispatchesARevokedEventOnSuccess(): void
    {
        $entry = new RevocationEntry('abc');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (object $event): bool => $event instanceof BiscuitTokenRevokedEvent
                && $event->entry === $entry))
            ->willReturnArgument(0);

        $writer = new ChainRevocationWriter(['store' => new ArrayRevocationStore()], $eventDispatcher);
        $writer->revoke($entry);
    }

    #[Test]
    public function itWorksWithoutAnEventDispatcher(): void
    {
        $store = new ArrayRevocationStore();

        $writer = new ChainRevocationWriter(['store' => $store]);
        $writer->revoke(new RevocationEntry('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    private function failingWriter(): RevocationWriterInterface
    {
        $writer = $this->createMock(RevocationWriterInterface::class);
        $writer->method('revoke')->willThrowException(new RevocationStoreUnavailableException('down'));
        $writer->method('unrevoke')->willThrowException(new RevocationStoreUnavailableException('down'));
        $writer->method('purgeExpired')->willThrowException(new RevocationStoreUnavailableException('down'));

        return $writer;
    }
}
