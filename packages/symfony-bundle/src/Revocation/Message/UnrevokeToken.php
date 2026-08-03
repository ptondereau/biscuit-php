<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Message;

final class UnrevokeToken
{
    public function __construct(
        public readonly string $revocationId,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function toRevocationId(): string
    {
        return Wire::revocationId($this->revocationId);
    }
}
