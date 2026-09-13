<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown by {@see \Biscuit\Auth\Authorizer::authorize()} and
 * {@see \Biscuit\Auth\Authorizer::query()} when the Datalog engine stops
 * because a run limit was reached, before any policy decision was taken.
 *
 * Extends {@see AuthorizationException} so existing `catch` blocks keep
 * failing closed; `getMatchedPolicy()` is always null and
 * `getFailedChecks()` always empty. Raise the limits with
 * {@see \Biscuit\Auth\AuthorizerBuilder::setLimits()}.
 *
 * `getCode()` identifies the limit that was hit:
 *
 * - `1` too many facts generated
 * - `2` too many engine iterations
 * - `3` too much time spent (default limit is 1 ms)
 *
 * ```php
 * try {
 *     $authorizer->authorize();
 * } catch (RunLimitException $e) {
 *     // not a denial: retry with higher limits or fail the request
 * } catch (AuthorizationException $e) {
 *     // denied
 * }
 * ```
 */
class RunLimitException extends AuthorizationException
{
    private function __construct() {}
}
