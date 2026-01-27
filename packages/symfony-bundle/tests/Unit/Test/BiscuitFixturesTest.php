<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Test;

use Biscuit\BiscuitBundle\Test\BiscuitFixtures;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(BiscuitFixtures::class)]
final class BiscuitFixturesTest extends TestCase
{
    use BiscuitTestTrait;

    protected function tearDown(): void
    {
        self::resetTestKeyPair();
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itGetsTokenByName(): void
    {
        $token = $this->createTestToken('user("fixture_test")');
        $fixtures = new BiscuitFixtures(
            ['test_token' => $token],
            self::getTestPublicKey(),
        );

        $retrievedToken = $fixtures->getToken('test_token');

        self::assertSame($token, $retrievedToken);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itGetsTokenBase64ByName(): void
    {
        $token = $this->createTestToken('user("base64_test")');
        $fixtures = new BiscuitFixtures(
            ['test_token' => $token],
            self::getTestPublicKey(),
        );

        $base64 = $fixtures->getTokenBase64('test_token');

        self::assertIsString($base64);
        self::assertSame($token->toBase64(), $base64);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itThrowsExceptionForUnknownToken(): void
    {
        $fixtures = new BiscuitFixtures([], self::getTestPublicKey());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Token "unknown" not found in fixtures');

        $fixtures->getToken('unknown');
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itChecksIfTokenExists(): void
    {
        $token = $this->createTestToken();
        $fixtures = new BiscuitFixtures(
            ['existing' => $token],
            self::getTestPublicKey(),
        );

        self::assertTrue($fixtures->hasToken('existing'));
        self::assertFalse($fixtures->hasToken('non_existing'));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itGetsAllTokenNames(): void
    {
        $token1 = $this->createTestToken('user("user1")');
        $token2 = $this->createTestToken('user("user2")');
        $fixtures = new BiscuitFixtures(
            ['token1' => $token1, 'token2' => $token2],
            self::getTestPublicKey(),
        );

        $names = $fixtures->getTokenNames();

        self::assertCount(2, $names);
        self::assertContains('token1', $names);
        self::assertContains('token2', $names);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itGetsAllTokens(): void
    {
        $token1 = $this->createTestToken('user("user1")');
        $token2 = $this->createTestToken('user("user2")');
        $fixtures = new BiscuitFixtures(
            ['token1' => $token1, 'token2' => $token2],
            self::getTestPublicKey(),
        );

        $allTokens = $fixtures->getAllTokens();

        self::assertCount(2, $allTokens);
        self::assertSame($token1, $allTokens['token1']);
        self::assertSame($token2, $allTokens['token2']);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itGetsPublicKey(): void
    {
        $publicKey = self::getTestPublicKey();
        $fixtures = new BiscuitFixtures([], $publicKey);

        self::assertSame($publicKey, $fixtures->getPublicKey());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCountsTokens(): void
    {
        $token1 = $this->createTestToken('user("user1")');
        $token2 = $this->createTestToken('user("user2")');
        $token3 = $this->createTestToken('user("user3")');
        $fixtures = new BiscuitFixtures(
            ['token1' => $token1, 'token2' => $token2, 'token3' => $token3],
            self::getTestPublicKey(),
        );

        self::assertSame(3, $fixtures->count());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itReturnsZeroCountForEmptyFixtures(): void
    {
        $fixtures = new BiscuitFixtures([], self::getTestPublicKey());

        self::assertSame(0, $fixtures->count());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itShowsAvailableTokensInErrorMessage(): void
    {
        $token = $this->createTestToken();
        $fixtures = new BiscuitFixtures(
            ['token_a' => $token, 'token_b' => $token],
            self::getTestPublicKey(),
        );

        try {
            $fixtures->getToken('unknown');
            self::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('token_a', $e->getMessage());
            self::assertStringContainsString('token_b', $e->getMessage());
        }
    }
}
