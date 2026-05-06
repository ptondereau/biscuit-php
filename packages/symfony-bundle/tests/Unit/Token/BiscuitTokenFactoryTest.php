<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\PrivateKey;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BiscuitTokenFactory::class)]
final class BiscuitTokenFactoryTest extends TestCase
{
    #[Test]
    public function itThrowsForUnknownTemplate(): void
    {
        $factory = $this->createFactoryWithMockedManager([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown token template: unknown');

        $factory->create('unknown');
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTokenWithFactsFromTemplate(): void
    {
        $factory = $this->createFactory([
            'user_token' => [
                'facts' => ['user("test_user")'],
            ],
        ]);

        $biscuit = $factory->create('user_token');

        self::assertInstanceOf(Biscuit::class, $biscuit);
        self::assertStringContainsString('user("test_user")', $biscuit->blockSource(0));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTokenWithChecksFromTemplate(): void
    {
        $factory = $this->createFactory([
            'read_only' => [
                'facts' => ['user("reader")'],
                'checks' => ['check if operation("read")'],
            ],
        ]);

        $biscuit = $factory->create('read_only');

        self::assertInstanceOf(Biscuit::class, $biscuit);
        self::assertStringContainsString('check if operation("read")', $biscuit->blockSource(0));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itCreatesTokenWithRulesFromTemplate(): void
    {
        $factory = $this->createFactory([
            'with_rules' => [
                'facts' => ['admin(true)'],
                'rules' => ['right($resource, $operation) <- admin(true), resource($resource), operation($operation)'],
            ],
        ]);

        $biscuit = $factory->create('with_rules');

        self::assertInstanceOf(Biscuit::class, $biscuit);
        self::assertStringContainsString('admin(true)', $biscuit->blockSource(0));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itPassesParamsToPrimitives(): void
    {
        $factory = $this->createFactory([
            'user_token' => [
                'facts' => ['user({user_id})'],
            ],
        ]);

        $biscuit = $factory->create('user_token', ['user_id' => 'john_doe']);

        self::assertInstanceOf(Biscuit::class, $biscuit);
        self::assertStringContainsString('user("john_doe")', $biscuit->blockSource(0));
    }

    #[Test]
    public function hasTemplateReturnsTrueForExistingTemplate(): void
    {
        $factory = $this->createFactoryWithMockedManager([
            'existing' => [
                'facts' => ['test("value")'],
            ],
        ]);

        self::assertTrue($factory->hasTemplate('existing'));
    }

    #[Test]
    public function hasTemplateReturnsFalseForUnknownTemplate(): void
    {
        $factory = $this->createFactoryWithMockedManager([]);

        self::assertFalse($factory->hasTemplate('unknown'));
    }

    #[Test]
    public function getTemplateNamesReturnsAllTemplateNames(): void
    {
        $factory = $this->createFactoryWithMockedManager([
            'template_a' => ['facts' => ['a("1")']],
            'template_b' => ['facts' => ['b("2")']],
            'template_c' => ['facts' => ['c("3")']],
        ]);

        $names = $factory->getTemplateNames();

        self::assertCount(3, $names);
        self::assertContains('template_a', $names);
        self::assertContains('template_b', $names);
        self::assertContains('template_c', $names);
    }

    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    private function createFactory(array $templates): BiscuitTokenFactory
    {
        $privateKey = PrivateKey::generate();

        $keyManager = new KeyManager(
            $privateKey->getPublicKey()->toHex(),
            $privateKey->toHex(),
            null,
            null,
            'ed25519',
        );

        $tokenManager = new BiscuitTokenManager($keyManager);

        return new BiscuitTokenFactory($tokenManager, $templates);
    }

    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    private function createFactoryWithMockedManager(array $templates): BiscuitTokenFactory
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);

        return new BiscuitTokenFactory($tokenManager, $templates);
    }
}
