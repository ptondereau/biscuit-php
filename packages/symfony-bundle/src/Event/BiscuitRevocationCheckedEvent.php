<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Event;

use Biscuit\BiscuitBundle\Revocation\RevocationResult;

final class BiscuitRevocationCheckedEvent
{
    public function __construct(
        public readonly RevocationResult $result,
    ) {
    }
}
