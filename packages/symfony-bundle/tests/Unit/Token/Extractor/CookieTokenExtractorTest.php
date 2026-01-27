<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Extractor;

use Biscuit\BiscuitBundle\Token\Extractor\CookieTokenExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CookieTokenExtractor::class)]
final class CookieTokenExtractorTest extends TestCase
{
    #[Test]
    public function itExtractsTokenFromCookie(): void
    {
        $extractor = new CookieTokenExtractor('biscuit_token');

        $request = new Request([], [], [], ['biscuit_token' => 'my_secret_token']);

        $token = $extractor->extract($request);

        self::assertSame('my_secret_token', $token);
    }

    #[Test]
    public function itReturnsNullWhenCookieNotPresent(): void
    {
        $extractor = new CookieTokenExtractor('biscuit_token');

        $request = new Request();

        $token = $extractor->extract($request);

        self::assertNull($token);
    }

    #[Test]
    public function itReturnsNullWhenDifferentCookiePresent(): void
    {
        $extractor = new CookieTokenExtractor('biscuit_token');

        $request = new Request([], [], [], ['other_cookie' => 'value']);

        $token = $extractor->extract($request);

        self::assertNull($token);
    }

    #[Test]
    public function itSupportsRequestWithMatchingCookie(): void
    {
        $extractor = new CookieTokenExtractor('auth_cookie');

        $request = new Request([], [], [], ['auth_cookie' => 'token']);

        self::assertTrue($extractor->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestWithoutMatchingCookie(): void
    {
        $extractor = new CookieTokenExtractor('auth_cookie');

        $request = new Request();

        self::assertFalse($extractor->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestWithDifferentCookie(): void
    {
        $extractor = new CookieTokenExtractor('auth_cookie');

        $request = new Request([], [], [], ['different_cookie' => 'token']);

        self::assertFalse($extractor->supports($request));
    }

    #[Test]
    public function itHandlesEmptyCookieValue(): void
    {
        $extractor = new CookieTokenExtractor('biscuit_token');

        $request = new Request([], [], [], ['biscuit_token' => '']);

        self::assertTrue($extractor->supports($request));
        self::assertSame('', $extractor->extract($request));
    }

    #[Test]
    public function itUsesConfiguredCookieName(): void
    {
        $extractor = new CookieTokenExtractor('custom_cookie_name');

        $request = new Request([], [], [], ['custom_cookie_name' => 'the_token']);

        $token = $extractor->extract($request);

        self::assertSame('the_token', $token);
    }
}
