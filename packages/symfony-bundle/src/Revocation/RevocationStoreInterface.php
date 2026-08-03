<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;

interface RevocationStoreInterface
{
    /**
     * @param list<non-empty-string> $revocationIds
     *
     * @return non-empty-string|null
     *
     * @throws RevocationStoreUnavailableException
     */
    public function findRevoked(array $revocationIds): ?string;
}
