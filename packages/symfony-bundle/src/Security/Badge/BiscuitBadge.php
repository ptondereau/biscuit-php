<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Badge;

use Biscuit\Auth\Biscuit;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class BiscuitBadge implements BadgeInterface
{
    public function __construct(
        private readonly Biscuit $biscuit,
    ) {
    }

    public function getBiscuit(): Biscuit
    {
        return $this->biscuit;
    }

    public function isResolved(): bool
    {
        return true;
    }
}
