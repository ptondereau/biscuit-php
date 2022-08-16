<?php

namespace Biscuit\Tests;

use Biscuit\Auth\KeyPair;
use PHPUnit\Framework\TestCase;

class KeyPairTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $keyPair = new KeyPair();

        self::assertIsString($keyPair->getPublicKey());
        self::assertStringStartsWith('ed25519/', $keyPair->getPublicKey());
    }
}
