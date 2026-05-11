<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\Exception\BiscuitException;
use Biscuit\Exception\KeyException;
use Biscuit\Exception\PrivateKeyException;
use Biscuit\Exception\PublicKeyException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class KeyExceptionTest extends TestCase
{
    #[Test]
    public function publicKeyExceptionExtendsKeyExceptionAndBiscuitException(): void
    {
        static::assertTrue(is_subclass_of(PublicKeyException::class, KeyException::class));
        static::assertTrue(is_subclass_of(PublicKeyException::class, BiscuitException::class));
    }

    #[Test]
    public function publicKeyParseFailureThrowsPublicKeyException(): void
    {
        $this->expectException(PublicKeyException::class);

        new PublicKey('not-a-valid-public-key');
    }

    #[Test]
    public function privateKeyExceptionExtendsKeyExceptionAndBiscuitException(): void
    {
        static::assertTrue(is_subclass_of(PrivateKeyException::class, KeyException::class));
        static::assertTrue(is_subclass_of(PrivateKeyException::class, BiscuitException::class));
    }

    #[Test]
    public function privateKeyParseFailureThrowsPrivateKeyException(): void
    {
        $this->expectException(PrivateKeyException::class);

        new PrivateKey('not-a-valid-private-key');
    }
}
