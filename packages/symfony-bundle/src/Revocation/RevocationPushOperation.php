<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

enum RevocationPushOperation: string
{
    case Revoke = 'revoke';

    case Unrevoke = 'unrevoke';

    case Purge = 'purge';
}
