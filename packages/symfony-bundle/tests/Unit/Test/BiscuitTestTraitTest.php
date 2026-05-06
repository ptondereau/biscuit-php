<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Test;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BiscuitTestTrait::class)]
final class BiscuitTestTraitTest extends TestCase
{
    use BiscuitTestTrait;

    protected function tearDown(): void
    {
        self::resetTestKeyPair();
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itProvidesTestKeyPair(): void
    {
        $keyPair = self::getTestKeyPair();

        self::assertInstanceOf(KeyPair::class, $keyPair);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itReturnsSameKeyPairOnMultipleCalls(): void
    {
        $keyPair1 = self::getTestKeyPair();
        $keyPair2 = self::getTestKeyPair();

        self::assertSame($keyPair1, $keyPair2);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itProvidesTestPublicKey(): void
    {
        $publicKey = self::getTestPublicKey();

        self::assertInstanceOf(PublicKey::class, $publicKey);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itProvidesTestPrivateKey(): void
    {
        $privateKey = self::getTestPrivateKey();

        self::assertInstanceOf(PrivateKey::class, $privateKey);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTestToken(): void
    {
        $token = $this->createTestToken();

        self::assertInstanceOf(Biscuit::class, $token);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTestTokenWithCustomCode(): void
    {
        $token = $this->createTestToken('user("custom_user"); role("admin")');

        self::assertInstanceOf(Biscuit::class, $token);
        $source = $token->blockSource(0);
        self::assertStringContainsString('user("custom_user")', $source);
        self::assertStringContainsString('role("admin")', $source);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTestTokenWithParams(): void
    {
        $token = $this->createTestToken('user({user_id})', ['user_id' => 'param_user']);

        self::assertInstanceOf(Biscuit::class, $token);
        $source = $token->blockSource(0);
        self::assertStringContainsString('user("param_user")', $source);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTestTokenBase64(): void
    {
        $base64 = $this->createTestTokenBase64();

        self::assertIsString($base64);
        self::assertNotEmpty($base64);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTestTokenBase64WithCustomCode(): void
    {
        $base64 = $this->createTestTokenBase64('user("base64_user")');

        self::assertIsString($base64);
        self::assertNotEmpty($base64);

        $parsed = Biscuit::fromBase64($base64, self::getTestPublicKey());
        $source = $parsed->blockSource(0);
        self::assertStringContainsString('user("base64_user")', $source);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itResetsTestKeyPair(): void
    {
        $keyPair1 = self::getTestKeyPair();
        self::resetTestKeyPair();
        $keyPair2 = self::getTestKeyPair();

        self::assertNotSame($keyPair1, $keyPair2);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreateValidTokenVerifiableWithPublicKey(): void
    {
        $token = $this->createTestToken('user("verify_test")');
        $base64 = $token->toBase64();

        $parsed = Biscuit::fromBase64($base64, self::getTestPublicKey());

        self::assertInstanceOf(Biscuit::class, $parsed);
        $source = $parsed->blockSource(0);
        self::assertStringContainsString('user("verify_test")', $source);
    }
}
