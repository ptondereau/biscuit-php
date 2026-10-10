<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

final class RevocationResult
{
    /**
     * @param list<non-empty-string> $checkedIds
     * @param non-empty-string|null $revokedId
     * @param list<RevocationStoreOutcome> $outcomes
     */
    public function __construct(
        public readonly array $checkedIds,
        public readonly ?string $revokedId,
        public readonly ?string $store,
        public readonly float $durationMs,
        public readonly bool $verified,
        public readonly bool $degraded,
        public readonly array $outcomes = [],
    ) {
    }

    public function isRevoked(): bool
    {
        return null !== $this->revokedId;
    }
}
