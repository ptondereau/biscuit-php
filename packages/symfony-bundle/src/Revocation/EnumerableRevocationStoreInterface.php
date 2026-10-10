<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;

interface EnumerableRevocationStoreInterface extends RevocationStoreInterface
{
    /**
     * A generator implementation throws while being iterated, not when called.
     *
     * @return iterable<RevocationEntry>
     *
     * @throws RevocationStoreUnavailableException
     */
    public function all(): iterable;
}
