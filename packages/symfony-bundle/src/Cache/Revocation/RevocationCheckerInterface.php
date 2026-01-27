<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Cache\Revocation;

use Biscuit\Auth\Biscuit;

interface RevocationCheckerInterface
{
    public function isRevoked(Biscuit $biscuit): bool;
}
