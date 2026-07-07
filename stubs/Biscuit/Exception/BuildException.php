<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Base class for token, block, and authorizer build failures.
 */
class BuildException extends BiscuitException
{
    private function __construct() {}
}
