<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Doctrine;

use Biscuit\BiscuitBundle\Revocation\Doctrine\RevocationSchemaListener;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RevocationSchemaListener::class)]
final class RevocationSchemaListenerTest extends TestCase
{
    #[Test]
    public function itAddsTheRevocationTableToAGeneratedSchema(): void
    {
        $connection = $this->connection();
        $event = new GenerateSchemaEventArgs($this->entityManager($connection), new Schema());

        (new RevocationSchemaListener(new DoctrineRevocationStore($connection)))->postGenerateSchema($event);

        self::assertTrue(
            $event->getSchema()->hasTable(DoctrineRevocationStore::DEFAULT_TABLE),
            'Without this, doctrine:migrations:diff emits DROP TABLE and disables revocation.',
        );
    }

    #[Test]
    public function itKeepsTheApplicationTablesTheSchemaAlreadyHeld(): void
    {
        $connection = $this->connection();
        $schema = new Schema();
        $schema->createTable('app_user')->addColumn('id', 'integer');

        $event = new GenerateSchemaEventArgs($this->entityManager($connection), $schema);

        (new RevocationSchemaListener(new DoctrineRevocationStore($connection)))->postGenerateSchema($event);

        self::assertTrue($event->getSchema()->hasTable('app_user'));
        self::assertTrue($event->getSchema()->hasTable(DoctrineRevocationStore::DEFAULT_TABLE));
    }

    #[Test]
    public function itHonoursAConfiguredTableName(): void
    {
        $connection = $this->connection();
        $event = new GenerateSchemaEventArgs($this->entityManager($connection), new Schema());

        (new RevocationSchemaListener(new DoctrineRevocationStore($connection, 'app_revocations')))
            ->postGenerateSchema($event);

        self::assertTrue($event->getSchema()->hasTable('app_revocations'));
    }

    #[Test]
    public function itRespectsASchemaFilterThatExcludesTheTable(): void
    {
        $connection = $this->connection();
        $connection->getConfiguration()->setSchemaAssetsFilter(
            static fn (string $name): bool => !str_starts_with($name, 'biscuit_'),
        );

        $event = new GenerateSchemaEventArgs($this->entityManager($connection), new Schema());

        (new RevocationSchemaListener(new DoctrineRevocationStore($connection)))->postGenerateSchema($event);

        self::assertFalse(
            $event->getSchema()->hasTable(DoctrineRevocationStore::DEFAULT_TABLE),
            'An application that filters our table out must keep it out of its migrations.',
        );
    }

    #[Test]
    public function itSkipsAnEntityManagerOnAnotherConnection(): void
    {
        $event = new GenerateSchemaEventArgs($this->entityManager($this->connection()), new Schema());

        (new RevocationSchemaListener(new DoctrineRevocationStore($this->connection())))
            ->postGenerateSchema($event);

        self::assertFalse($event->getSchema()->hasTable(DoctrineRevocationStore::DEFAULT_TABLE));
    }

    #[Test]
    public function itAddsTheTableOnlyOnceWhenTheEventFiresTwice(): void
    {
        $connection = $this->connection();
        $listener = new RevocationSchemaListener(new DoctrineRevocationStore($connection));
        $schema = new Schema();

        $listener->postGenerateSchema(new GenerateSchemaEventArgs($this->entityManager($connection), $schema));
        $listener->postGenerateSchema(new GenerateSchemaEventArgs($this->entityManager($connection), $schema));

        self::assertCount(1, $schema->getTables());
    }

    private function connection(): Connection
    {
        return DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
    }

    private function entityManager(Connection $connection): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }
}
