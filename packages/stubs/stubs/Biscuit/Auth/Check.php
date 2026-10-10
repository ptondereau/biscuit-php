<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\CheckException;
use Biscuit\Exception\ScopeException;
use Biscuit\Exception\TermException;
use Error;

/**
 * A Datalog check, such as `check if user($u)`.
 *
 * Checks must succeed during authorization, otherwise the token is rejected
 * and the failure is reported through {@see \Biscuit\Exception\AuthorizationException::getFailedChecks()}.
 *
 * Parameters written as `{name}` in the source are substituted from the
 * `$params` map or bound later with {@see Check::set()}.
 *
 * ```php
 * $check = new Check('check if user({username})', ['username' => 'alice']);
 * ```
 */
class Check
{
    /**
     * Parses a check from Datalog source code.
     *
     * @param non-empty-string $source
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws CheckException If the source is not a valid check.
     * @throws TermException If a parameter value has an unsupported type (e.g. null).
     * @throws ScopeException If a scope parameter cannot be applied.
     */
    public function __construct(string $source, ?array $params = null, ?array $scope_params = null)
    {
        throw new Error('Biscuit\Auth\Check::__construct() should be implemented by the biscuit_php extension.');
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
        throw new Error('Biscuit\Auth\Check::set() should be implemented by the biscuit_php extension.');
    }

    /**
     * Binds the `{$name}` trust scope parameter to a public key.
     *
     * @throws ScopeException If the scope parameter cannot be applied.
     */
    public function setScope(string $name, PublicKey $key): void
    {
        throw new Error('Biscuit\Auth\Check::setScope() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the check as Datalog source code.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Check::__toString() should be implemented by the biscuit_php extension.');
    }
}
