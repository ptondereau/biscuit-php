<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Error;

/**
 * A single Datalog parse error, reported through
 * {@see \Biscuit\Exception\DatalogException::getParseErrors()}.
 */
class ParseError
{
    /**
     * Instances are created by the extension when parsing fails.
     */
    private function __construct() {}

    /**
     * Returns the source fragment that failed to parse.
     */
    public function getInput(): string
    {
        throw new Error('Biscuit\Auth\ParseError::getInput() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the parser error message, when one is available.
     */
    public function getMessage(): ?string
    {
        throw new Error('Biscuit\Auth\ParseError::getMessage() should be implemented by the biscuit_php extension.');
    }
}
