<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\PrivateKey;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Token\BiscuitBlockFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BiscuitBlockFactory::class)]
final class BiscuitBlockFactoryTest extends TestCase
{
    #[Test]
    public function attenuateAppliesTemplateAsNewBlock(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $factory = new BiscuitBlockFactory($tokenManager, new Applier(), [
            'read_only' => [
                'checks' => ['check if operation("read")'],
            ],
        ]);

        $parent = $this->buildSimpleToken($tokenManager);
        $derived = $factory->attenuate($parent, 'read_only');

        self::assertSame($parent->blockCount() + 1, $derived->blockCount());
        self::assertStringContainsString(
            'operation("read")',
            $derived->blockSource($derived->blockCount() - 1),
        );
    }

    #[Test]
    public function attenuateThrowsForUnknownTemplate(): void
    {
        $factory = new BiscuitBlockFactory(
            $this->createMock(BiscuitTokenManagerInterface::class),
            new Applier(),
            [],
        );

        $biscuit = $this->createMock(Biscuit::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown block template: missing');

        $factory->attenuate($biscuit, 'missing');
    }

    #[Test]
    public function hasTemplateDistinguishesRegisteredAndUnknownNames(): void
    {
        $factory = new BiscuitBlockFactory(
            $this->createMock(BiscuitTokenManagerInterface::class),
            new Applier(),
            ['known' => ['checks' => ['check if true']]],
        );

        self::assertTrue($factory->hasTemplate('known'));
        self::assertFalse($factory->hasTemplate('unknown'));
    }

    #[Test]
    public function getTemplateNamesReturnsRegisteredKeys(): void
    {
        $factory = new BiscuitBlockFactory(
            $this->createMock(BiscuitTokenManagerInterface::class),
            new Applier(),
            [
                'read_only' => ['checks' => ['check if operation("read")']],
                'expires' => ['checks' => ['check if now($t), $t <= {exp}']],
                'single_resource' => ['checks' => ['check if resource({res})']],
            ],
        );

        $names = $factory->getTemplateNames();

        self::assertCount(3, $names);
        self::assertContains('read_only', $names);
        self::assertContains('expires', $names);
        self::assertContains('single_resource', $names);
    }

    private function createRealTokenManager(): BiscuitTokenManager
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

    private function buildSimpleToken(BiscuitTokenManagerInterface $tokenManager): Biscuit
    {
        $builder = $tokenManager->createBuilder('user("alice")');

        return $tokenManager->build($builder);
    }
}
