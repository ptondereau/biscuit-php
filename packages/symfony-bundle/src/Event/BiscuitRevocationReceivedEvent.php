<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Event;

use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationPushOperation;

final class BiscuitRevocationReceivedEvent
{
    private function __construct(
        public readonly RevocationPushOperation $operation,
        public readonly ?RevocationEntry $entry = null,
        public readonly ?string $revocationId = null,
        public readonly int $purged = 0,
    ) {
    }

    public static function revoked(RevocationEntry $entry): self
    {
        return new self(
            RevocationPushOperation::Revoke,
            entry: $entry,
            revocationId: $entry->revocationId,
        );
    }

    public static function unrevoked(string $revocationId): self
    {
        return new self(RevocationPushOperation::Unrevoke, revocationId: $revocationId);
    }

    public static function purged(int $purged): self
    {
        return new self(RevocationPushOperation::Purge, purged: $purged);
    }
}
