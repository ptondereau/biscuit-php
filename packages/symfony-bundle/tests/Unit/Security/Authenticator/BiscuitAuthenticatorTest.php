<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Authenticator;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationResult;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use Biscuit\BiscuitBundle\Security\Exception\MissingTokenException;
use Biscuit\BiscuitBundle\Security\Exception\RevokedTokenException;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[CoversClass(BiscuitAuthenticator::class)]
final class BiscuitAuthenticatorTest extends TestCase
{
    private TokenExtractorInterface&MockObject $tokenExtractor;

    private BiscuitTokenManager&MockObject $tokenManager;

    private RevocationChecker&MockObject $revocationChecker;

    protected function setUp(): void
    {
        $this->tokenExtractor = $this->createMock(TokenExtractorInterface::class);
        $this->tokenManager = $this->createMock(BiscuitTokenManager::class);
        $this->revocationChecker = $this->createMock(RevocationChecker::class);
    }

    #[Test]
    public function itImplementsAbstractAuthenticator(): void
    {
        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        self::assertInstanceOf(AbstractAuthenticator::class, $authenticator);
    }

    #[Test]
    public function itSupportsRequestWhenTokenExtractorSupports(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor
            ->expects(self::once())
            ->method('supports')
            ->with($request)
            ->willReturn(true);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        self::assertTrue($authenticator->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestWhenTokenExtractorDoesNotSupport(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor
            ->expects(self::once())
            ->method('supports')
            ->with($request)
            ->willReturn(false);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        self::assertFalse($authenticator->supports($request));
    }

    #[Test]
    public function itThrowsExceptionWhenNoTokenProvided(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn(null);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $this->expectException(MissingTokenException::class);

        $authenticator->authenticate($request);
    }

    #[Test]
    public function itThrowsExceptionWhenTokenIsInvalid(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('invalid-token');

        $this->tokenManager
            ->method('parse')
            ->with('invalid-token')
            ->willThrowException(new Exception('Parse error'));

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid biscuit token: Parse error');

        $authenticator->authenticate($request);
    }

    #[Test]
    public function itThrowsRevokedTokenExceptionWhenTokenIsRevoked(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $this->revocationChecker
            ->method('check')
            ->with($biscuit)
            ->willReturn($this->revokedResult());

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
            $this->revocationChecker,
        );

        $this->expectException(RevokedTokenException::class);

        $authenticator->authenticate($request);
    }

    #[Test]
    public function itAuthenticatesWithUserIdentifierFromFact(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('user("john-doe-123");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $passport = $authenticator->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
        self::assertSame('john-doe-123', $passport->getUser()->getUserIdentifier());
        self::assertTrue($passport->hasBadge(BiscuitBadge::class));
    }

    #[Test]
    public function itAuthenticatesWithCustomUserIdentifierFact(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('subject("custom-user-id");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
            null,
            'subject',
        );

        $passport = $authenticator->authenticate($request);

        self::assertSame('custom-user-id', $passport->getUser()->getUserIdentifier());
    }

    #[Test]
    public function itFallsBackToRevocationIdWhenNoUserFact(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('permission("read");');

        $biscuit
            ->method('revocationIds')
            ->willReturn(['revocation-id-123', 'other-id']);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $passport = $authenticator->authenticate($request);

        self::assertSame('revocation-id-123', $passport->getUser()->getUserIdentifier());
    }

    #[Test]
    public function itFallsBackToAnonymousWhenNoIdentifierAvailable(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('permission("read");');

        $biscuit
            ->method('revocationIds')
            ->willReturn([]);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $passport = $authenticator->authenticate($request);

        self::assertSame('anonymous', $passport->getUser()->getUserIdentifier());
    }

    #[Test]
    public function itReturnsNullOnAuthenticationSuccess(): void
    {
        $request = Request::create('/api/resource');
        $token = $this->createMock(TokenInterface::class);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertNull($response);
    }

    #[Test]
    public function itReturnsJsonResponseOnAuthenticationFailure(): void
    {
        $request = Request::create('/api/resource');
        $exception = new AuthenticationException('Authentication failed');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        self::assertSame('invalid_token', $content['error']);
        self::assertSame('Authentication failed', $content['error_description']);
    }

    #[Test]
    public function itReturnsRevokedMessageOnRevokedTokenException(): void
    {
        $request = Request::create('/api/resource');
        $exception = new RevokedTokenException();

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(JsonResponse::class, $response);

        $content = json_decode((string) $response->getContent(), true);
        self::assertSame('invalid_token', $content['error']);
        self::assertSame('Token has been revoked.', $content['error_description']);
    }

    #[Test]
    public function itChallengesWithoutAnErrorCodeWhenTheRequestCarriesNoToken(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor->method('extract')->with($request)->willReturn(null);

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $response = $authenticator->start($request);

        self::assertSame('Bearer realm="api"', $response->headers->get('WWW-Authenticate'));
    }

    #[Test]
    public function itChallengesWithAnInvalidTokenErrorWhenTheRequestCarriesAToken(): void
    {
        $request = Request::create('/api/resource');

        $this->tokenExtractor->method('extract')->with($request)->willReturn('some-token');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $response = $authenticator->start($request, new RevokedTokenException());

        $challenge = $response->headers->get('WWW-Authenticate');
        self::assertIsString($challenge);
        self::assertStringContainsString('error="invalid_token"', $challenge);
    }

    #[Test]
    public function itCreatesPassportWithBiscuitBadge(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('user("test-user");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $passport = $authenticator->authenticate($request);

        $badge = $passport->getBadge(BiscuitBadge::class);
        self::assertInstanceOf(BiscuitBadge::class, $badge);
        self::assertSame($biscuit, $badge->getBiscuit());
    }

    #[Test]
    public function itCreatesBiscuitUserWithCorrectData(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('user("my-user-id");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
        );

        $passport = $authenticator->authenticate($request);
        $user = $passport->getUser();

        self::assertInstanceOf(BiscuitUser::class, $user);
        self::assertSame('my-user-id', $user->getUserIdentifier());
        self::assertSame($biscuit, $user->getBiscuit());
    }

    #[Test]
    public function itWorksWithoutRevocationChecker(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('user("test");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
            null,
        );

        $passport = $authenticator->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
    }

    #[Test]
    public function itPassesWithNonRevokedToken(): void
    {
        $request = Request::create('/api/resource');
        $biscuit = $this->createMock(Biscuit::class);

        $this->tokenExtractor
            ->method('extract')
            ->with($request)
            ->willReturn('valid-token');

        $this->tokenManager
            ->method('parse')
            ->with('valid-token')
            ->willReturn($biscuit);

        $this->revocationChecker
            ->method('check')
            ->with($biscuit)
            ->willReturn($this->notRevokedResult());

        $biscuit
            ->method('blockSource')
            ->with(0)
            ->willReturn('user("test");');

        $authenticator = new BiscuitAuthenticator(
            $this->tokenExtractor,
            $this->tokenManager,
            $this->revocationChecker,
        );

        $passport = $authenticator->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
    }

    private function revokedResult(): RevocationResult
    {
        return new RevocationResult(['abc'], 'abc', 'cache', 0.1, true, false);
    }

    private function notRevokedResult(): RevocationResult
    {
        return new RevocationResult(['abc'], null, null, 0.1, true, false);
    }
}
