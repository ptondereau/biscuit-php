<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Event\BiscuitTokenRevokedEvent;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ChainRevocationWriter implements RevocationWriterInterface
{
    /**
     * @param iterable<array-key, RevocationWriterInterface> $writers
     */
    public function __construct(
        private readonly iterable $writers,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function revoke(RevocationEntry $entry): void
    {
        $failure = null;

        foreach ($this->writers as $writer) {
            try {
                $writer->revoke($entry);
            } catch (RevocationStoreUnavailableException $e) {
                $failure ??= $e;
            }
        }

        if (null !== $failure) {
            throw $failure;
        }

        $this->eventDispatcher?->dispatch(new BiscuitTokenRevokedEvent($entry));
    }

    public function unrevoke(string $revocationId): void
    {
        $failure = null;

        foreach ($this->writers as $writer) {
            try {
                $writer->unrevoke($revocationId);
            } catch (RevocationStoreUnavailableException $e) {
                $failure ??= $e;
            }
        }

        if (null !== $failure) {
            throw $failure;
        }
    }

    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();
        $purged = 0;
        $failure = null;

        foreach ($this->writers as $writer) {
            try {
                $purged += $writer->purgeExpired($now);
            } catch (RevocationStoreUnavailableException $e) {
                $failure ??= $e;
            }
        }

        if (null !== $failure) {
            throw $failure;
        }

        return $purged;
    }
}
