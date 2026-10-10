<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayRevocationStore::class)]
final class ArrayRevocationStoreTest extends TestCase
{
    #[Test]
    public function itIsAnEnumerableStoreAndAWriter(): void
    {
        $store = new ArrayRevocationStore();

        self::assertInstanceOf(EnumerableRevocationStoreInterface::class, $store);
        self::assertInstanceOf(RevocationWriterInterface::class, $store);
    }

    #[Test]
    public function itReturnsNullWhenEmpty(): void
    {
        self::assertNull((new ArrayRevocationStore())->findRevoked(['abc']));
    }

    #[Test]
    public function itAcceptsInitialEntries(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('abc')]);

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itMatchesCaseInsensitivelyAndReturnsTheCallerSpelling(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('ABC')]);

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itFindsTheFirstMatchingIdInOrder(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('second'), new RevocationEntry('third')]);

        self::assertSame('second', $store->findRevoked(['first', 'second', 'third']));
    }

    #[Test]
    public function itRevokesAndUnrevokes(): void
    {
        $store = new ArrayRevocationStore();
        $store->revoke(new RevocationEntry('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']));

        $store->unrevoke('abc');

        self::assertNull($store->findRevoked(['abc']));
    }

    #[Test]
    public function itKeepsOneEntryPerIdWhenRevokedTwice(): void
    {
        $store = new ArrayRevocationStore();
        $store->revoke(new RevocationEntry('abc', reason: 'first'));
        $store->revoke(new RevocationEntry('abc', reason: 'second'));

        $entries = $this->entries($store);

        self::assertCount(1, $entries);
        self::assertSame('second', $entries[0]->reason);
    }

    #[Test]
    public function itEnumeratesEntries(): void
    {
        $store = new ArrayRevocationStore([
            new RevocationEntry('abc', subject: 'alice'),
            new RevocationEntry('def', subject: 'bob'),
        ]);

        $subjects = array_map(
            static fn (RevocationEntry $entry): ?string => $entry->subject,
            $this->entries($store),
        );

        self::assertSame(['alice', 'bob'], $subjects);
    }

    #[Test]
    public function itPurgesOnlyExpiredEntries(): void
    {
        $now = new DateTimeImmutable('2026-08-03T12:00:00Z');

        $store = new ArrayRevocationStore([
            new RevocationEntry('expired', new DateTimeImmutable('2026-08-02T12:00:00Z')),
            new RevocationEntry('active', new DateTimeImmutable('2026-08-04T12:00:00Z')),
            new RevocationEntry('forever'),
        ]);

        self::assertSame(1, $store->purgeExpired($now));
        self::assertNull($store->findRevoked(['expired']));
        self::assertSame('active', $store->findRevoked(['active']));
        self::assertSame('forever', $store->findRevoked(['forever']));
    }

    #[Test]
    public function itNeverPurgesEntriesWithoutAnExpiration(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('forever')]);

        self::assertSame(0, $store->purgeExpired(new DateTimeImmutable('2099-01-01T00:00:00Z')));
        self::assertSame('forever', $store->findRevoked(['forever']));
    }

    /**
     * @return list<RevocationEntry>
     */
    private function entries(EnumerableRevocationStoreInterface $store): array
    {
        $entries = [];

        foreach ($store->all() as $entry) {
            $entries[] = $entry;
        }

        return $entries;
    }
}
