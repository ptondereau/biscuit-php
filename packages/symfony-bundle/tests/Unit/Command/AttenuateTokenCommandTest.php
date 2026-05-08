<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Command;

use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Command\AttenuateTokenCommand;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Tests\ConsoleApplicationTrait;
use Biscuit\BiscuitBundle\Token\BiscuitBlockFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(AttenuateTokenCommand::class)]
final class AttenuateTokenCommandTest extends TestCase
{
    use ConsoleApplicationTrait;

    #[Test]
    public function itAttenuatesTokenWithRegisteredTemplate(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $parentBase64 = $this->buildParentToken($tokenManager);

        $tester = $this->createCommandTester($tokenManager, [
            'read_only' => ['checks' => ['check if operation("read")']],
        ]);

        $tester->execute([
            'token' => $parentBase64,
            '--template' => 'read_only',
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        $output = $tester->getDisplay();
        self::assertStringContainsString('read_only', $output);
        self::assertStringContainsString('operation("read")', $output);
    }

    #[Test]
    public function itAttenuatesTokenWithInlineCode(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $parentBase64 = $this->buildParentToken($tokenManager);

        $tester = $this->createCommandTester($tokenManager, []);

        $tester->execute([
            'token' => $parentBase64,
            '--code' => 'check if operation("read")',
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('operation("read")', $tester->getDisplay());
    }

    #[Test]
    public function itListsRegisteredBlockTemplates(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $tester = $this->createCommandTester($tokenManager, [
            'read_only' => ['checks' => ['check if operation("read")']],
            'expires' => ['checks' => ['check if now($t), $t <= {exp}']],
        ]);

        $tester->execute(['token' => 'unused', '--list' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $output = $tester->getDisplay();
        self::assertStringContainsString('read_only', $output);
        self::assertStringContainsString('expires', $output);
    }

    #[Test]
    public function itFailsWhenBothTemplateAndCodeProvided(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $tester = $this->createCommandTester($tokenManager, [
            'read_only' => ['checks' => ['check if operation("read")']],
        ]);

        $tester->execute([
            'token' => 'unused',
            '--template' => 'read_only',
            '--code' => 'check if operation("read")',
        ]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('mutually exclusive', $tester->getDisplay());
    }

    #[Test]
    public function itAttenuatesUnverifiedTokenAcrossKeys(): void
    {
        $issuingManager = $this->createRealTokenManager();
        $parentBase64 = $this->buildParentToken($issuingManager);

        $foreignManager = $this->createRealTokenManager();
        $tester = $this->createCommandTester($foreignManager, [
            'read_only' => ['checks' => ['check if operation("read")']],
        ]);

        $tester->execute([
            'token' => $parentBase64,
            '--template' => 'read_only',
            '--unverified' => true,
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('operation("read")', $tester->getDisplay());
    }

    #[Test]
    public function itFailsWhenNeitherTemplateNorCodeProvided(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $tester = $this->createCommandTester($tokenManager, []);

        $tester->execute(['token' => 'unused']);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('--template or --code', $tester->getDisplay());
    }

    #[Test]
    public function itFailsGracefullyOnMalformedTokenInput(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $tester = $this->createCommandTester($tokenManager, [
            'read_only' => ['checks' => ['check if operation("read")']],
        ]);

        $tester->execute([
            'token' => 'this is not valid base64',
            '--template' => 'read_only',
        ]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Failed to attenuate token', $tester->getDisplay());
    }

    #[Test]
    public function itShowsHelpWhenNoBlockTemplatesConfigured(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $tester = $this->createCommandTester($tokenManager, []);

        $tester->execute(['token' => 'unused', '--list' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $output = $tester->getDisplay();
        self::assertStringContainsString('No block templates configured', $output);
        self::assertStringContainsString('block_templates', $output);
    }

    #[Test]
    public function itCoercesParameterTypesFromCommandLine(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $parentBase64 = $this->buildParentToken($tokenManager);

        $tester = $this->createCommandTester($tokenManager, [
            'mixed_params' => [
                'checks' => ['check if user($u), $u == {who}, active({flag}), retries({n})'],
            ],
        ]);

        $tester->execute([
            'token' => $parentBase64,
            '--template' => 'mixed_params',
            '--param' => ['who=alice', 'flag=true', 'n=3', 'malformed_no_equals'],
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $output = $tester->getDisplay();
        self::assertStringContainsString('"alice"', $output);
        self::assertStringContainsString('true', $output);
        self::assertStringContainsString('3', $output);
    }

    #[Test]
    public function itCoercesFalseAndNullParameterValues(): void
    {
        $tokenManager = $this->createRealTokenManager();
        $parentBase64 = $this->buildParentToken($tokenManager);

        $tester = $this->createCommandTester($tokenManager, [
            'falsy' => [
                'checks' => ['check if active({flag})'],
            ],
        ]);

        $tester->execute([
            'token' => $parentBase64,
            '--template' => 'falsy',
            '--param' => ['flag=false', 'unused_null=null'],
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('false', $tester->getDisplay());
    }

    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    private function createCommandTester(
        BiscuitTokenManagerInterface $tokenManager,
        array $templates,
    ): CommandTester {
        $factory = new BiscuitBlockFactory($tokenManager, new Applier(), $templates);
        $command = new AttenuateTokenCommand($factory, $tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }

    private function createRealTokenManager(): BiscuitTokenManager
    {
        $keyPair = new KeyPair();

        $keyManager = new KeyManager(
            $keyPair->getPublicKey()->toHex(),
            $keyPair->getPrivateKey()->toHex(),
            null,
            null,
            'ed25519',
        );

        return new BiscuitTokenManager($keyManager);
    }

    private function buildParentToken(BiscuitTokenManagerInterface $tokenManager): string
    {
        $builder = $tokenManager->createBuilder('user("alice")');

        return $tokenManager->serialize($tokenManager->build($builder));
    }
}
