<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a token cannot be serialized to or deserialized from raw
 * bytes ({@see \Biscuit\Auth\Biscuit::fromBytes()},
 * {@see \Biscuit\Auth\Biscuit::toBytes()}).
 */
class BytesException extends FormatException
{
    private function __construct() {}
}
