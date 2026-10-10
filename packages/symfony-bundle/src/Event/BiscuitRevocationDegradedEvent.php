<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Event;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationFailurePolicy;

final class BiscuitRevocationDegradedEvent
{
    public function __construct(
        public readonly string $store,
        public readonly RevocationStoreUnavailableException $exception,
        public readonly RevocationFailurePolicy $policy,
    ) {
    }
}
