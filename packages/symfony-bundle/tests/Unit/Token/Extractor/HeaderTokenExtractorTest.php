<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Extractor;

use Biscuit\BiscuitBundle\Token\Extractor\HeaderTokenExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(HeaderTokenExtractor::class)]
final class HeaderTokenExtractorTest extends TestCase
{
    private HeaderTokenExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new HeaderTokenExtractor();
    }

    #[Test]
    public function itExtractsTokenFromBearerHeader(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer my_secret_token');

        $token = $this->extractor->extract($request);

        self::assertSame('my_secret_token', $token);
    }

    #[Test]
    public function itReturnsNullWhenNoAuthorizationHeader(): void
    {
        $request = new Request();

        $token = $this->extractor->extract($request);

        self::assertNull($token);
    }

    #[Test]
    public function itReturnsNullWhenAuthorizationHeaderDoesNotStartWithBearer(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Basic dXNlcjpwYXNz');

        $token = $this->extractor->extract($request);

        self::assertNull($token);
    }

    #[Test]
    public function itReturnsEmptyStringWhenBearerPrefixOnly(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ');

        $token = $this->extractor->extract($request);

        self::assertSame('', $token);
    }

    #[Test]
    public function itSupportsBearerAuthorizationHeader(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer token');

        self::assertTrue($this->extractor->supports($request));
    }

    #[Test]
    public function itDoesNotSupportMissingAuthorizationHeader(): void
    {
        $request = new Request();

        self::assertFalse($this->extractor->supports($request));
    }

    #[Test]
    public function itDoesNotSupportNonBearerAuthorizationHeader(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Basic dXNlcjpwYXNz');

        self::assertFalse($this->extractor->supports($request));
    }

    #[Test]
    public function itIsCaseSensitiveForBearerPrefix(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'bearer token');

        self::assertFalse($this->extractor->supports($request));
        self::assertNull($this->extractor->extract($request));
    }

    #[Test]
    public function itHandlesTokenWithSpaces(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer token with spaces');

        $token = $this->extractor->extract($request);

        self::assertSame('token with spaces', $token);
    }
}
