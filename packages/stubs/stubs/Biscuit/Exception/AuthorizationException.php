<?php

declare(strict_types=1);

namespace Biscuit\Exception;

use Biscuit\Auth\FailedCheck;
use Biscuit\Auth\MatchedPolicy;
use Error;

/**
 * Thrown by {@see \Biscuit\Auth\Authorizer::authorize()} when a check
 * fails or no `allow` policy matches.
 *
 * ```php
 * try {
 *     $authorizer->authorize();
 * } catch (AuthorizationException $e) {
 *     $deniedBy = $e->getMatchedPolicy();
 *     foreach ($e->getFailedChecks() as $check) {
 *         // $check->getRule(), $check->getOrigin(), ...
 *     }
 * }
 * ```
 */
class AuthorizationException extends BiscuitException
{
    private function __construct() {}

    /**
     * Returns the `deny` policy that matched, or null when authorization
     * failed without any policy matching.
     */
    public function getMatchedPolicy(): ?MatchedPolicy
    {
        throw new Error(
            'Biscuit\Exception\AuthorizationException::getMatchedPolicy() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the checks that failed during authorization.
     *
     * @return list<FailedCheck>
     */
    public function getFailedChecks(): array
    {
        throw new Error(
            'Biscuit\Exception\AuthorizationException::getFailedChecks() should be implemented by the biscuit_php extension.',
        );
    }
}
