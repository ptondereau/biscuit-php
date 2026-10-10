<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Error;

/**
 * The policy that decided an authorization outcome.
 *
 * Returned by {@see Authorizer::authorize()} on success (an `allow`
 * policy), or exposed by
 * {@see \Biscuit\Exception\AuthorizationException::getMatchedPolicy()}
 * on failure (a `deny` policy).
 */
class MatchedPolicy
{
    /**
     * Instances are created by the extension during authorization.
     */
    private function __construct() {}

    /**
     * @return 'allow'|'deny'
     */
    public function getKind(): string
    {
        throw new Error('Biscuit\Auth\MatchedPolicy::getKind() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the index of the matched policy, in declaration order.
     */
    public function getPolicyId(): int
    {
        throw new Error(
            'Biscuit\Auth\MatchedPolicy::getPolicyId() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the Datalog source of the matched policy, when available.
     */
    public function getCode(): ?string
    {
        throw new Error('Biscuit\Auth\MatchedPolicy::getCode() should be implemented by the biscuit_php extension.');
    }
}
