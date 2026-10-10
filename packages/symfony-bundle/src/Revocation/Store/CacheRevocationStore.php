<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

final class CacheRevocationStore implements RevocationStoreInterface, RevocationWriterInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cachePool,
        private readonly string $keyPrefix = 'biscuit_revoked_',
        private readonly ?int $defaultTtl = null,
    ) {
    }

    public function findRevoked(array $revocationIds): ?string
    {
        if ([] === $revocationIds) {
            return null;
        }

        $keys = [];

        foreach ($revocationIds as $revocationId) {
            $keys[$this->createKey($revocationId)] = $revocationId;
        }

        try {
            $items = $this->cachePool->getItems(array_keys($keys));

            foreach ($items as $key => $item) {
                if ($item->isHit()) {
                    return $keys[$key];
                }
            }
        } catch (InvalidArgumentException $e) {
            throw new RevocationStoreUnavailableException($e->getMessage(), 0, $e);
        }

        return null;
    }

    public function revoke(RevocationEntry $entry): void
    {
        try {
            $item = $this->cachePool->getItem($this->createKey($entry->revocationId));
        } catch (InvalidArgumentException $e) {
            throw new RevocationStoreUnavailableException($e->getMessage(), 0, $e);
        }

        $item->set(true);

        if (null !== $entry->expiresAt) {
            $item->expiresAt($entry->expiresAt);
        } elseif (null !== $this->defaultTtl) {
            $item->expiresAfter($this->defaultTtl);
        }

        $this->cachePool->save($item);
    }

    public function unrevoke(string $revocationId): void
    {
        try {
            $this->cachePool->deleteItem($this->createKey($revocationId));
        } catch (InvalidArgumentException $e) {
            throw new RevocationStoreUnavailableException($e->getMessage(), 0, $e);
        }
    }

    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        return 0;
    }

    private function createKey(string $revocationId): string
    {
        return $this->keyPrefix . $revocationId;
    }
}
