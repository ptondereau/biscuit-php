<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\Auth\PrivateKey;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BiscuitTokenManager::class)]
final class BiscuitTokenManagerTest extends TestCase
{
    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBiscuitBuilder(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder();

        self::assertInstanceOf(BiscuitBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBiscuitBuilderWithCode(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("test_user")');

        self::assertInstanceOf(BiscuitBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBiscuitBuilderWithCodeAndParams(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user({user_id})', ['user_id' => 123]);

        self::assertInstanceOf(BiscuitBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBlockBuilder(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBlockBuilder();

        self::assertInstanceOf(BlockBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBlockBuilderWithCode(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBlockBuilder('check if operation("read")');

        self::assertInstanceOf(BlockBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesBlockBuilderWithCodeAndParams(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBlockBuilder('check if resource({resource})', ['resource' => 'file1']);

        self::assertInstanceOf(BlockBuilder::class, $builder);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itBuildsBiscuitToken(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("test_user")');
        $biscuit = $manager->build($builder);

        self::assertInstanceOf(Biscuit::class, $biscuit);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itSerializesBiscuitToBase64(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("test_user")');
        $biscuit = $manager->build($builder);
        $serialized = $manager->serialize($biscuit);

        self::assertIsString($serialized);
        self::assertNotEmpty($serialized);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itParsesBiscuitFromBase64(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("test_user")');
        $originalBiscuit = $manager->build($builder);
        $serialized = $manager->serialize($originalBiscuit);

        $parsedBiscuit = $manager->parse($serialized);

        self::assertInstanceOf(Biscuit::class, $parsedBiscuit);
        self::assertSame($originalBiscuit->blockCount(), $parsedBiscuit->blockCount());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itAttenuatesBiscuitWithBlock(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("test_user")');
        $biscuit = $manager->build($builder);

        $block = $manager->createBlockBuilder('check if operation("read")');
        $attenuatedBiscuit = $manager->attenuate($biscuit, $block);

        self::assertInstanceOf(Biscuit::class, $attenuatedBiscuit);
        self::assertSame($biscuit->blockCount() + 1, $attenuatedBiscuit->blockCount());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itRoundTripsToken(): void
    {
        $manager = $this->createTokenManager();

        $builder = $manager->createBuilder('user("roundtrip_user")');
        $biscuit = $manager->build($builder);

        $serialized = $manager->serialize($biscuit);
        $parsed = $manager->parse($serialized);
        $reSerialized = $manager->serialize($parsed);

        self::assertSame($serialized, $reSerialized);
    }

    private function createTokenManager(): BiscuitTokenManager
    {
        $privateKey = PrivateKey::generate();

        $keyManager = new KeyManager(
            $privateKey->getPublicKey()->toHex(),
            $privateKey->toHex(),
            null,
            null,
            'ed25519',
        );

        return new BiscuitTokenManager($keyManager);
    }
}
