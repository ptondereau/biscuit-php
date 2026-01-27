<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Command;

use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Command\InspectTokenCommand;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Tests\ConsoleApplicationTrait;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(InspectTokenCommand::class)]
final class InspectTokenCommandTest extends TestCase
{
    use ConsoleApplicationTrait;

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itInspectsTokenWithoutVerification(): void
    {
        [$commandTester, $token] = $this->createCommandTesterWithToken();

        $commandTester->execute(['token' => $token]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Token shown without signature verification', $output);
        self::assertStringContainsString('Token Information', $output);
        self::assertStringContainsString('Block 0 (Authority)', $output);
        self::assertStringContainsString('user("test_user")', $output);
        self::assertStringContainsString('Revocation IDs', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itInspectsTokenWithVerification(): void
    {
        [$commandTester, $token] = $this->createCommandTesterWithToken();

        $commandTester->execute([
            'token' => $token,
            '--verify' => true,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Token signature is valid', $output);
        self::assertStringContainsString('Token Information', $output);
        self::assertStringContainsString('Block 0 (Authority)', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itShowsMultipleBlocks(): void
    {
        [$commandTester, $token] = $this->createCommandTesterWithAttenuatedToken();

        $commandTester->execute(['token' => $token]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Block 0 (Authority)', $output);
        self::assertStringContainsString('Block 1', $output);
        self::assertStringContainsString('user("test_user")', $output);
        self::assertStringContainsString('operation("read")', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itFailsForInvalidTokenUnverified(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $commandTester = $this->createCommandTesterWithManager($tokenManager);

        $commandTester->execute(['token' => 'invalid-token']);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Failed to parse token', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itFailsForInvalidTokenVerified(): void
    {
        $tokenManager = $this->createMock(BiscuitTokenManagerInterface::class);
        $tokenManager->method('parse')
            ->willThrowException(new RuntimeException('Invalid signature'));

        $commandTester = $this->createCommandTesterWithManager($tokenManager);

        $commandTester->execute([
            'token' => 'some-token',
            '--verify' => true,
        ]);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Failed to parse/verify token', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itShowsRootKeyIdForUnverifiedToken(): void
    {
        [$commandTester, $token] = $this->createCommandTesterWithToken();

        $commandTester->execute(['token' => $token]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Root key ID', $output);
    }

    /**
     * @return array{CommandTester, string}
     */
    private function createCommandTesterWithToken(): array
    {
        $tokenManager = $this->createTokenManager();
        $builder = $tokenManager->createBuilder('user("test_user")');
        $biscuit = $tokenManager->build($builder);
        $token = $tokenManager->serialize($biscuit);

        $command = new InspectTokenCommand($tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return [new CommandTester($command), $token];
    }

    /**
     * @return array{CommandTester, string}
     */
    private function createCommandTesterWithAttenuatedToken(): array
    {
        $tokenManager = $this->createTokenManager();
        $builder = $tokenManager->createBuilder('user("test_user")');
        $biscuit = $tokenManager->build($builder);

        $blockBuilder = $tokenManager->createBlockBuilder('check if operation("read")');
        $attenuated = $tokenManager->attenuate($biscuit, $blockBuilder);
        $token = $tokenManager->serialize($attenuated);

        $command = new InspectTokenCommand($tokenManager);

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return [new CommandTester($command), $token];
    }

    private function createCommandTesterWithManager(BiscuitTokenManagerInterface $tokenManager): CommandTester
    {
        $command = new InspectTokenCommand($tokenManager);

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
