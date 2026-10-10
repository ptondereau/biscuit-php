<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Message;

use DateTimeImmutable;

final class PurgeExpiredRevocations
{
    public function __construct(
        public readonly string $before,
    ) {
    }

    public static function fromDate(DateTimeImmutable $before): self
    {
        return new self(Wire::date($before));
    }

    public function toDate(): DateTimeImmutable
    {
        return Wire::toDate($this->before, 'before');
    }
}
