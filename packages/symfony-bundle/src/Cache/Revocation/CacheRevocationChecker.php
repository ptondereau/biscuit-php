<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Cache\Revocation;

use Biscuit\Auth\Biscuit;
use Psr\Cache\CacheItemPoolInterface;

final class CacheRevocationChecker implements RevocationCheckerInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cachePool,
    ) {
    }

    public function isRevoked(Biscuit $biscuit): bool
    {
        $revocationIds = $biscuit->revocationIds();
        foreach ($revocationIds as $revocationId) {
            $item = $this->cachePool->getItem($this->createKey($revocationId));
            if ($item->isHit()) {
                return true;
            }
        }

        return false;
    }

    public function revoke(string $revocationId, ?int $ttl = null): void
    {
        $item = $this->cachePool->getItem($this->createKey($revocationId));
        $item->set(true);
        if (null !== $ttl) {
            $item->expiresAfter($ttl);
        }
        $this->cachePool->save($item);
    }

    public function unrevoke(string $revocationId): void
    {
        $this->cachePool->deleteItem($this->createKey($revocationId));
    }

    private function createKey(string $revocationId): string
    {
        return 'biscuit_revoked_' . $revocationId;
    }
}
