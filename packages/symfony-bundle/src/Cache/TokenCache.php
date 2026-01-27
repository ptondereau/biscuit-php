<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Cache;

use Biscuit\Auth\Biscuit;
use Psr\Cache\CacheItemPoolInterface;

final class TokenCache
{
    public function __construct(
        private readonly CacheItemPoolInterface $cachePool,
        private readonly int $ttl = 300,
    ) {
    }

    public function get(string $tokenHash): ?Biscuit
    {
        $item = $this->cachePool->getItem($this->createKey($tokenHash));
        if ($item->isHit()) {
            $data = $item->get();
            if ($data instanceof Biscuit) {
                return $data;
            }
        }

        return null;
    }

    public function set(string $tokenHash, Biscuit $biscuit): void
    {
        $item = $this->cachePool->getItem($this->createKey($tokenHash));
        $item->set($biscuit);
        $item->expiresAfter($this->ttl);
        $this->cachePool->save($item);
    }

    public function delete(string $tokenHash): void
    {
        $this->cachePool->deleteItem($this->createKey($tokenHash));
    }

    private function createKey(string $tokenHash): string
    {
        return 'biscuit_token_' . $tokenHash;
    }
}
