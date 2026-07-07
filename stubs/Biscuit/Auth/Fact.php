<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\FactException;
use Biscuit\Exception\TermException;
use Error;

/**
 * A Datalog fact, such as `user("alice")`.
 *
 * Parameters written as `{name}` in the source are substituted from the
 * `$params` map or bound later with {@see Fact::set()}.
 *
 * ```php
 * $fact = new Fact('user({name})', ['name' => 'alice']);
 *
 * $fact = new Fact('user({name})');
 * $fact->set('name', 'alice');
 * ```
 */
class Fact
{
    /**
     * Parses a fact from Datalog source code.
     *
     * @param non-empty-string $source
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     *
     * @throws FactException If the source is not a valid fact.
     * @throws TermException If a parameter value has an unsupported type (e.g. null).
     */
    public function __construct(string $source, ?array $params = null)
    {
        throw new Error('Biscuit\Auth\Fact::__construct() should be implemented by the biscuit_php extension.');
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
        throw new Error('Biscuit\Auth\Fact::set() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the name of the fact's predicate (e.g. `user` for `user("alice")`).
     */
    public function name(): string
    {
        throw new Error('Biscuit\Auth\Fact::name() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the fact as Datalog source code.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Fact::__toString() should be implemented by the biscuit_php extension.');
    }
}
