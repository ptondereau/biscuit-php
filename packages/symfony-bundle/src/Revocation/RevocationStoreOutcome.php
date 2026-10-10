<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

final class RevocationStoreOutcome
{
    /**
     * @param non-empty-string|null $revokedId
     */
    public function __construct(
        public readonly string $store,
        public readonly ?string $revokedId,
        public readonly float $durationMs,
        public readonly ?string $error = null,
    ) {
    }
}
