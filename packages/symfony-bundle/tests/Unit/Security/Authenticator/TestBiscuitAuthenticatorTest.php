<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Authenticator;

use Biscuit\BiscuitBundle\Security\Authenticator\TestBiscuitAuthenticator;
use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[CoversClass(TestBiscuitAuthenticator::class)]
final class TestBiscuitAuthenticatorTest extends TestCase
{
    use BiscuitTestTrait;

    protected function tearDown(): void
    {
        self::resetTestKeyPair();
    }

    #[Test]
    public function itImplementsAuthenticatorInterface(): void
    {
        $authenticator = new TestBiscuitAuthenticator();

        self::assertInstanceOf(AuthenticatorInterface::class, $authenticator);
    }

    #[Test]
    public function itSupportsRequestsWithTestBiscuitHeader(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator();

        self::assertTrue($authenticator->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutTestBiscuitHeader(): void
    {
        $request = Request::create('/api/test');

        $authenticator = new TestBiscuitAuthenticator();

        self::assertFalse($authenticator->supports($request));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itAuthenticatesWithDefaultUserIdentifier(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator();
        $passport = $authenticator->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
        self::assertSame('test_user', $passport->getUser()->getUserIdentifier());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itAuthenticatesWithCustomUserIdentifier(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator('custom_user', 'user("custom_user")');
        $passport = $authenticator->authenticate($request);

        self::assertSame('custom_user', $passport->getUser()->getUserIdentifier());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBiscuitUser(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator();
        $passport = $authenticator->authenticate($request);
        $user = $passport->getUser();

        self::assertInstanceOf(BiscuitUser::class, $user);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itIncludesBiscuitBadge(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator();
        $passport = $authenticator->authenticate($request);

        self::assertTrue($passport->hasBadge(BiscuitBadge::class));

        $badge = $passport->getBadge(BiscuitBadge::class);
        self::assertInstanceOf(BiscuitBadge::class, $badge);
    }

    #[Test]
    public function itThrowsLogicExceptionOnCreateToken(): void
    {
        $authenticator = new TestBiscuitAuthenticator();
        $passport = $this->createMock(SelfValidatingPassport::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('createToken() should not be called directly');

        $authenticator->createToken($passport, 'main');
    }

    #[Test]
    public function itReturnsNullOnAuthenticationSuccess(): void
    {
        $request = Request::create('/api/test');
        $token = $this->createMock(TokenInterface::class);

        $authenticator = new TestBiscuitAuthenticator();
        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertNull($response);
    }

    #[Test]
    public function itReturnsNullOnAuthenticationFailure(): void
    {
        $request = Request::create('/api/test');
        $exception = new AuthenticationException('Test failure');

        $authenticator = new TestBiscuitAuthenticator();
        $response = $authenticator->onAuthenticationFailure($request, $exception);

        self::assertNull($response);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesValidBiscuitToken(): void
    {
        $request = Request::create('/api/test');
        $request->headers->set('X-Test-Biscuit', '1');

        $authenticator = new TestBiscuitAuthenticator('test_user', 'user("test_user"); role("admin")');
        $passport = $authenticator->authenticate($request);

        $badge = $passport->getBadge(BiscuitBadge::class);
        self::assertInstanceOf(BiscuitBadge::class, $badge);

        $biscuit = $badge->getBiscuit();
        $source = $biscuit->blockSource(0);

        self::assertStringContainsString('user("test_user")', $source);
        self::assertStringContainsString('role("admin")', $source);
    }
}
