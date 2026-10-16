<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Error;

/**
 * A Datalog bytes term, such as `hex:00ff`.
 *
 * A PHP string binds as a Datalog string and a list binds as a set, so binary
 * data must be wrapped in `Bytes` to bind as bytes.
 *
 * ```php
 * $fact = new Fact('digest({digest})', ['digest' => new Bytes(hash('sha256', $payload, true))]);
 * ```
 */
class Bytes
{
    /**
     * Wraps a binary string.
     */
    public function __construct(string $data)
    {
        throw new Error('Biscuit\Auth\Bytes::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the binary string.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Bytes::__toString() should be implemented by the biscuit_php extension.');
    }
}
