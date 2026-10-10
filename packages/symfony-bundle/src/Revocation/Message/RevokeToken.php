<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Message;

use Biscuit\BiscuitBundle\Revocation\RevocationEntry;

final class RevokeToken
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        public readonly string $revocationId,
        public readonly ?string $expiresAt = null,
        public readonly ?string $revokedAt = null,
        public readonly ?string $subject = null,
        public readonly ?string $reason = null,
        public readonly array $metadata = [],
    ) {
    }

    public static function fromEntry(RevocationEntry $entry): self
    {
        return new self(
            revocationId: $entry->revocationId,
            expiresAt: Wire::nullableDate($entry->expiresAt),
            revokedAt: Wire::nullableDate($entry->revokedAt),
            subject: $entry->subject,
            reason: $entry->reason,
            metadata: $entry->metadata,
        );
    }

    public function toEntry(): RevocationEntry
    {
        return new RevocationEntry(
            revocationId: Wire::revocationId($this->revocationId),
            expiresAt: Wire::toNullableDate($this->expiresAt, 'expiresAt'),
            revokedAt: Wire::toNullableDate($this->revokedAt, 'revokedAt'),
            subject: $this->subject,
            reason: $this->reason,
            metadata: $this->metadata,
        );
    }
}
