<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when an authorizer snapshot cannot be serialized or deserialized,
 * or when a token block index is out of range
 * ({@see \Biscuit\Auth\Biscuit::blockSource()},
 * {@see \Biscuit\Auth\Biscuit::blockExternalKey()}).
 */
class SnapshotException extends FormatException
{
    private function __construct() {}
}
