<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\BiscuitBuildException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\CheckException;
use Biscuit\Exception\FactException;
use Biscuit\Exception\RuleException;
use Biscuit\Exception\TermException;
use Error;

/**
 * Builds the authority (first) block of a new Biscuit token.
 *
 * ```php
 * $root = new KeyPair();
 *
 * $builder = new BiscuitBuilder();
 * $builder->addCode('user({id})', ['id' => 'alice']);
 * $token = $builder->build($root->getPrivateKey());
 * ```
 *
 * {@see BiscuitBuilder::build()} clones the builder internally, so the same
 * builder can keep accumulating code and build several tokens.
 */
class BiscuitBuilder
{
    /**
     * Creates a builder, optionally seeded with Datalog source code
     * (equivalent to calling {@see BiscuitBuilder::addCode()}).
     *
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws TermException If the source cannot be parsed or a parameter cannot be applied.
     */
    public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null)
    {
        throw new Error(
            'Biscuit\Auth\BiscuitBuilder::__construct() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Signs the accumulated authority block with the root private key and
     * returns the resulting token.
     *
     * @throws BiscuitBuildException If the token cannot be built.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function build(PrivateKey $root): Biscuit
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::build() should be implemented by the biscuit_php extension.');
    }

    /**
     * Parses Datalog source code (facts, rules, and checks) into the builder.
     *
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws TermException If the source cannot be parsed or a parameter cannot be applied.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::addCode() should be implemented by the biscuit_php extension.');
    }

    /**
     * Merges the content of a block builder into this builder.
     *
     * $other is consumed by this call: using it afterwards throws
     * a {@see BuilderStateException}.
     *
     * @throws BuilderStateException If either builder has already been consumed.
     */
    public function merge(BlockBuilder $other): void
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::merge() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws FactException If the fact cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addFact(Fact $fact): void
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::addFact() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws RuleException If the rule cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addRule(Rule $rule): void
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::addRule() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws CheckException If the check cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addCheck(Check $check): void
    {
        throw new Error('Biscuit\Auth\BiscuitBuilder::addCheck() should be implemented by the biscuit_php extension.');
    }

    /**
     * Stores an identifier in the token that hints which root public key to
     * use during verification (see {@see UnverifiedBiscuit::rootKeyId()}).
     *
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function setRootKeyId(int $root_key_id): void
    {
        throw new Error(
            'Biscuit\Auth\BiscuitBuilder::setRootKeyId() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the builder content as Datalog source code.
     *
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function __toString(): string
    {
        throw new Error(
            'Biscuit\Auth\BiscuitBuilder::__toString() should be implemented by the biscuit_php extension.',
        );
    }
}
