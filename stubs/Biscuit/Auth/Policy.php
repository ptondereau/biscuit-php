<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\PolicyException;
use Biscuit\Exception\ScopeException;
use Biscuit\Exception\TermException;
use Error;

/**
 * An authorizer policy, such as `allow if user("alice")` or `deny if true`.
 *
 * Policies are evaluated in order during authorization; the first matching
 * policy decides the outcome (see {@see Authorizer::authorize()}).
 *
 * Parameters written as `{name}` in the source are substituted from the
 * `$params` map or bound later with {@see Policy::set()}.
 *
 * ```php
 * $policy = new Policy('allow if user({username})', ['username' => 'alice']);
 * ```
 */
class Policy
{
    /**
     * Parses a policy from Datalog source code.
     *
     * @param non-empty-string $source
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws PolicyException If the source is not a valid policy.
     * @throws TermException If a parameter value has an unsupported type (e.g. null).
     * @throws ScopeException If a scope parameter cannot be applied.
     */
    public function __construct(string $source, ?array $params = null, ?array $scope_params = null)
    {
        throw new Error('Biscuit\Auth\Policy::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * Binds the `{$name}` parameter to a value.
     *
     * @param int|bool|string|list<int|bool|string> $value
     *
     * @throws TermException If the value has an unsupported type (e.g. null).
     */
    public function set(string $name, mixed $value): void
    {
        throw new Error('Biscuit\Auth\Policy::set() should be implemented by the biscuit_php extension.');
    }

    /**
     * Binds the `{$name}` trust scope parameter to a public key.
     *
     * @throws ScopeException If the scope parameter cannot be applied.
     */
    public function setScope(string $name, PublicKey $key): void
    {
        throw new Error('Biscuit\Auth\Policy::setScope() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the policy as Datalog source code.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Policy::__toString() should be implemented by the biscuit_php extension.');
    }
}
