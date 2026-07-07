<?php

declare(strict_types=1);

namespace Biscuit\Exception;

use Biscuit\Auth\ParseError;
use Error;

/**
 * Base class for Datalog parsing and parameter substitution failures.
 */
class DatalogException extends BiscuitException
{
    private function __construct() {}

    /**
     * Parse errors reported by the Datalog parser, when the failure was a
     * parse error.
     *
     * @return list<ParseError>|null
     */
    public function getParseErrors(): ?array
    {
        throw new Error(
            'Biscuit\Exception\DatalogException::getParseErrors() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Names of `{name}` parameters declared in the source but left without
     * a value, when the failure was a parameter mismatch.
     *
     * @return list<string>|null
     */
    public function getMissingParameters(): ?array
    {
        throw new Error(
            'Biscuit\Exception\DatalogException::getMissingParameters() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Names of provided parameters that do not appear in the source, when
     * the failure was a parameter mismatch.
     *
     * @return list<string>|null
     */
    public function getUnusedParameters(): ?array
    {
        throw new Error(
            'Biscuit\Exception\DatalogException::getUnusedParameters() should be implemented by the biscuit_php extension.',
        );
    }
}
