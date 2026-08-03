<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\Fixtures;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;

final class CustomEnumerableRevocationStore implements EnumerableRevocationStoreInterface
{
    public function findRevoked(array $revocationIds): ?string
    {
        return null;
    }

    public function all(): iterable
    {
        yield new RevocationEntry('abc');
    }
}
