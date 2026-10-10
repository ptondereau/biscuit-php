<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RevocationCommandTest extends KernelTestCase
{
    use BiscuitTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itRegistersEveryRevocationCommandEvenWhenRevocationIsDisabled(): void
    {
        $names = array_keys((new Application(self::bootKernel()))->all());

        foreach ([
            'biscuit:revocation:revoke',
            'biscuit:revocation:check',
            'biscuit:revocation:list',
            'biscuit:revocation:purge',
        ] as $name) {
            self::assertContains($name, $names);
        }
    }

    #[Test]
    public function itReportsAMissingSetupInsteadOfCrashingWhenRevocationIsDisabled(): void
    {
        $tester = $this->tester('biscuit:revocation:revoke');
        $exit = $tester->execute(['--id' => ['abc']]);

        self::assertSame(Command::FAILURE, $exit);
        self::assertStringContainsString('Revocation is not enabled', $tester->getDisplay());
    }

    #[Test]
    public function itRevokesTheDeepestIdentifierOfAnAttenuatedToken(): void
    {
        $parent = $this->createTestToken('user("alice")');
        $child = $parent->append(new BlockBuilder('check if resource("report")'));
        $deepest = $child->revocationIds()[$child->blockCount() - 1];

        $this->bootWithInMemoryStore();

        $tester = $this->tester('biscuit:revocation:revoke');
        $exit = $tester->execute(['token' => $child->toBase64()]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString($deepest, $tester->getDisplay());

        $checker = self::getContainer()->get(RevocationChecker::class);
        self::assertInstanceOf(RevocationChecker::class, $checker);
        self::assertTrue($checker->check($child)->isRevoked());
        self::assertFalse($checker->check($parent)->isRevoked(), 'The ancestor must keep working.');
    }

    #[Test]
    public function itWritesNothingOnADryRun(): void
    {
        $token = $this->createTestToken('user("alice")');

        $this->bootWithInMemoryStore();

        $tester = $this->tester('biscuit:revocation:revoke');
        $tester->execute(['token' => $token->toBase64(), '--dry-run' => true]);

        self::assertStringContainsString('Nothing was written', $tester->getDisplay());

        $checker = self::getContainer()->get(RevocationChecker::class);
        self::assertInstanceOf(RevocationChecker::class, $checker);
        self::assertFalse($checker->check($token)->isRevoked());
    }

    #[Test]
    public function itRefusesATokenArgumentCombinedWithRawIdentifiers(): void
    {
        $this->bootWithInMemoryStore();

        $tester = $this->tester('biscuit:revocation:revoke');
        $exit = $tester->execute(['token' => 'whatever', '--id' => ['abc']]);

        self::assertSame(Command::FAILURE, $exit);
        self::assertStringContainsString('not both', $tester->getDisplay());
    }

    #[Test]
    public function itExitsZeroForATokenThatIsNotRevoked(): void
    {
        $token = $this->createTestToken('user("alice")');

        $this->bootWithStaticIds(['0000000000000000']);

        $tester = $this->tester('biscuit:revocation:check');

        self::assertSame(Command::SUCCESS, $tester->execute(['token' => $token->toBase64()]));
        self::assertStringContainsString('Not revoked', $tester->getDisplay());
    }

    #[Test]
    public function itExitsOneForARevokedToken(): void
    {
        $token = $this->createTestToken('user("alice")');

        $this->bootWithStaticIds([$token->revocationIds()[0]]);

        $tester = $this->tester('biscuit:revocation:check');

        self::assertSame(Command::FAILURE, $tester->execute(['token' => $token->toBase64()]));
    }

    #[Test]
    public function itExplainsWhichStoreAnswered(): void
    {
        $token = $this->createTestToken('user("alice")');

        $this->bootWithStaticIds([$token->revocationIds()[0]]);

        $tester = $this->tester('biscuit:revocation:check');
        $tester->execute(['token' => $token->toBase64(), '--explain' => true]);

        $display = $tester->getDisplay();
        self::assertStringContainsString('Stores consulted', $display);
        self::assertStringContainsString('static', $display);
        self::assertStringContainsString('matched', $display);
    }

    #[Test]
    public function itListsStaticIdentifiersAsBareTextForPipingIntoAFile(): void
    {
        $this->bootWithStaticIds(['aaa111', 'bbb222']);

        $tester = $this->tester('biscuit:revocation:list');
        $tester->execute(['--format' => 'txt']);

        self::assertSame("aaa111\nbbb222", trim($tester->getDisplay()));
    }

    #[Test]
    public function itWarnsThatEntriesWithoutAnExpirationCanNeverBePurged(): void
    {
        $this->bootWithStaticIds(['aaa111']);

        $tester = $this->tester('biscuit:revocation:list');
        $tester->execute([]);

        self::assertStringContainsString('never be purged', $tester->getDisplay());
    }

    #[Test]
    public function itRejectsAnUnknownListFormat(): void
    {
        $this->bootWithStaticIds(['aaa111']);

        $tester = $this->tester('biscuit:revocation:list');

        self::assertSame(Command::FAILURE, $tester->execute(['--format' => 'yaml']));
    }

    #[Test]
    public function itPurgesNothingWhenForcedWithAnEmptyStore(): void
    {
        $this->bootWithInMemoryStore();

        $tester = $this->tester('biscuit:revocation:purge');
        $exit = $tester->execute(['--force' => true]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString('Purged 0', $tester->getDisplay());
    }

    #[Test]
    public function itRejectsAnInvalidPurgeDate(): void
    {
        $this->bootWithInMemoryStore();

        $tester = $this->tester('biscuit:revocation:purge');

        self::assertSame(Command::FAILURE, $tester->execute(['--before' => 'not-a-date']));
    }

    private function tester(string $command): CommandTester
    {
        if (null === self::$kernel) {
            self::bootKernel();
        }

        $application = new Application(self::bootKernel());

        return new CommandTester($application->find($command));
    }

    private function bootWithInMemoryStore(): void
    {
        TestKernel::configure([
            'keys' => ['public_key' => self::getTestPublicKey()->toHex()],
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['in_memory' => ['enabled' => true]],
            ],
        ]);

        self::bootKernel();
    }

    /**
     * @param list<string> $ids
     */
    private function bootWithStaticIds(array $ids): void
    {
        TestKernel::configure([
            'keys' => ['public_key' => self::getTestPublicKey()->toHex()],
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['static' => ['ids' => $ids]],
            ],
        ]);

        self::bootKernel();
    }
}
