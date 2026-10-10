<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;

final class ArrayRevocationStore implements EnumerableRevocationStoreInterface, RevocationWriterInterface
{
    /**
     * @var array<string, RevocationEntry>
     */
    private array $entries = [];

    /**
     * @param iterable<RevocationEntry> $entries
     */
    public function __construct(iterable $entries = [])
    {
        foreach ($entries as $entry) {
            $this->revoke($entry);
        }
    }

    public function findRevoked(array $revocationIds): ?string
    {
        if ([] === $this->entries) {
            return null;
        }

        foreach ($revocationIds as $revocationId) {
            if (isset($this->entries[strtolower($revocationId)])) {
                return $revocationId;
            }
        }

        return null;
    }

    public function all(): iterable
    {
        return array_values($this->entries);
    }

    public function revoke(RevocationEntry $entry): void
    {
        $this->entries[strtolower($entry->revocationId)] = $entry;
    }

    public function unrevoke(string $revocationId): void
    {
        unset($this->entries[strtolower($revocationId)]);
    }

    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();
        $purged = 0;

        foreach ($this->entries as $key => $entry) {
            if (null !== $entry->expiresAt && $entry->expiresAt < $now) {
                unset($this->entries[$key]);
                ++$purged;
            }
        }

        return $purged;
    }
}
