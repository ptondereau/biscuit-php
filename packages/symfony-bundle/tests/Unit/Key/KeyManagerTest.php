<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Key;

use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\BiscuitBundle\Key\KeyManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(KeyManager::class)]
final class KeyManagerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/biscuit_test_' . uniqid();
        mkdir($this->tempDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if (false !== $files) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    #[Test]
    public function itReportsPublicKeyAvailableFromHex(): void
    {
        $manager = new KeyManager(
            'abc123',
            null,
            null,
            null,
        );

        self::assertTrue($manager->hasPublicKey());
    }

    #[Test]
    public function itReportsPublicKeyAvailableFromFile(): void
    {
        $manager = new KeyManager(
            null,
            null,
            '/path/to/key.pem',
            null,
        );

        self::assertTrue($manager->hasPublicKey());
    }

    #[Test]
    public function itReportsPublicKeyNotAvailable(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            null,
        );

        self::assertFalse($manager->hasPublicKey());
    }

    #[Test]
    public function itReportsPrivateKeyAvailableFromHex(): void
    {
        $manager = new KeyManager(
            null,
            'def456',
            null,
            null,
        );

        self::assertTrue($manager->hasPrivateKey());
    }

    #[Test]
    public function itReportsPrivateKeyAvailableFromFile(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            '/path/to/private.pem',
        );

        self::assertTrue($manager->hasPrivateKey());
    }

    #[Test]
    public function itReportsPrivateKeyNotAvailable(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            null,
        );

        self::assertFalse($manager->hasPrivateKey());
    }

    #[Test]
    public function itThrowsWhenPublicKeyNotConfigured(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No public key configured');

        $manager->getPublicKey();
    }

    #[Test]
    public function itThrowsWhenPrivateKeyNotConfigured(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No private key configured');

        $manager->getPrivateKey();
    }

    #[Test]
    public function itThrowsWhenPublicKeyFileNotFound(): void
    {
        $manager = new KeyManager(
            null,
            null,
            '/non/existent/path.pem',
            null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key file not found');

        $manager->getPublicKey();
    }

    #[Test]
    public function itThrowsWhenPrivateKeyFileNotFound(): void
    {
        $manager = new KeyManager(
            null,
            null,
            null,
            '/non/existent/path.pem',
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key file not found');

        $manager->getPrivateKey();
    }

    #[Test]
    public function itLoadsPublicKeyFromHexString(): void
    {
        $privateKey = PrivateKey::generate();
        $publicKeyHex = $privateKey->getPublicKey()->toHex();

        $manager = new KeyManager(
            $publicKeyHex,
            null,
            null,
            null,
        );

        $publicKey = $manager->getPublicKey();

        self::assertInstanceOf(PublicKey::class, $publicKey);
        self::assertSame($publicKeyHex, $publicKey->toHex());
    }

    #[Test]
    public function itLoadsPrivateKeyFromHexString(): void
    {
        $originalPrivateKey = PrivateKey::generate();
        $privateKeyHex = $originalPrivateKey->toHex();

        $manager = new KeyManager(
            null,
            $privateKeyHex,
            null,
            null,
        );

        $privateKey = $manager->getPrivateKey();

        self::assertInstanceOf(PrivateKey::class, $privateKey);
        self::assertSame($privateKeyHex, $privateKey->toHex());
    }

    #[Test]
    public function itCachesPublicKey(): void
    {
        $privateKey = PrivateKey::generate();
        $publicKeyHex = $privateKey->getPublicKey()->toHex();

        $manager = new KeyManager(
            $publicKeyHex,
            null,
            null,
            null,
        );

        $firstCall = $manager->getPublicKey();
        $secondCall = $manager->getPublicKey();

        self::assertSame($firstCall, $secondCall);
    }

    #[Test]
    public function itCachesPrivateKey(): void
    {
        $originalPrivateKey = PrivateKey::generate();
        $privateKeyHex = $originalPrivateKey->toHex();

        $manager = new KeyManager(
            null,
            $privateKeyHex,
            null,
            null,
        );

        $firstCall = $manager->getPrivateKey();
        $secondCall = $manager->getPrivateKey();

        self::assertSame($firstCall, $secondCall);
    }

    #[Test]
    public function itLoadsPublicKeyFromPemFile(): void
    {
        $publicPem = "-----BEGIN PUBLIC KEY-----\nMCowBQYDK2VwAyEAqIR/FDhIoNgaC4g2B+miJll8qDV9pVYVGfPuNFz1Omw=\n-----END PUBLIC KEY-----";
        $expectedHex = 'ed25519/a8847f143848a0d81a0b883607e9a226597ca8357da5561519f3ee345cf53a6c';

        $pemPath = $this->tempDir . '/public.pem';
        file_put_contents($pemPath, $publicPem);

        $manager = new KeyManager(
            null,
            null,
            $pemPath,
            null,
        );

        $loadedKey = $manager->getPublicKey();

        self::assertInstanceOf(PublicKey::class, $loadedKey);
        self::assertSame($expectedHex, $loadedKey->toHex());
    }

    #[Test]
    public function itLoadsPrivateKeyFromPemFile(): void
    {
        $privatePem = "-----BEGIN PRIVATE KEY-----\nMC4CAQAwBQYDK2VwBCIEIASZaU0NoF3KxABSZj5x1QwVOUZfiSbf6SAzz3qq1T1l\n-----END PRIVATE KEY-----";
        $expectedHex = 'ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65';

        $pemPath = $this->tempDir . '/private.pem';
        file_put_contents($pemPath, $privatePem);

        $manager = new KeyManager(
            null,
            null,
            null,
            $pemPath,
        );

        $loadedKey = $manager->getPrivateKey();

        self::assertInstanceOf(PrivateKey::class, $loadedKey);
        self::assertSame($expectedHex, $loadedKey->toHex());
    }

    #[Test]
    public function itPrefersHexKeyOverFileKey(): void
    {
        $hexPrivateKey = PrivateKey::generate();
        $hexPublicKey = $hexPrivateKey->getPublicKey()->toHex();

        // Use a different key in the PEM file to verify hex takes precedence
        $filePem = "-----BEGIN PUBLIC KEY-----\nMCowBQYDK2VwAyEAqIR/FDhIoNgaC4g2B+miJll8qDV9pVYVGfPuNFz1Omw=\n-----END PUBLIC KEY-----";

        $pemPath = $this->tempDir . '/public.pem';
        file_put_contents($pemPath, $filePem);

        $manager = new KeyManager(
            $hexPublicKey,
            null,
            $pemPath,
            null,
        );

        $loadedKey = $manager->getPublicKey();

        self::assertSame($hexPublicKey, $loadedKey->toHex());
    }
}
