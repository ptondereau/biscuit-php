<?php

namespace Biscuit\Tests;

use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Exception\InvalidPrivateKey;
use PHPUnit\Framework\TestCase;

class KeyPairTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $keyPair = new KeyPair();

        self::assertIsString($keyPair->public()->toHex());
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

        self::assertEquals(
            'eb24ffe8cd3ce08e856d010186435e3d364e77c72360e5ddd6938c10d65786bc',
            $keyPair->public()->toHex()
        );
        self::assertEquals(
            new PrivateKey('12345678912345678912345678912345'),
            $keyPair->private()
        );
    }
}
