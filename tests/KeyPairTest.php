<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Algorithm;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use PHPUnit\Framework\TestCase;

class KeyPairTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $keyPair = new KeyPair();

        static::assertInstanceOf(KeyPair::class, $keyPair);
        static::assertInstanceOf(PublicKey::class, $keyPair->getPublicKey());
        static::assertInstanceOf(PrivateKey::class, $keyPair->getPrivateKey());
        static::assertIsString($keyPair->getPublicKey()->toHex());
    }

    public function testNewWithAlgorithmDefault(): void
    {
        $keyPair = KeyPair::newWithAlgorithm();

        static::assertInstanceOf(KeyPair::class, $keyPair);
        static::assertStringStartsWith('ed25519/', $keyPair->getPublicKey()->toHex());
    }

    public function testNewWithAlgorithmEd25519(): void
    {
        $keyPair = KeyPair::newWithAlgorithm(Algorithm::Ed25519);

        static::assertInstanceOf(KeyPair::class, $keyPair);
        static::assertStringStartsWith('ed25519/', $keyPair->getPublicKey()->toHex());
    }

    public function testNewWithAlgorithmSecp256r1(): void
    {
        $keyPair = KeyPair::newWithAlgorithm(Algorithm::Secp256r1);

        static::assertInstanceOf(KeyPair::class, $keyPair);
        static::assertStringStartsWith('secp256r1/', $keyPair->getPublicKey()->toHex());
    }

    public function testFromPrivateKey(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        $keyPair = KeyPair::fromPrivateKey($privateKey);

        static::assertInstanceOf(KeyPair::class, $keyPair);
        static::assertSame($privateKeyHex, $keyPair->getPrivateKey()->toHex());
    }

    public function testFromPrivateKeyRoundTrip(): void
    {
        $originalKeyPair = new KeyPair();
        $privateKey = $originalKeyPair->getPrivateKey();

        $reconstructedKeyPair = KeyPair::fromPrivateKey($privateKey);

        static::assertSame($originalKeyPair->getPublicKey()->toHex(), $reconstructedKeyPair->getPublicKey()->toHex());
        static::assertSame($originalKeyPair->getPrivateKey()->toHex(), $reconstructedKeyPair->getPrivateKey()->toHex());
    }

    public function testPrivateKeyConstruction(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        static::assertInstanceOf(PrivateKey::class, $privateKey);
        static::assertSame($privateKeyHex, $privateKey->toHex());
    }

    public function testInvalidPrivateKeyException(): void
    {
        $this->expectException(\Exception::class);

        new PrivateKey('invalid-key-format');
    }

    public function testPrivateKeyToBytes(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();

        static::assertIsArray($bytes);
        static::assertNotEmpty($bytes);
        static::assertCount(32, $bytes);
    }

    public function testPrivateKeyFromBytes(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();
        $reconstructed = PrivateKey::fromBytes(pack('C*', ...$bytes));

        static::assertSame($privateKeyHex, $reconstructed->toHex());
    }

    public function testPrivateKeyFromBytesWithExplicitAlgorithm(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();
        $reconstructed = PrivateKey::fromBytes(pack('C*', ...$bytes), Algorithm::Ed25519);

        static::assertSame($privateKeyHex, $reconstructed->toHex());
    }

    public function testPrivateKeyFromPem(): void
    {
        $privatePem = "-----BEGIN PRIVATE KEY-----\nMC4CAQAwBQYDK2VwBCIEIASZaU0NoF3KxABSZj5x1QwVOUZfiSbf6SAzz3qq1T1l\n-----END PRIVATE KEY-----";
        $expectedHex = 'ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65';

        $privateKey = PrivateKey::fromPem($privatePem);

        static::assertInstanceOf(PrivateKey::class, $privateKey);
        static::assertSame($expectedHex, $privateKey->toHex());
    }

    public function testPrivateKeyFromDer(): void
    {
        $privateDer = hex2bin(
            '302e020100300506032b6570042204200499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65',
        );
        $expectedHex = 'ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65';

        $privateKey = PrivateKey::fromDer($privateDer);

        static::assertInstanceOf(PrivateKey::class, $privateKey);
        static::assertSame($expectedHex, $privateKey->toHex());
    }

    public function testPrivateKeyToString(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        static::assertSame($privateKeyHex, (string) $privateKey);
    }

    public function testPublicKeyConstruction(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        static::assertInstanceOf(PublicKey::class, $publicKey);
        static::assertSame($publicKeyHex, $publicKey->toHex());
    }

    public function testPublicKeyToBytes(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();

        static::assertIsArray($bytes);
        static::assertNotEmpty($bytes);
        static::assertCount(32, $bytes);
    }

    public function testPublicKeyFromBytes(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();
        $reconstructed = PublicKey::fromBytes(pack('C*', ...$bytes));

        static::assertSame($publicKeyHex, $reconstructed->toHex());
    }

    public function testPublicKeyFromBytesWithExplicitAlgorithm(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();
        $reconstructed = PublicKey::fromBytes(pack('C*', ...$bytes), Algorithm::Ed25519);

        static::assertSame($publicKeyHex, $reconstructed->toHex());
    }

    public function testPublicKeyFromKeyPair(): void
    {
        $keyPair = new KeyPair();
        $publicKey = $keyPair->getPublicKey();

        static::assertInstanceOf(PublicKey::class, $publicKey);
        static::assertStringStartsWith('ed25519/', $publicKey->toHex());
    }

    public function testPublicKeyToString(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        static::assertSame($publicKeyHex, (string) $publicKey);
    }

    public function testKeyPairPublicPrivateConsistency(): void
    {
        $keyPair = new KeyPair();
        $publicKey = $keyPair->getPublicKey();
        $privateKey = $keyPair->getPrivateKey();

        static::assertMatchesRegularExpression('/^ed25519\/[0-9a-f]{64}$/', $publicKey->toHex());
        static::assertMatchesRegularExpression('/^ed25519-private\/[0-9a-f]{64}$/', $privateKey->toHex());

        $reconstructed = KeyPair::fromPrivateKey($privateKey);
        static::assertSame($publicKey->toHex(), $reconstructed->getPublicKey()->toHex());
    }

    public function testMultipleKeyPairsAreUnique(): void
    {
        $keyPair1 = new KeyPair();
        $keyPair2 = new KeyPair();

        static::assertNotSame(
            $keyPair1->getPublicKey()->toHex(),
            $keyPair2->getPublicKey()->toHex(),
            'Different KeyPair instances should generate different keys',
        );

        static::assertNotSame(
            $keyPair1->getPrivateKey()->toHex(),
            $keyPair2->getPrivateKey()->toHex(),
            'Different KeyPair instances should generate different private keys',
        );
    }

    public function testKeySerializationRoundTrip(): void
    {
        $originalKeyPair = new KeyPair();

        $publicBytes = $originalKeyPair->getPublicKey()->toBytes();
        $publicReconstructed = PublicKey::fromBytes(pack('C*', ...$publicBytes));
        static::assertSame($originalKeyPair->getPublicKey()->toHex(), $publicReconstructed->toHex());

        $privateBytes = $originalKeyPair->getPrivateKey()->toBytes();
        $privateReconstructed = PrivateKey::fromBytes(pack('C*', ...$privateBytes));
        static::assertSame($originalKeyPair->getPrivateKey()->toHex(), $privateReconstructed->toHex());

        $reconstructedKeyPair = KeyPair::fromPrivateKey($privateReconstructed);
        static::assertSame($originalKeyPair->getPublicKey()->toHex(), $reconstructedKeyPair->getPublicKey()->toHex());
    }
}
