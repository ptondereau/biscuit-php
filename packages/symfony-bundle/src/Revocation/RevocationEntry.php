<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use DateTimeImmutable;

final class RevocationEntry
{
    /**
     * @param non-empty-string $revocationId
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        public readonly string $revocationId,
        public readonly ?DateTimeImmutable $expiresAt = null,
        public readonly ?DateTimeImmutable $revokedAt = null,
        public readonly ?string $subject = null,
        public readonly ?string $reason = null,
        public readonly array $metadata = [],
    ) {
    }
}
