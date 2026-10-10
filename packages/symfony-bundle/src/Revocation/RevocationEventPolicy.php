<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

enum RevocationEventPolicy: string
{
    case Never = 'never';
    case OnRevoke = 'on_revoke';
    case Always = 'always';
}
