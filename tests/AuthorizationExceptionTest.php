<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Exception\AuthorizationException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuthorizationExceptionTest extends TestCase
{
    #[Test]
    public function failedAuthorizationMessageStatesTheCauseOnce(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessageMatches(
            '/^authorization failed: an allow policy matched \(policy index: 0\), and the following checks failed: Check n°0 in authorizer: check if false$/',
        );

        (new AuthorizerBuilder('check if false; allow if true;'))->buildUnauthenticated()->authorize();
    }
}
