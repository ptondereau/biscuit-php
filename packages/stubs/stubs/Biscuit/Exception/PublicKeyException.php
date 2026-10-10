<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a {@see \Biscuit\Auth\PublicKey} cannot be parsed or
 * deserialized (constructor, fromBytes, fromPem, fromDer).
 */
class PublicKeyException extends KeyException
{
    private function __construct() {}
}
