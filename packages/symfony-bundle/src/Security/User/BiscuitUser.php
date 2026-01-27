<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\User;

use Biscuit\Auth\Biscuit;
use Symfony\Component\Security\Core\User\UserInterface;

final class BiscuitUser implements UserInterface
{
    /**
     * @param non-empty-string $identifier
     */
    public function __construct(
        private readonly Biscuit $biscuit,
        private readonly string $identifier,
    ) {
    }

    public function getBiscuit(): Biscuit
    {
        return $this->biscuit;
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_BISCUIT_USER'];
    }

    public function eraseCredentials(): void
    {
    }
}
