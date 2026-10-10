<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when token signature verification fails
 * ({@see \Biscuit\Auth\UnverifiedBiscuit::verify()}).
 */
class SignatureException extends FormatException
{
    private function __construct() {}
}
