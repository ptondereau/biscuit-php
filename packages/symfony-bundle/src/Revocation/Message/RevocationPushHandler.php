<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Message;

use Biscuit\BiscuitBundle\Event\BiscuitRevocationReceivedEvent;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RevocationPushHandler
{
    public function __construct(
        private readonly RevocationWriterInterface $writer,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function handleRevoke(RevokeToken $message): void
    {
        $entry = $message->toEntry();

        $this->writer->revoke($entry);

        $this->logger?->info('Applied a pushed revocation.', ['revocation_id' => $entry->revocationId]);
        $this->eventDispatcher?->dispatch(BiscuitRevocationReceivedEvent::revoked($entry));
    }

    public function handleUnrevoke(UnrevokeToken $message): void
    {
        $revocationId = $message->toRevocationId();

        $this->writer->unrevoke($revocationId);

        $this->logger?->info('Applied a pushed unrevocation.', ['revocation_id' => $revocationId]);
        $this->eventDispatcher?->dispatch(BiscuitRevocationReceivedEvent::unrevoked($revocationId));
    }

    public function handlePurge(PurgeExpiredRevocations $message): void
    {
        $purged = $this->writer->purgeExpired($message->toDate());

        $this->logger?->info('Applied a pushed purge.', ['purged' => $purged]);
        $this->eventDispatcher?->dispatch(BiscuitRevocationReceivedEvent::purged($purged));
    }
}
