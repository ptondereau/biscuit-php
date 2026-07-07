<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\AuthorizationException;
use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\SnapshotException;
use Error;

/**
 * Checks authorization policies against a token.
 *
 * Created from {@see AuthorizerBuilder::build()} or
 * {@see AuthorizerBuilder::buildUnauthenticated()}.
 *
 * ```php
 * $authorizer = (new AuthorizerBuilder('allow if user("alice")'))->build($token);
 *
 * try {
 *     $matched = $authorizer->authorize();
 *     // $matched->getKind() === 'allow'
 * } catch (AuthorizationException $e) {
 *     $deniedBy = $e->getMatchedPolicy();
 *     $failedChecks = $e->getFailedChecks();
 * }
 * ```
 */
class Authorizer
{
    /**
     * Instances are obtained through {@see AuthorizerBuilder}.
     */
    private function __construct() {}

    /**
     * Verifies the checks and policies.
     *
     * On success, returns the `allow` policy that matched. On failure,
     * throws an {@see AuthorizationException} carrying the matched `deny`
     * policy (when one matched) and the list of failed checks.
     *
     * @throws AuthorizationException If a check fails or no `allow` policy matches.
     */
    public function authorize(): MatchedPolicy
    {
        throw new Error('Biscuit\Auth\Authorizer::authorize() should be implemented by the biscuit_php extension.');
    }

    /**
     * Runs a rule against the authorizer's Datalog engine and returns the
     * facts it produces. The query only sees facts from the authorizer and
     * the token's authority block.
     *
     * @return list<Fact>
     *
     * @throws AuthorizerBuildException If the query cannot be executed.
     */
    public function query(Rule $rule): array
    {
        throw new Error('Biscuit\Auth\Authorizer::query() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the authorizer state to a base64 snapshot.
     *
     * @throws SnapshotException If the snapshot cannot be serialized.
     */
    public function base64Snapshot(): string
    {
        throw new Error(
            'Biscuit\Auth\Authorizer::base64Snapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Serializes the authorizer state to a raw snapshot.
     *
     * @return list<int>
     *
     * @throws SnapshotException If the snapshot cannot be serialized.
     */
    public function rawSnapshot(): array
    {
        throw new Error('Biscuit\Auth\Authorizer::rawSnapshot() should be implemented by the biscuit_php extension.');
    }

    /**
     * Restores an authorizer from a base64 snapshot.
     *
     * @throws SnapshotException If the snapshot cannot be deserialized.
     */
    public static function fromBase64Snapshot(string $input): Authorizer
    {
        throw new Error(
            'Biscuit\Auth\Authorizer::fromBase64Snapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Restores an authorizer from a raw snapshot (binary string).
     *
     * @throws SnapshotException If the snapshot cannot be deserialized.
     */
    public static function fromRawSnapshot(string $input): Authorizer
    {
        throw new Error(
            'Biscuit\Auth\Authorizer::fromRawSnapshot() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the authorizer content (facts, rules, checks, and policies)
     * as Datalog source code.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Authorizer::__toString() should be implemented by the biscuit_php extension.');
    }
}
