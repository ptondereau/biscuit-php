<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\CheckException;
use Biscuit\Exception\FactException;
use Biscuit\Exception\PolicyException;
use Biscuit\Exception\RuleException;
use Biscuit\Exception\SnapshotException;
use Biscuit\Exception\TermException;
use Error;

/**
 * Accumulates facts, rules, checks, and policies, then builds an
 * {@see Authorizer} to evaluate them against a token.
 *
 * ```php
 * $authBuilder = new AuthorizerBuilder();
 * $authBuilder->addCode('resource({res});', ['res' => 'file1']);
 * $authBuilder->addPolicy(new Policy('allow if user("alice")'));
 *
 * $authorizer = $authBuilder->build($token);
 * $matched = $authorizer->authorize();
 * ```
 *
 * {@see AuthorizerBuilder::build()} and
 * {@see AuthorizerBuilder::buildUnauthenticated()} clone the builder
 * internally, so the same builder can build several authorizers.
 */
class AuthorizerBuilder
{
    /**
     * Creates a builder, optionally seeded with Datalog source code
     * (equivalent to calling {@see AuthorizerBuilder::addCode()}).
     *
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws TermException If the source cannot be parsed or a parameter cannot be applied.
     */
    public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null)
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::__construct() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Parses Datalog source code (facts, rules, checks, and policies) into
     * the builder.
     *
     * @param array<string, int|bool|string|list<int|bool|string>>|null $params Values for `{name}` parameters.
     * @param array<string, PublicKey>|null $scope_params Public keys for trust scope parameters.
     *
     * @throws TermException If the source cannot be parsed or a parameter cannot be applied.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::addCode() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * @throws FactException If the fact cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addFact(Fact $fact): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::addFact() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * @throws RuleException If the rule cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addRule(Rule $rule): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::addRule() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * @throws CheckException If the check cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addCheck(Check $check): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::addCheck() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Adds a policy; policies are evaluated in insertion order during
     * {@see Authorizer::authorize()}.
     *
     * @throws PolicyException If the policy cannot be added.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function addPolicy(Policy $policy): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::addPolicy() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Adds a fact with the current time (`time(<current timestamp>)`).
     *
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function setTime(): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::setTime() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Merges the content of another authorizer builder into this builder.
     *
     * $other is consumed by this call: using it afterwards throws
     * a {@see BuilderStateException}.
     *
     * @throws BuilderStateException If either builder has already been consumed.
     */
    public function merge(AuthorizerBuilder $other): void
    {
        throw new Error('Biscuit\Auth\AuthorizerBuilder::merge() should be implemented by the biscuit_php extension.');
    }

    /**
     * Merges the content of a block builder into this builder.
     *
     * $block is consumed by this call: using it afterwards throws
     * a {@see BuilderStateException}.
     *
     * @throws BuilderStateException If either builder has already been consumed.
     */
    public function mergeBlock(BlockBuilder $block): void
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::mergeBlock() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Serializes the builder state to a base64 snapshot.
     *
     * @throws SnapshotException If the snapshot cannot be serialized.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function base64Snapshot(): string
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::base64Snapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Serializes the builder state to a raw snapshot.
     *
     * @return list<int>
     *
     * @throws SnapshotException If the snapshot cannot be serialized.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function rawSnapshot(): array
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::rawSnapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Restores a builder from a base64 snapshot.
     *
     * @throws SnapshotException If the snapshot cannot be deserialized.
     */
    public static function fromBase64Snapshot(string $input): AuthorizerBuilder
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::fromBase64Snapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Restores a builder from a raw snapshot (binary string).
     *
     * @throws SnapshotException If the snapshot cannot be deserialized.
     */
    public static function fromRawSnapshot(string $input): AuthorizerBuilder
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::fromRawSnapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Builds the authorizer from a verified token.
     *
     * @throws AuthorizerBuildException If the authorizer cannot be built.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function build(#[\SensitiveParameter] Biscuit $token): Authorizer
    {
        throw new Error('Biscuit\Auth\AuthorizerBuilder::build() should be implemented by the biscuit_php extension.');
    }

    /**
     * Builds the authorizer without a token.
     *
     * @throws AuthorizerBuildException If the authorizer cannot be built.
     * @throws BuilderStateException If the builder has already been consumed.
     */
    public function buildUnauthenticated(): Authorizer
    {
        throw new Error(
            'Biscuit\Auth\AuthorizerBuilder::buildUnauthenticated() should be implemented by the biscuit_php extension.',
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
            'Biscuit\Auth\AuthorizerBuilder::__toString() should be implemented by the biscuit_php extension.',
        );
    }
}
