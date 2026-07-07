<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\CheckException;
use Biscuit\Exception\FactException;
use Biscuit\Exception\RuleException;
use Biscuit\Exception\TermException;
use Error;

/**
 * Builds an attenuation block, appended to an existing token with
 * {@see Biscuit::append()} or signed by a third party through
 * {@see ThirdPartyRequest::createBlock()}.
 *
 * ```php
 * $block = new BlockBuilder();
 * $block->addCode('check if resource({res})', ['res' => 'file1']);
 * $attenuated = $token->append($block);
 * ```
 */
class BlockBuilder
{
    /**
     * Creates a builder, optionally seeded with Datalog source code
     * (equivalent to calling {@see BlockBuilder::addCode()}).
     *
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws TermException If the source cannot be parsed or a parameter cannot be applied.
     */
    public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null)
    {
        throw new Error('Biscuit\Auth\BlockBuilder::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws FactException If the fact cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addFact(Fact $fact): void
    {
        throw new Error('Biscuit\Auth\BlockBuilder::addFact() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws RuleException If the rule cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addRule(Rule $rule): void
    {
        throw new Error('Biscuit\Auth\BlockBuilder::addRule() should be implemented by the biscuit_php extension.');
    }

    /**
     * @throws CheckException If the check cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addCheck(Check $check): void
    {
        throw new Error('Biscuit\Auth\BlockBuilder::addCheck() should be implemented by the biscuit_php extension.');
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
        throw new Error('Biscuit\Auth\BlockBuilder::addCode() should be implemented by the biscuit_php extension.');
    }

    /**
     * Merges the content of another block builder into this builder.
     *
     * $other is consumed by this call: using it afterwards throws
     * a {@see BuilderStateException}.
     *
     * @throws BuilderStateException If either builder has already been consumed.
     */
    public function merge(BlockBuilder $other): void
    {
        throw new Error('Biscuit\Auth\BlockBuilder::merge() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the builder content as Datalog source code.
     *
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\BlockBuilder::__toString() should be implemented by the biscuit_php extension.');
    }
}
