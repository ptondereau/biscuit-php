<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\FactException;
use Biscuit\Exception\TermException;
use DateTimeImmutable;
use DateTimeInterface;
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
     * @param array<string, int|bool|string|Bytes|DateTimeInterface|list<int|bool|string|Bytes|DateTimeInterface>>|null $params Values for `{name}` parameters.
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
     * @param int|bool|string|Bytes|DateTimeInterface|list<int|bool|string|Bytes|DateTimeInterface> $value
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
     * Returns the fact's terms as PHP values, in the types accepted by {@see Fact::set()}:
     * int, bool, string, {@see Bytes} for bytes, `DateTimeImmutable` in UTC for dates and a list for sets.
     *
     * ```php
     * $fact = $authorizer->query(new Rule('u($name, $age) <- user($name, $age)'))[0];
     * [$name, $age] = $fact->terms();
     * ```
     *
     * @return list<int|bool|string|Bytes|DateTimeImmutable|list<int|bool|string|Bytes|DateTimeImmutable>>
     *
     * @throws TermException If a term has no PHP equivalent (null, array, map or unbound parameter).
     */
    public function terms(): array
    {
        throw new Error('Biscuit\Auth\Fact::terms() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the fact as Datalog source code.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Fact::__toString() should be implemented by the biscuit_php extension.');
    }
}
