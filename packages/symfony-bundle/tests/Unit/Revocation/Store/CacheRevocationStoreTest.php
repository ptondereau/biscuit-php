<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\CacheRevocationStore;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(CacheRevocationStore::class)]
final class CacheRevocationStoreTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cachePool;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
    }

    #[Test]
    public function itIsAStoreAndAWriter(): void
    {
        $store = new CacheRevocationStore($this->cachePool);

        self::assertInstanceOf(RevocationStoreInterface::class, $store);
        self::assertInstanceOf(RevocationWriterInterface::class, $store);
    }

    #[Test]
    public function itReturnsNullWhenNoRevocationIdIsRevoked(): void
    {
        $this->cachePool
            ->method('getItems')
            ->willReturn($this->items(['id1' => false, 'id2' => false, 'id3' => false]));

        $store = new CacheRevocationStore($this->cachePool);

        self::assertNull($store->findRevoked(['id1', 'id2', 'id3']));
    }

    #[Test]
    public function itReturnsTheRevokedId(): void
    {
        $this->cachePool
            ->method('getItems')
            ->willReturn($this->items(['id1' => false, 'id2' => true, 'id3' => false]));

        $store = new CacheRevocationStore($this->cachePool);

        self::assertSame('id2', $store->findRevoked(['id1', 'id2', 'id3']));
    }

    #[Test]
    public function itReturnsNullWithoutTouchingThePoolWhenThereAreNoIds(): void
    {
        $this->cachePool->expects(self::never())->method('getItems');

        $store = new CacheRevocationStore($this->cachePool);

        self::assertNull($store->findRevoked([]));
    }

    #[Test]
    public function itFetchesEveryIdInASingleCall(): void
    {
        $this->cachePool
            ->expects(self::once())
            ->method('getItems')
            ->with([
                'biscuit_revoked_first',
                'biscuit_revoked_second',
                'biscuit_revoked_third',
            ])
            ->willReturn($this->items(['first' => false, 'second' => false, 'third' => false]));

        $store = new CacheRevocationStore($this->cachePool);
        $store->findRevoked(['first', 'second', 'third']);
    }

    #[Test]
    public function itHonoursAConfiguredKeyPrefix(): void
    {
        $this->cachePool
            ->expects(self::once())
            ->method('getItems')
            ->with(['tenant_a:abc'])
            ->willReturn($this->items(['abc' => false], 'tenant_a:'));

        $store = new CacheRevocationStore($this->cachePool, 'tenant_a:');
        $store->findRevoked(['abc']);
    }

    #[Test]
    public function itRevokesWithoutExpiryWhenNoneIsKnown(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())->method('set')->with(true);
        $item->expects(self::never())->method('expiresAfter');
        $item->expects(self::never())->method('expiresAt');

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_revoked_abc')
            ->willReturn($item);

        $this->cachePool->expects(self::once())->method('save')->with($item);

        $store = new CacheRevocationStore($this->cachePool);
        $store->revoke(new RevocationEntry('abc'));
    }

    #[Test]
    public function itPrefersTheEntryExpirationOverTheDefaultTtl(): void
    {
        $expiresAt = new DateTimeImmutable('2026-08-03T12:00:00Z');

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())->method('expiresAt')->with($expiresAt);
        $item->expects(self::never())->method('expiresAfter');

        $this->cachePool->method('getItem')->willReturn($item);

        $store = new CacheRevocationStore($this->cachePool, 'biscuit_revoked_', 3600);
        $store->revoke(new RevocationEntry('abc', $expiresAt));
    }

    #[Test]
    public function itFallsBackToTheDefaultTtlWhenTheEntryHasNoExpiration(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())->method('expiresAfter')->with(3600);
        $item->expects(self::never())->method('expiresAt');

        $this->cachePool->method('getItem')->willReturn($item);

        $store = new CacheRevocationStore($this->cachePool, 'biscuit_revoked_', 3600);
        $store->revoke(new RevocationEntry('abc'));
    }

    #[Test]
    public function itUnrevokesAnId(): void
    {
        $this->cachePool
            ->expects(self::once())
            ->method('deleteItem')
            ->with('biscuit_revoked_abc');

        $store = new CacheRevocationStore($this->cachePool);
        $store->unrevoke('abc');
    }

    #[Test]
    public function itPurgesNothingBecauseThePoolExpiresItemsItself(): void
    {
        $store = new CacheRevocationStore($this->cachePool);

        self::assertSame(0, $store->purgeExpired());
    }

    #[Test]
    public function itReportsAnUnusablePoolAsStoreUnavailable(): void
    {
        $this->cachePool
            ->method('getItems')
            ->willThrowException(new class('bad key') extends InvalidArgumentException implements \Psr\Cache\InvalidArgumentException {});

        $store = new CacheRevocationStore($this->cachePool);

        $this->expectException(RevocationStoreUnavailableException::class);

        $store->findRevoked(['abc']);
    }

    /**
     * @param array<string, bool> $hits keyed by revocation id
     *
     * @return array<string, CacheItemInterface>
     */
    private function items(array $hits, string $prefix = 'biscuit_revoked_'): array
    {
        $items = [];

        foreach ($hits as $revocationId => $isHit) {
            $item = $this->createMock(CacheItemInterface::class);
            $item->method('isHit')->willReturn($isHit);

            $items[$prefix . $revocationId] = $item;
        }

        return $items;
    }
}
