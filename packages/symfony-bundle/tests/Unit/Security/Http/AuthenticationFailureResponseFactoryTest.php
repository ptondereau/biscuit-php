<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Http;

use Biscuit\BiscuitBundle\Security\Exception\InvalidTokenException;
use Biscuit\BiscuitBundle\Security\Exception\MissingTokenException;
use Biscuit\BiscuitBundle\Security\Exception\RevokedTokenException;
use Biscuit\BiscuitBundle\Security\Http\AuthenticationFailureResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[CoversClass(AuthenticationFailureResponseFactory::class)]
final class AuthenticationFailureResponseFactoryTest extends TestCase
{
    #[Test]
    public function itAlwaysRespondsWithUnauthorized(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new RevokedTokenException());

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    public function itReportsARevokedTokenAsAnInvalidToken(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new RevokedTokenException());

        self::assertSame(
            ['error' => 'invalid_token', 'error_description' => 'Token has been revoked.'],
            $this->body($response),
        );
    }

    #[Test]
    public function itChallengesWithAnInvalidTokenErrorWhenCredentialsWerePresented(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new RevokedTokenException());

        self::assertSame(
            'Bearer realm="api", error="invalid_token", error_description="Token has been revoked."',
            $response->headers->get('WWW-Authenticate'),
        );
    }

    #[Test]
    public function itOmitsTheErrorCodeWhenNoCredentialsWerePresented(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new MissingTokenException());

        self::assertSame('Bearer realm="api"', $response->headers->get('WWW-Authenticate'));
    }

    #[Test]
    public function itReportsAMissingTokenAsAnInvalidRequest(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new MissingTokenException());

        self::assertSame('invalid_request', $this->body($response)['error']);
    }

    #[Test]
    public function itKeepsTheDetailOfAnInvalidTokenInTheDescription(): void
    {
        $response = (new AuthenticationFailureResponseFactory())
            ->create(new InvalidTokenException('Invalid biscuit token: signature mismatch'));

        self::assertSame(
            'Invalid biscuit token: signature mismatch',
            $this->body($response)['error_description'],
        );
    }

    #[Test]
    public function itFallsBackToTheMessageKeyWhenTheExceptionCarriesNoMessage(): void
    {
        $response = (new AuthenticationFailureResponseFactory())->create(new AuthenticationException(''));

        self::assertSame('An authentication exception occurred.', $this->body($response)['error_description']);
    }

    #[Test]
    public function itHonoursAConfiguredRealm(): void
    {
        $response = (new AuthenticationFailureResponseFactory(true, 'reports'))->create(new MissingTokenException());

        self::assertSame('Bearer realm="reports"', $response->headers->get('WWW-Authenticate'));
    }

    #[Test]
    public function itOmitsTheChallengeWhenDisabled(): void
    {
        $response = (new AuthenticationFailureResponseFactory(false))->create(new RevokedTokenException());

        self::assertFalse($response->headers->has('WWW-Authenticate'));
        self::assertSame('invalid_token', $this->body($response)['error']);
    }

    #[Test]
    public function itEscapesQuotesAndNewlinesSoTheChallengeStaysParseable(): void
    {
        $response = (new AuthenticationFailureResponseFactory())
            ->create(new InvalidTokenException("bad \"quote\"\nand newline"));

        $challenge = $response->headers->get('WWW-Authenticate');

        self::assertIsString($challenge);
        self::assertStringNotContainsString("\n", $challenge);
        self::assertStringContainsString('error_description="bad \\"quote\\" and newline"', $challenge);
    }

    /**
     * @return array<string, string>
     */
    private function body(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true);

        self::assertIsArray($decoded);

        /* @var array<string, string> $decoded */
        return $decoded;
    }
}
