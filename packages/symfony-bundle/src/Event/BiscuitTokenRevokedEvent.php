<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Event;

use Biscuit\BiscuitBundle\Revocation\RevocationEntry;

final class BiscuitTokenRevokedEvent
{
    public function __construct(
        public readonly RevocationEntry $entry,
    ) {
    }
}
