<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Error;

/**
 * A check that failed during authorization, exposed by
 * {@see \Biscuit\Exception\AuthorizationException::getFailedChecks()}.
 */
class FailedCheck
{
    /**
     * Instances are created by the extension during authorization.
     */
    private function __construct() {}

    /**
     * Returns where the failed check was declared: in a token block or in
     * the authorizer.
     *
     * @return 'block'|'authorizer'
     */
    public function getOrigin(): string
    {
        throw new Error('Biscuit\Auth\FailedCheck::getOrigin() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the index of the block declaring the check, or null when the
     * check comes from the authorizer.
     */
    public function getBlockId(): ?int
    {
        throw new Error('Biscuit\Auth\FailedCheck::getBlockId() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the index of the check within its block or the authorizer.
     */
    public function getCheckId(): int
    {
        throw new Error('Biscuit\Auth\FailedCheck::getCheckId() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the Datalog source of the failed check.
     */
    public function getRule(): string
    {
        throw new Error('Biscuit\Auth\FailedCheck::getRule() should be implemented by the biscuit_php extension.');
    }
}
