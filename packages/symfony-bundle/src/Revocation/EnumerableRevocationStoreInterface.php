<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

interface EnumerableRevocationStoreInterface extends RevocationStoreInterface
{
    /**
     * @return iterable<RevocationEntry>
     */
    public function all(): iterable;
}
