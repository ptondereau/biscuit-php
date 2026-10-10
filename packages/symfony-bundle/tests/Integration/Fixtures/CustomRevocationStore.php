<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\Fixtures;

use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;

final class CustomRevocationStore implements RevocationStoreInterface
{
    public function findRevoked(array $revocationIds): ?string
    {
        return null;
    }
}
