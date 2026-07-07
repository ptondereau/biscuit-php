<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when an authorizer cannot be built or queried
 * ({@see \Biscuit\Auth\AuthorizerBuilder::build()},
 * {@see \Biscuit\Auth\AuthorizerBuilder::buildUnauthenticated()},
 * {@see \Biscuit\Auth\Authorizer::query()}).
 */
class AuthorizerBuildException extends BuildException
{
    private function __construct() {}
}
