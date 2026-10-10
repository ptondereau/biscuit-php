<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when an attenuation block cannot be appended to a token
 * ({@see \Biscuit\Auth\Biscuit::append()},
 * {@see \Biscuit\Auth\UnverifiedBiscuit::append()}).
 */
class BlockAppendException extends BuildException
{
    private function __construct() {}
}
