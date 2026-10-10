<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a token cannot be built
 * ({@see \Biscuit\Auth\BiscuitBuilder::build()}).
 */
class BiscuitBuildException extends BuildException
{
    private function __construct() {}
}
