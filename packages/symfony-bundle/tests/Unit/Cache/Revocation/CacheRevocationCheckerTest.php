<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Cache\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Cache\Revocation\CacheRevocationChecker;
use Biscuit\BiscuitBundle\Cache\Revocation\RevocationCheckerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(CacheRevocationChecker::class)]
final class CacheRevocationCheckerTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cachePool;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
    }

    #[Test]
    public function itImplementsRevocationCheckerInterface(): void
    {
        $checker = new CacheRevocationChecker($this->cachePool);

        self::assertInstanceOf(RevocationCheckerInterface::class, $checker);
    }

    #[Test]
    public function itReturnsFalseWhenNoRevocationIdsAreRevoked(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn(['id1', 'id2', 'id3']);

        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $this->cachePool
            ->method('getItem')
            ->willReturn($item);

        $checker = new CacheRevocationChecker($this->cachePool);

        self::assertFalse($checker->isRevoked($biscuit));
    }

    #[Test]
    public function itReturnsTrueWhenAnyRevocationIdIsRevoked(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn(['id1', 'id2', 'id3']);

        $notRevokedItem = $this->createMock(CacheItemInterface::class);
        $notRevokedItem->method('isHit')->willReturn(false);

        $revokedItem = $this->createMock(CacheItemInterface::class);
        $revokedItem->method('isHit')->willReturn(true);

        $this->cachePool
            ->method('getItem')
            ->willReturnCallback(function (string $key) use ($notRevokedItem, $revokedItem): CacheItemInterface {
                if ('biscuit_revoked_id2' === $key) {
                    return $revokedItem;
                }

                return $notRevokedItem;
            });

        $checker = new CacheRevocationChecker($this->cachePool);

        self::assertTrue($checker->isRevoked($biscuit));
    }

    #[Test]
    public function itReturnsFalseWhenBiscuitHasNoRevocationIds(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn([]);

        $checker = new CacheRevocationChecker($this->cachePool);

        self::assertFalse($checker->isRevoked($biscuit));
    }

    #[Test]
    public function itRevokesIdWithoutTtl(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())
            ->method('set')
            ->with(true);
        $item->expects(self::never())
            ->method('expiresAfter');

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_revoked_revocation_id_123')
            ->willReturn($item);

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->with($item);

        $checker = new CacheRevocationChecker($this->cachePool);
        $checker->revoke('revocation_id_123');
    }

    #[Test]
    public function itRevokesIdWithTtl(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())
            ->method('set')
            ->with(true);
        $item->expects(self::once())
            ->method('expiresAfter')
            ->with(3600);

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_revoked_revocation_id_123')
            ->willReturn($item);

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->with($item);

        $checker = new CacheRevocationChecker($this->cachePool);
        $checker->revoke('revocation_id_123', 3600);
    }

    #[Test]
    public function itUnrevokesId(): void
    {
        $this->cachePool
            ->expects(self::once())
            ->method('deleteItem')
            ->with('biscuit_revoked_revocation_id_123');

        $checker = new CacheRevocationChecker($this->cachePool);
        $checker->unrevoke('revocation_id_123');
    }

    #[Test]
    public function itChecksRevocationIdsInOrder(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn(['first', 'second', 'third']);

        $checkedKeys = [];

        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $this->cachePool
            ->method('getItem')
            ->willReturnCallback(function (string $key) use ($item, &$checkedKeys): CacheItemInterface {
                $checkedKeys[] = $key;

                return $item;
            });

        $checker = new CacheRevocationChecker($this->cachePool);
        $checker->isRevoked($biscuit);

        self::assertSame([
            'biscuit_revoked_first',
            'biscuit_revoked_second',
            'biscuit_revoked_third',
        ], $checkedKeys);
    }

    #[Test]
    public function itStopsCheckingAfterFirstRevokedId(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn(['first', 'second', 'third']);

        $checkedKeys = [];

        $revokedItem = $this->createMock(CacheItemInterface::class);
        $revokedItem->method('isHit')->willReturn(true);

        $this->cachePool
            ->method('getItem')
            ->willReturnCallback(function (string $key) use ($revokedItem, &$checkedKeys): CacheItemInterface {
                $checkedKeys[] = $key;

                return $revokedItem;
            });

        $checker = new CacheRevocationChecker($this->cachePool);
        $result = $checker->isRevoked($biscuit);

        self::assertTrue($result);
        self::assertSame(['biscuit_revoked_first'], $checkedKeys);
    }
}
