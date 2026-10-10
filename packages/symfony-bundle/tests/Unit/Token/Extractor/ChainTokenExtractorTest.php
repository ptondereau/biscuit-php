<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Extractor;

use Biscuit\BiscuitBundle\Token\Extractor\ChainTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\CookieTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\HeaderTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ChainTokenExtractor::class)]
final class ChainTokenExtractorTest extends TestCase
{
    #[Test]
    public function itReturnsNullWhenNoExtractors(): void
    {
        $extractor = new ChainTokenExtractor();

        $request = new Request();

        self::assertNull($extractor->extract($request));
    }

    #[Test]
    public function itDoesNotSupportWhenNoExtractors(): void
    {
        $extractor = new ChainTokenExtractor();

        $request = new Request();

        self::assertFalse($extractor->supports($request));
    }

    #[Test]
    public function itExtractsFromFirstMatchingExtractor(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer header_token');

        $token = $extractor->extract($request);

        self::assertSame('header_token', $token);
    }

    #[Test]
    public function itFallsBackToSecondExtractorWhenFirstDoesNotMatch(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request([], [], [], ['token' => 'cookie_token']);

        $token = $extractor->extract($request);

        self::assertSame('cookie_token', $token);
    }

    #[Test]
    public function itReturnsNullWhenNoExtractorMatches(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request();

        $token = $extractor->extract($request);

        self::assertNull($token);
    }

    #[Test]
    public function itSupportsWhenAnyExtractorSupports(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request([], [], [], ['token' => 'value']);

        self::assertTrue($extractor->supports($request));
    }

    #[Test]
    public function itDoesNotSupportWhenNoExtractorSupports(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request();

        self::assertFalse($extractor->supports($request));
    }

    #[Test]
    public function itPrioritizesFirstExtractorWhenBothMatch(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($headerExtractor, $cookieExtractor);

        $request = new Request([], [], [], ['token' => 'cookie_token']);
        $request->headers->set('Authorization', 'Bearer header_token');

        $token = $extractor->extract($request);

        self::assertSame('header_token', $token);
    }

    #[Test]
    public function itPrioritizesCookieWhenConfiguredFirst(): void
    {
        $headerExtractor = new HeaderTokenExtractor();
        $cookieExtractor = new CookieTokenExtractor('token');

        $extractor = new ChainTokenExtractor($cookieExtractor, $headerExtractor);

        $request = new Request([], [], [], ['token' => 'cookie_token']);
        $request->headers->set('Authorization', 'Bearer header_token');

        $token = $extractor->extract($request);

        self::assertSame('cookie_token', $token);
    }

    #[Test]
    public function itWorksWithCustomExtractors(): void
    {
        $customExtractor = new class implements TokenExtractorInterface {
            public function extract(Request $request): ?string
            {
                return $request->query->get('token');
            }

            public function supports(Request $request): bool
            {
                return $request->query->has('token');
            }
        };

        $extractor = new ChainTokenExtractor($customExtractor);

        $request = new Request(['token' => 'query_token']);

        $token = $extractor->extract($request);

        self::assertSame('query_token', $token);
    }

    #[Test]
    public function itStopsAtFirstNonNullResult(): void
    {
        $firstExtractor = $this->createMock(TokenExtractorInterface::class);
        $firstExtractor->expects(self::once())
            ->method('extract')
            ->willReturn('first_token');

        $secondExtractor = $this->createMock(TokenExtractorInterface::class);
        $secondExtractor->expects(self::never())
            ->method('extract');

        $extractor = new ChainTokenExtractor($firstExtractor, $secondExtractor);

        $token = $extractor->extract(new Request());

        self::assertSame('first_token', $token);
    }
}
