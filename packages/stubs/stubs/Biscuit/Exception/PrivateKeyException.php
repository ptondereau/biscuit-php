<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a {@see \Biscuit\Auth\PrivateKey} cannot be parsed or
 * deserialized (constructor, fromBytes, fromPem, fromDer).
 */
class PrivateKeyException extends KeyException
{
    private function __construct() {}
}
