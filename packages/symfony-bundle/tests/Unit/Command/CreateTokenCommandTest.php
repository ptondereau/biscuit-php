<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Command;

use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Command\CreateTokenCommand;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Tests\ConsoleApplicationTrait;
use Biscuit\BiscuitBundle\Token\BiscuitTokenFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CreateTokenCommand::class)]
final class CreateTokenCommandTest extends TestCase
{
    use ConsoleApplicationTrait;

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesTokenFromTemplate(): void
    {
        $commandTester = $this->createCommandTester([
            'user_token' => [
                'facts' => ['user("test_user")'],
            ],
        ]);

        $commandTester->execute(['template' => 'user_token']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Token Created', $output);
        self::assertStringContainsString('Template', $output);
        self::assertStringContainsString('user_token', $output);
        self::assertStringContainsString('Base64 Token', $output);
        self::assertStringContainsString('Token Contents', $output);
        self::assertStringContainsString('user("test_user")', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesTokenWithParameters(): void
    {
        $commandTester = $this->createCommandTester([
            'user_token' => [
                'facts' => ['user({user_id})'],
            ],
        ]);

        $commandTester->execute([
            'template' => 'user_token',
            '--param' => ['user_id=john_doe'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('user("john_doe")', $output);
        self::assertStringContainsString("user_id='john_doe'", $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itCreatesTokenWithMultipleParameters(): void
    {
        $commandTester = $this->createCommandTester([
            'complex_token' => [
                'facts' => ['user_role({user_id}, {role})'],
            ],
        ]);

        $commandTester->execute([
            'template' => 'complex_token',
            '-p' => ['user_id=alice', 'role=admin'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('user_role("alice", "admin")', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itParsesIntegerParameters(): void
    {
        $commandTester = $this->createCommandTester([
            'user_token' => [
                'facts' => ['user_id({id})'],
            ],
        ]);

        $commandTester->execute([
            'template' => 'user_token',
            '--param' => ['id=123'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('id=123', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itParsesBooleanParameters(): void
    {
        $commandTester = $this->createCommandTester([
            'user_token' => [
                'facts' => ['active({is_active})'],
            ],
        ]);

        $commandTester->execute([
            'template' => 'user_token',
            '--param' => ['is_active=true'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('is_active=true', $output);
    }

    #[Test]
    public function itFailsForUnknownTemplate(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([]);

        $commandTester->execute(['template' => 'unknown']);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Unknown template: unknown', $output);
    }

    #[Test]
    public function itListsAvailableTemplatesOnFailure(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([
            'template_a' => ['facts' => ['a("1")']],
            'template_b' => ['facts' => ['b("2")']],
        ]);

        $commandTester->execute(['template' => 'unknown']);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Available templates:', $output);
        self::assertStringContainsString('template_a', $output);
        self::assertStringContainsString('template_b', $output);
    }

    #[Test]
    public function itListsTemplatesWithOption(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([
            'user_token' => ['facts' => ['user("test")']],
            'admin_token' => ['facts' => ['admin(true)']],
        ]);

        $commandTester->execute([
            'template' => 'ignored',
            '--list' => true,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Available Token Templates', $output);
        self::assertStringContainsString('user_token', $output);
        self::assertStringContainsString('admin_token', $output);
    }

    #[Test]
    public function itShowsHelpForEmptyTemplates(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([]);

        $commandTester->execute([
            'template' => 'ignored',
            '--list' => true,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('No token templates configured', $output);
        self::assertStringContainsString('biscuit:', $output);
        self::assertStringContainsString('token_templates:', $output);
    }

    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    private function createCommandTester(array $templates): CommandTester
    {
        $tokenManager = $this->createTokenManager();
        $factory = new BiscuitTokenFactory($tokenManager, $templates);

        $command = new CreateTokenCommand($factory, $tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }

    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    private function createCommandTesterWithMocks(array $templates): CommandTester
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $factory = new BiscuitTokenFactory($tokenManager, $templates);

        $command = new CreateTokenCommand($factory, $tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }

    private function createTokenManager(): BiscuitTokenManager
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
}
