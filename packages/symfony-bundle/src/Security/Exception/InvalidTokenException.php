<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidTokenException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Invalid biscuit token.';
    }
}
