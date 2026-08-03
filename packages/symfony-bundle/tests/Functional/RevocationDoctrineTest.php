<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Revocation\RevocationCheckerInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class RevocationDoctrineTest extends WebTestCase
{
    use BiscuitTestTrait;
    use ResetsTestKernel;

    private ?KernelInterface $bootedKernel = null;

    #[Test]
    public function itRegistersTheSetupCommandWhenTheStoreIsEnabled(): void
    {
        $this->bootWithDoctrineStore();

        self::assertContains(
            'biscuit:revocation:doctrine:setup',
            array_keys((new Application($this->kernel()))->all()),
        );
    }

    #[Test]
    public function itCreatesTheTableFromTheSetupCommand(): void
    {
        $this->bootWithDoctrineStore();

        $tester = $this->tester('biscuit:revocation:doctrine:setup');

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Created table', $tester->getDisplay());
        self::assertTrue($this->store()->tableExists());
    }

    #[Test]
    public function itIsSafeToRunTheSetupCommandTwice(): void
    {
        $this->bootWithDoctrineStore();
        $this->tester('biscuit:revocation:doctrine:setup')->execute([]);

        $tester = $this->tester('biscuit:revocation:doctrine:setup');

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('already exists', $tester->getDisplay());
    }

    #[Test]
    public function itPrintsTheStatementsWithoutRunningThemOnDumpSql(): void
    {
        $this->bootWithDoctrineStore();

        $tester = $this->tester('biscuit:revocation:doctrine:setup');
        $tester->execute(['--dump-sql' => true]);

        self::assertStringContainsString('CREATE TABLE biscuit_revoked_tokens', $tester->getDisplay());
        self::assertFalse(
            $this->store()->tableExists(),
            '--dump-sql must not touch the database.',
        );
    }

    #[Test]
    public function itRejectsATokenRevokedInTheDatabase(): void
    {
        $token = $this->createTestToken('user("alice")');

        $client = $this->clientWithDoctrineStore();
        $this->createTable();
        $this->revoke($token);

        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itAcceptsATokenThatIsNotInTheDatabase(): void
    {
        $client = $this->clientWithDoctrineStore();
        $this->createTable();

        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createTestTokenBase64('user("alice")'),
        ]);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function itKeepsTheAncestorWorkingWhenTheDeepestIdentifierIsRevoked(): void
    {
        $parent = $this->createTestToken('user("alice")');
        $child = $parent->append(new BlockBuilder('check if resource("report")'));

        $this->bootWithDoctrineStore();
        $this->createTable();
        $this->revoke($child);

        $checker = $this->checker();

        self::assertTrue($checker->check($child)->isRevoked());
        self::assertFalse($checker->check($parent)->isRevoked());
    }

    #[Test]
    public function itListsWhatTheDatabaseHolds(): void
    {
        $token = $this->createTestToken('user("alice")');

        $this->bootWithDoctrineStore();
        $this->createTable();
        $this->revoke($token, 'logout');

        $tester = $this->tester('biscuit:revocation:list');
        $tester->execute(['--format' => 'txt']);

        self::assertStringContainsString(
            $token->revocationIds()[$token->blockCount() - 1],
            $tester->getDisplay(),
        );
    }

    #[Test]
    public function itPurgesExpiredEntriesFromTheDatabase(): void
    {
        $this->bootWithDoctrineStore();
        $this->createTable();

        $writer = $this->writer();
        $writer->revoke(new RevocationEntry(
            'deadbeef',
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
        ));

        $tester = $this->tester('biscuit:revocation:purge');
        $exit = $tester->execute(['--before' => '2026-08-15T00:00:00Z', '--force' => true]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString('Purged 1 entry', $tester->getDisplay());
        self::assertFalse($this->checker()->checkIds(['deadbeef'])->isRevoked());
    }

    #[Test]
    public function itReportsAnUnreadableStoreInsteadOfCrashingTheListCommand(): void
    {
        $this->bootWithDoctrineStore();

        $tester = $this->tester('biscuit:revocation:list');
        $exit = $tester->execute([]);

        self::assertSame(
            Command::FAILURE,
            $exit,
            'The table was never created, so the store cannot answer.',
        );
        self::assertStringContainsString('could not be read', $tester->getDisplay());
        self::assertStringContainsString('biscuit:revocation:doctrine:setup', $tester->getDisplay());
    }

    private function revoke(Biscuit $token, ?string $reason = null): void
    {
        $factory = self::getContainer()->get(RevocationEntryFactory::class);

        self::assertInstanceOf(RevocationEntryFactory::class, $factory);

        $this->writer()->revoke($factory->fromToken($token, $reason));
    }

    private function createTable(): void
    {
        $store = $this->store();

        foreach ($store->schemaSql() as $sql) {
            $this->connection()->executeStatement($sql);
        }
    }

    private function connection(): Connection
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        self::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }

    private function store(): DoctrineRevocationStore
    {
        $store = self::getContainer()->get('biscuit.revocation.store.doctrine');

        self::assertInstanceOf(DoctrineRevocationStore::class, $store);

        return $store;
    }

    private function writer(): RevocationWriterInterface
    {
        $writer = self::getContainer()->get(RevocationWriterInterface::class);

        self::assertInstanceOf(RevocationWriterInterface::class, $writer);

        return $writer;
    }

    private function checker(): RevocationCheckerInterface
    {
        $checker = self::getContainer()->get(RevocationCheckerInterface::class);

        self::assertInstanceOf(RevocationCheckerInterface::class, $checker);

        return $checker;
    }

    private function tester(string $command): CommandTester
    {
        return new CommandTester((new Application($this->kernel()))->find($command));
    }

    private function kernel(): KernelInterface
    {
        return $this->bootedKernel ??= self::bootKernel();
    }

    private function clientWithDoctrineStore(): KernelBrowser
    {
        $this->configureDoctrineStore(withFirewall: true);

        $client = self::createClient();
        $this->bootedKernel = $client->getKernel();

        return $client;
    }

    private function bootWithDoctrineStore(): void
    {
        $this->configureDoctrineStore();

        $this->bootedKernel = self::bootKernel();
    }

    private function configureDoctrineStore(bool $withFirewall = false): void
    {
        TestKernel::configure(
            biscuitConfig: [
                'keys' => ['public_key' => self::getTestPublicKey()->toHex()],
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['doctrine' => ['enabled' => true]],
                ],
            ],
            withFirewall: $withFirewall,
            withDoctrineConnection: true,
        );
    }
}
