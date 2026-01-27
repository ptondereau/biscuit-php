<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Cache;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Cache\TokenCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(TokenCache::class)]
final class TokenCacheTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cachePool;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
    }

    #[Test]
    public function itReturnsNullWhenCacheMisses(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_token_abc123')
            ->willReturn($item);

        $cache = new TokenCache($this->cachePool);

        self::assertNull($cache->get('abc123'));
    }

    #[Test]
    public function itReturnsBiscuitWhenCacheHits(): void
    {
        $biscuit = $this->createMock(Biscuit::class);

        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn($biscuit);

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_token_abc123')
            ->willReturn($item);

        $cache = new TokenCache($this->cachePool);

        self::assertSame($biscuit, $cache->get('abc123'));
    }

    #[Test]
    public function itReturnsNullWhenCachedDataIsNotBiscuit(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn('not a biscuit');

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_token_abc123')
            ->willReturn($item);

        $cache = new TokenCache($this->cachePool);

        self::assertNull($cache->get('abc123'));
    }

    #[Test]
    public function itSetsBiscuitInCacheWithDefaultTtl(): void
    {
        $biscuit = $this->createMock(Biscuit::class);

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())
            ->method('set')
            ->with($biscuit);
        $item->expects(self::once())
            ->method('expiresAfter')
            ->with(300);

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_token_abc123')
            ->willReturn($item);

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->with($item);

        $cache = new TokenCache($this->cachePool);
        $cache->set('abc123', $biscuit);
    }

    #[Test]
    public function itSetsBiscuitInCacheWithCustomTtl(): void
    {
        $biscuit = $this->createMock(Biscuit::class);

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::once())
            ->method('set')
            ->with($biscuit);
        $item->expects(self::once())
            ->method('expiresAfter')
            ->with(600);

        $this->cachePool
            ->method('getItem')
            ->with('biscuit_token_abc123')
            ->willReturn($item);

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->with($item);

        $cache = new TokenCache($this->cachePool, 600);
        $cache->set('abc123', $biscuit);
    }

    #[Test]
    public function itDeletesTokenFromCache(): void
    {
        $this->cachePool
            ->expects(self::once())
            ->method('deleteItem')
            ->with('biscuit_token_abc123');

        $cache = new TokenCache($this->cachePool);
        $cache->delete('abc123');
    }

    #[Test]
    public function itCreatesProperCacheKey(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with('biscuit_token_my_special_hash_123')
            ->willReturn($item);

        $cache = new TokenCache($this->cachePool);
        $cache->get('my_special_hash_123');
    }
}
