<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use DateTimeImmutable;

interface RevocationWriterInterface
{
    public function revoke(RevocationEntry $entry): void;

    /**
     * @param non-empty-string $revocationId
     */
    public function unrevoke(string $revocationId): void;

    public function purgeExpired(?DateTimeImmutable $now = null): int;
}
