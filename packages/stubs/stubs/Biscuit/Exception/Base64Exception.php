<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a token cannot be serialized to or deserialized from its
 * URL-safe base64 form ({@see \Biscuit\Auth\Biscuit::fromBase64()},
 * {@see \Biscuit\Auth\Biscuit::toBase64()},
 * {@see \Biscuit\Auth\UnverifiedBiscuit::fromBase64()}).
 */
class Base64Exception extends FormatException
{
    private function __construct() {}
}
