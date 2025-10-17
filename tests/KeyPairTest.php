<?php

namespace Biscuit\Tests;

use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use PHPUnit\Framework\TestCase;

class KeyPairTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $keyPair = new KeyPair();

        $this->assertInstanceOf(KeyPair::class, $keyPair);
        $this->assertInstanceOf(PublicKey::class, $keyPair->public());
        $this->assertInstanceOf(PrivateKey::class, $keyPair->private());
        $this->assertIsString($keyPair->public()->toHex());
    }

    public function testNewWithAlgorithmDefault(): void
    {
        $keyPair = KeyPair::newWithAlgorithm();

        $this->assertInstanceOf(KeyPair::class, $keyPair);
        $this->assertStringStartsWith('ed25519/', $keyPair->public()->toHex());
    }

    public function testNewWithAlgorithmEd25519(): void
    {
        $keyPair = KeyPair::newWithAlgorithm(0);

        $this->assertInstanceOf(KeyPair::class, $keyPair);
        $this->assertStringStartsWith('ed25519/', $keyPair->public()->toHex());
    }

    public function testNewWithAlgorithmSecp256r1(): void
    {
        $keyPair = KeyPair::newWithAlgorithm(1);

        $this->assertInstanceOf(KeyPair::class, $keyPair);
        $this->assertStringStartsWith('secp256r1/', $keyPair->public()->toHex());
    }

    public function testFromPrivateKey(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $keyPair = KeyPair::fromPrivateKey($privateKey);

        $this->assertInstanceOf(KeyPair::class, $keyPair);
        $this->assertEquals($privateKeyHex, $keyPair->private()->toHex());
    }

    public function testFromPrivateKeyRoundTrip(): void
    {
        $originalKeyPair = new KeyPair();
        $privateKey = $originalKeyPair->private();

        $reconstructedKeyPair = KeyPair::fromPrivateKey($privateKey);

        $this->assertEquals(
            $originalKeyPair->public()->toHex(),
            $reconstructedKeyPair->public()->toHex()
        );
        $this->assertEquals(
            $originalKeyPair->private()->toHex(),
            $reconstructedKeyPair->private()->toHex()
        );
    }

    public function testPrivateKeyConstruction(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $this->assertInstanceOf(PrivateKey::class, $privateKey);
        $this->assertEquals($privateKeyHex, $privateKey->toHex());
    }

    public function testInvalidPrivateKeyException(): void
    {
        $this->expectException(\Exception::class);

        new PrivateKey('invalid-key-format');
    }

    public function testPrivateKeyToBytes(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();

        $this->assertIsArray($bytes);
        $this->assertNotEmpty($bytes);
        $this->assertCount(32, $bytes);
    }

    public function testPrivateKeyFromBytes(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();
        $reconstructed = PrivateKey::fromBytes(pack('C*', ...$bytes));

        $this->assertEquals($privateKeyHex, $reconstructed->toHex());
    }

    public function testPrivateKeyFromBytesWithExplicitAlgorithm(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();
        $reconstructed = PrivateKey::fromBytes(pack('C*', ...$bytes), 0);

        $this->assertEquals($privateKeyHex, $reconstructed->toHex());
    }

    public function testPrivateKeyFromPem(): void
    {
        $privatePem = "-----BEGIN PRIVATE KEY-----\nMC4CAQAwBQYDK2VwBCIEIASZaU0NoF3KxABSZj5x1QwVOUZfiSbf6SAzz3qq1T1l\n-----END PRIVATE KEY-----";
        $expectedHex = "ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65";

        $privateKey = PrivateKey::fromPem($privatePem);

        $this->assertInstanceOf(PrivateKey::class, $privateKey);
        $this->assertEquals($expectedHex, $privateKey->toHex());
    }

    public function testPrivateKeyFromDer(): void
    {
        $privateDer = hex2bin("302e020100300506032b6570042204200499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65");
        $expectedHex = "ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65";

        $privateKey = PrivateKey::fromDer($privateDer);

        $this->assertInstanceOf(PrivateKey::class, $privateKey);
        $this->assertEquals($expectedHex, $privateKey->toHex());
    }

    public function testPrivateKeyToString(): void
    {
        $privateKeyHex = "ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97";
        $privateKey = new PrivateKey($privateKeyHex);

        $this->assertEquals($privateKeyHex, (string)$privateKey);
    }

    public function testPublicKeyConstruction(): void
    {
        $publicKeyHex = "ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189";
        $publicKey = new PublicKey($publicKeyHex);

        $this->assertInstanceOf(PublicKey::class, $publicKey);
        $this->assertEquals($publicKeyHex, $publicKey->toHex());
    }

    public function testPublicKeyToBytes(): void
    {
        $publicKeyHex = "ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189";
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();

        $this->assertIsArray($bytes);
        $this->assertNotEmpty($bytes);
        $this->assertCount(32, $bytes);
    }

    public function testPublicKeyFromBytes(): void
    {
        $publicKeyHex = "ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189";
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();
        $reconstructed = PublicKey::fromBytes(pack('C*', ...$bytes));

        $this->assertEquals($publicKeyHex, $reconstructed->toHex());
    }

    public function testPublicKeyFromBytesWithExplicitAlgorithm(): void
    {
        $publicKeyHex = "ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189";
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();
        $reconstructed = PublicKey::fromBytes(pack('C*', ...$bytes), 0);

        $this->assertEquals($publicKeyHex, $reconstructed->toHex());
    }

    public function testPublicKeyFromKeyPair(): void
    {
        $keyPair = new KeyPair();
        $publicKey = $keyPair->public();

        $this->assertInstanceOf(PublicKey::class, $publicKey);
        $this->assertStringStartsWith('ed25519/', $publicKey->toHex());
    }

    public function testPublicKeyToString(): void
    {
        $publicKeyHex = "ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189";
        $publicKey = new PublicKey($publicKeyHex);

        $this->assertEquals($publicKeyHex, (string)$publicKey);
    }

    public function testKeyPairPublicPrivateConsistency(): void
    {
        $keyPair = new KeyPair();
        $publicKey = $keyPair->public();
        $privateKey = $keyPair->private();

        $this->assertMatchesRegularExpression('/^ed25519\/[0-9a-f]{64}$/', $publicKey->toHex());
        $this->assertMatchesRegularExpression('/^ed25519-private\/[0-9a-f]{64}$/', $privateKey->toHex());

        $reconstructed = KeyPair::fromPrivateKey($privateKey);
        $this->assertEquals($publicKey->toHex(), $reconstructed->public()->toHex());
    }

    public function testMultipleKeyPairsAreUnique(): void
    {
        $keyPair1 = new KeyPair();
        $keyPair2 = new KeyPair();

        $this->assertNotEquals(
            $keyPair1->public()->toHex(),
            $keyPair2->public()->toHex(),
            'Different KeyPair instances should generate different keys'
        );

        $this->assertNotEquals(
            $keyPair1->private()->toHex(),
            $keyPair2->private()->toHex(),
            'Different KeyPair instances should generate different private keys'
        );
    }

    public function testKeySerializationRoundTrip(): void
    {
        $originalKeyPair = new KeyPair();

        $publicBytes = $originalKeyPair->public()->toBytes();
        $publicReconstructed = PublicKey::fromBytes(pack('C*', ...$publicBytes));
        $this->assertEquals($originalKeyPair->public()->toHex(), $publicReconstructed->toHex());

        $privateBytes = $originalKeyPair->private()->toBytes();
        $privateReconstructed = PrivateKey::fromBytes(pack('C*', ...$privateBytes));
        $this->assertEquals($originalKeyPair->private()->toHex(), $privateReconstructed->toHex());

        $reconstructedKeyPair = KeyPair::fromPrivateKey($privateReconstructed);
        $this->assertEquals($originalKeyPair->public()->toHex(), $reconstructedKeyPair->public()->toHex());
    }
}
