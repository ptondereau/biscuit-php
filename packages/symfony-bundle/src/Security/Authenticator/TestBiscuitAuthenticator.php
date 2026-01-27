<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Authenticator;

use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Test authenticator that bypasses real token validation.
 *
 * Use this authenticator in your test environment to simplify testing
 * of Biscuit-protected endpoints. It creates a valid test token when
 * the X-Test-Biscuit header is present.
 *
 * @example
 * ```yaml
 * # config/packages/test/security.yaml
 * security:
 *     firewalls:
 *         api:
 *             custom_authenticators:
 *                 - Biscuit\BiscuitBundle\Security\Authenticator\TestBiscuitAuthenticator
 * ```
 * @example
 * ```php
 * // In your functional test
 * $client->request('GET', '/api/resource', [], [], [
 *     'HTTP_X_TEST_BISCUIT' => '1',
 * ]);
 * ```
 */
final class TestBiscuitAuthenticator implements AuthenticatorInterface
{
    use BiscuitTestTrait;

    /**
     * @param non-empty-string $testUserIdentifier The user identifier to use for test authentication
     * @param string $testTokenCode The Biscuit datalog code for the test token
     */
    public function __construct(
        private readonly string $testUserIdentifier = 'test_user',
        private readonly string $testTokenCode = 'user("test_user")',
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-Test-Biscuit');
    }

    public function authenticate(Request $request): Passport
    {
        $biscuit = $this->createTestToken($this->testTokenCode);

        return new SelfValidatingPassport(
            new UserBadge($this->testUserIdentifier, fn (): BiscuitUser => new BiscuitUser($biscuit, $this->testUserIdentifier)),
            [new BiscuitBadge($biscuit)],
        );
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        throw new LogicException('createToken() should not be called directly. The security system handles token creation.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
