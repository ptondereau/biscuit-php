<?php

namespace Biscuit\Tests;

use Biscuit\Auth\KeyPair;
use Biscuit\Exception\InvalidPrivateKey;
use PHPUnit\Framework\TestCase;

class KeyPairTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $keyPair = new KeyPair();

        self::assertIsString($keyPair->public());
        self::assertStringStartsWith('ed25519/', $keyPair->public());
    }

    public function testInvalidPrivateKeyException(): void
    {
        $this->expectException(InvalidPrivateKey::class);
        $this->expectExceptionMessage('invalid key size');

        KeyPair::fromPrivateKey('test');
    }

    public function testFromPrivateKey(): void
    {
        $keyPair = KeyPair::fromPrivateKey('12345678912345678912345678912345');

        self::assertIsString($keyPair->public());
        self::assertStringStartsWith('ed25519/', $keyPair->public());
    }
}
