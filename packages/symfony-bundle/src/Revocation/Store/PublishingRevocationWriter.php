<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

final class PublishingRevocationWriter implements RevocationWriterInterface
{
    public function __construct(
        private readonly RevocationWriterInterface $writer,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function revoke(RevocationEntry $entry): void
    {
        $this->writer->revoke($entry);
        $this->bus->dispatch(RevokeToken::fromEntry($entry));
    }

    public function unrevoke(string $revocationId): void
    {
        $this->writer->unrevoke($revocationId);
        $this->bus->dispatch(new UnrevokeToken($revocationId));
    }

    /**
     * @return int the number of entries purged locally; counts from other instances cannot be
     *             collected across a fanout transport
     */
    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();

        $purged = $this->writer->purgeExpired($now);

        $this->bus->dispatch(PurgeExpiredRevocations::fromDate($now));

        return $purged;
    }
}
