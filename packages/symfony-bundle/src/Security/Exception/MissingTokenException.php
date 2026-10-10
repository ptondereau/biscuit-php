<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class MissingTokenException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'No biscuit token provided.';
    }
}
