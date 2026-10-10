<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

enum RevocationFailurePolicy: string
{
    case Allow = 'allow';

    case Deny = 'deny';
}
