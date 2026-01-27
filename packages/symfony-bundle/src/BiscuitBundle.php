<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BiscuitBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
