<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Command;

use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Command\TestPolicyCommand;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Tests\ConsoleApplicationTrait;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(TestPolicyCommand::class)]
final class TestPolicyCommandTest extends TestCase
{
    use ConsoleApplicationTrait;

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsPassingPolicyWithFacts(): void
    {
        $commandTester = $this->createCommandTester([
            'is_user' => 'allow if user($u)',
        ]);

        $commandTester->execute([
            'policy' => 'is_user',
            '--fact' => ['user("alice")'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Testing Policy', $output);
        self::assertStringContainsString('is_user', $output);
        self::assertStringContainsString('Authorization PASSED', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsFailingPolicy(): void
    {
        $commandTester = $this->createCommandTester([
            'is_admin' => 'deny if true',
        ]);

        $commandTester->execute([
            'policy' => 'is_admin',
        ]);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Authorization FAILED', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsInlinePolicy(): void
    {
        $commandTester = $this->createCommandTester([]);

        $commandTester->execute([
            'policy' => 'allow if user($u)',
            '--fact' => ['user("bob")'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Authorization PASSED', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsPolicyWithToken(): void
    {
        $tokenManager = $this->createTokenManager();
        $builder = $tokenManager->createBuilder('user("token_user")');
        $biscuit = $tokenManager->build($builder);
        $token = $tokenManager->serialize($biscuit);

        $commandTester = $this->createCommandTesterWithManager(
            ['has_user' => 'allow if user($u)'],
            $tokenManager,
        );

        $commandTester->execute([
            'policy' => 'has_user',
            '--token' => $token,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('With token', $output);
        self::assertStringContainsString('Yes', $output);
        self::assertStringContainsString('Token Contents', $output);
        self::assertStringContainsString('user("token_user")', $output);
        self::assertStringContainsString('Authorization PASSED', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsPolicyWithParameters(): void
    {
        $commandTester = $this->createCommandTester([
            'is_specific_user' => 'allow if user({expected_user})',
        ]);

        $commandTester->execute([
            'policy' => 'is_specific_user',
            '--fact' => ['user("alice")'],
            '--param' => ['expected_user=alice'],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Authorization PASSED', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itTestsMultipleFacts(): void
    {
        $commandTester = $this->createCommandTester([
            'can_read' => 'allow if user($u), resource($r), operation("read")',
        ]);

        $commandTester->execute([
            'policy' => 'can_read',
            '--fact' => [
                'user("alice")',
                'resource("file1")',
                'operation("read")',
            ],
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Authorization PASSED', $output);
    }

    #[Test]
    public function itListsPolicies(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([
            'is_admin' => 'allow if role("admin")',
            'can_read' => 'allow if right($r, "read")',
        ]);

        $commandTester->execute([
            'policy' => 'ignored',
            '--list' => true,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Available Policies', $output);
        self::assertStringContainsString('is_admin', $output);
        self::assertStringContainsString('can_read', $output);
        self::assertStringContainsString('role("admin")', $output);
    }

    #[Test]
    public function itShowsHelpForEmptyPolicies(): void
    {
        $commandTester = $this->createCommandTesterWithMocks([]);

        $commandTester->execute([
            'policy' => 'ignored',
            '--list' => true,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('No policies configured', $output);
        self::assertStringContainsString('biscuit:', $output);
        self::assertStringContainsString('policies:', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itShowsAuthorizerState(): void
    {
        $commandTester = $this->createCommandTester([
            'test_policy' => 'allow if user($u)',
        ]);

        $commandTester->execute([
            'policy' => 'test_policy',
            '--fact' => ['user("alice")'],
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Authorizer State', $output);
    }

    /**
     * @param array<string, string> $policies
     */
    private function createCommandTester(array $policies): CommandTester
    {
        $tokenManager = $this->createTokenManager();
        $registry = new PolicyRegistry($policies);

        $command = new TestPolicyCommand($registry, $tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }

    /**
     * @param array<string, string> $policies
     */
    private function createCommandTesterWithManager(array $policies, BiscuitTokenManagerInterface $tokenManager): CommandTester
    {
        $registry = new PolicyRegistry($policies);

        $command = new TestPolicyCommand($registry, $tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }

    /**
     * @param array<string, string> $policies
     */
    private function createCommandTesterWithMocks(array $policies): CommandTester
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $registry = new PolicyRegistry($policies);

        $command = new TestPolicyCommand($registry, $tokenManager);

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
