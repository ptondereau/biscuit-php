<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;

interface RevocationCheckerInterface
{
    /**
     * @throws RevocationStoreUnavailableException
     */
    public function check(Biscuit|UnverifiedBiscuit $token): RevocationResult;

    /**
     * @param list<non-empty-string> $revocationIds
     *
     * @throws RevocationStoreUnavailableException
     */
    public function checkIds(array $revocationIds, bool $verified = false): RevocationResult;
}
