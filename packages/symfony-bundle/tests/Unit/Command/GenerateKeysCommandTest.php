<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Command;

use Biscuit\BiscuitBundle\Command\GenerateKeysCommand;
use Biscuit\BiscuitBundle\Tests\ConsoleApplicationTrait;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GenerateKeysCommand::class)]
final class GenerateKeysCommandTest extends TestCase
{
    use ConsoleApplicationTrait;

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itGeneratesEd25519KeyPairByDefault(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Generated Key Pair', $output);
        self::assertStringContainsString('Algorithm', $output);
        self::assertStringContainsString('ed25519', $output);
        self::assertStringContainsString('Public Key', $output);
        self::assertStringContainsString('Private Key', $output);
        self::assertStringContainsString('BISCUIT_PUBLIC_KEY', $output);
        self::assertStringContainsString('BISCUIT_PRIVATE_KEY', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itGeneratesEd25519KeyPairWithExplicitOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(['--algorithm' => 'ed25519']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('ed25519', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itGeneratesSecp256r1KeyPair(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(['--algorithm' => 'secp256r1']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('secp256r1', $output);
        self::assertStringContainsString('Generated Key Pair', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itGeneratesShortAlgorithmOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(['-a' => 'ed25519']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itThrowsForUnknownAlgorithm(): void
    {
        $commandTester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown algorithm: rsa');

        $commandTester->execute(['--algorithm' => 'rsa']);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itShowsSecurityWarning(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Store the private key securely', $output);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit_php')]
    public function itShowsConfigurationExample(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('config/packages/biscuit.yaml', $output);
        self::assertStringContainsString('biscuit:', $output);
        self::assertStringContainsString('keys:', $output);
    }

    private function createCommandTester(): CommandTester
    {
        $command = new GenerateKeysCommand();

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        return new CommandTester($command);
    }
}
