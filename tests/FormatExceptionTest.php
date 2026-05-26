<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Authorizer;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\Exception\Base64Exception;
use Biscuit\Exception\BiscuitException;
use Biscuit\Exception\BytesException;
use Biscuit\Exception\FormatException;
use Biscuit\Exception\SignatureException;
use Biscuit\Exception\SnapshotException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormatExceptionTest extends TestCase
{
    #[Test]
    public function base64ExceptionExtendsFormatExceptionAndBiscuitException(): void
    {
        static::assertTrue(is_subclass_of(Base64Exception::class, FormatException::class));
        static::assertTrue(is_subclass_of(Base64Exception::class, BiscuitException::class));
    }

    #[Test]
    public function invalidBase64TokenThrowsBase64Exception(): void
    {
        $keyPair = new KeyPair();
        $rootKey = $keyPair->getPublicKey();
        $this->expectException(Base64Exception::class);

        Biscuit::fromBase64('this-is-not-valid-base64-token-data!!', $rootKey);
    }

    #[Test]
    public function bytesExceptionExtendsFormatException(): void
    {
        static::assertTrue(is_subclass_of(BytesException::class, FormatException::class));
    }

    #[Test]
    public function invalidByteSequenceThrowsBytesException(): void
    {
        $keyPair = new KeyPair();
        $rootKey = $keyPair->getPublicKey();
        $this->expectException(BytesException::class);

        Biscuit::fromBytes('garbage-bytes', $rootKey);
    }

    #[Test]
    public function signatureExceptionExtendsFormatException(): void
    {
        static::assertTrue(is_subclass_of(SignatureException::class, FormatException::class));
    }

    #[Test]
    public function verifyingWithWrongRootKeyThrowsSignatureException(): void
    {
        $rootA = new KeyPair();
        $rootB = new KeyPair();

        $builder = new BiscuitBuilder('user("alice");');
        $biscuit = $builder->build($rootA->getPrivateKey());
        $token = $biscuit->toBase64();

        $unverified = UnverifiedBiscuit::fromBase64($token);

        $this->expectException(SignatureException::class);
        $unverified->verify($rootB->getPublicKey());
    }

    #[Test]
    public function snapshotExceptionExtendsFormatException(): void
    {
        static::assertTrue(is_subclass_of(SnapshotException::class, FormatException::class));
    }

    #[Test]
    public function invalidBase64SnapshotThrowsSnapshotException(): void
    {
        $this->expectException(SnapshotException::class);

        Authorizer::fromBase64Snapshot('not-a-real-snapshot-payload');
    }
}
