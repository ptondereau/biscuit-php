<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineRevocationStore::class)]
final class DoctrineRevocationSchemaTest extends TestCase
{
    #[Test]
    public function itDeclaresTheTableWithAPrimaryKeyOnTheIdentifier(): void
    {
        $schema = $this->store()->addTableToSchema(new Schema());
        $table = $schema->getTable(DoctrineRevocationStore::DEFAULT_TABLE);

        self::assertSame(['revocation_id'], $table->getPrimaryKey()?->getColumns());
    }

    #[Test]
    public function itIndexesTheExpirationSoPurgeIsNotAFullScan(): void
    {
        $schema = $this->store()->addTableToSchema(new Schema());
        $table = $schema->getTable(DoctrineRevocationStore::DEFAULT_TABLE);

        $indexed = [];

        foreach ($table->getIndexes() as $index) {
            if (!$index->isPrimary()) {
                $indexed[] = $index->getColumns();
            }
        }

        self::assertContains(['expires_at'], $indexed);
    }

    #[Test]
    public function itLeavesTheIdentifierNotNullAndEverythingElseNullable(): void
    {
        $schema = $this->store()->addTableToSchema(new Schema());
        $table = $schema->getTable(DoctrineRevocationStore::DEFAULT_TABLE);

        self::assertTrue($table->getColumn('revocation_id')->getNotnull());

        foreach (['expires_at', 'revoked_at', 'subject', 'reason', 'metadata'] as $column) {
            self::assertFalse($table->getColumn($column)->getNotnull(), $column . ' must be nullable');
        }
    }

    #[Test]
    public function itPinsAByteExactCollationOnMysqlSoACaseInsensitiveDefaultCannotWronglyRevoke(): void
    {
        $schema = $this->store(new MySQL80Platform())->addTableToSchema(new Schema());
        $column = $schema->getTable(DoctrineRevocationStore::DEFAULT_TABLE)->getColumn('revocation_id');

        self::assertSame('utf8mb4_bin', $column->getPlatformOption('collation'));
    }

    #[Test]
    public function itEmitsNoCollationOnPostgresWhichWouldRejectIt(): void
    {
        $schema = $this->store(new PostgreSQLPlatform())->addTableToSchema(new Schema());
        $column = $schema->getTable(DoctrineRevocationStore::DEFAULT_TABLE)->getColumn('revocation_id');

        self::assertFalse($column->hasPlatformOption('collation'));
    }

    #[Test]
    public function itAddsNothingWhenTheSchemaAlreadyHoldsTheTable(): void
    {
        $store = $this->store();
        $schema = new Schema();
        $schema->createTable(DoctrineRevocationStore::DEFAULT_TABLE)
            ->addColumn('mine', 'string', ['length' => 8]);

        $result = $store->configureSchema($schema);

        self::assertTrue($result->getTable(DoctrineRevocationStore::DEFAULT_TABLE)->hasColumn('mine'));
        self::assertFalse($result->getTable(DoctrineRevocationStore::DEFAULT_TABLE)->hasColumn('revocation_id'));
    }

    #[Test]
    public function itAddsTheTableToAnEmptySchema(): void
    {
        $result = $this->store()->configureSchema(new Schema());

        self::assertTrue($result->hasTable(DoctrineRevocationStore::DEFAULT_TABLE));
    }

    #[Test]
    public function itRecognisesItsOwnConnection(): void
    {
        $connection = $this->connection();

        self::assertTrue((new DoctrineRevocationStore($connection))->ownsConnection($connection));
    }

    #[Test]
    public function itDisownsAnotherConnection(): void
    {
        $store = new DoctrineRevocationStore($this->connection());

        self::assertFalse(
            $store->ownsConnection($this->connection()),
            'A multi-connection application must not get the table added to every schema.',
        );
    }

    #[Test]
    public function itUsesTheMysqlUpsertOnMysql(): void
    {
        self::assertStringContainsString(
            'ON DUPLICATE KEY UPDATE',
            $this->capturedWriteSql(new MySQL80Platform()),
        );
    }

    #[Test]
    public function itUsesTheStandardUpsertOnPostgres(): void
    {
        $sql = $this->capturedWriteSql(new PostgreSQLPlatform());

        self::assertStringContainsString('ON CONFLICT (revocation_id) DO UPDATE SET', $sql);
        self::assertStringContainsString('EXCLUDED.expires_at', $sql);
    }

    #[Test]
    public function itUsesTheStandardUpsertOnSqlite(): void
    {
        self::assertStringContainsString(
            'ON CONFLICT (revocation_id) DO UPDATE SET',
            $this->capturedWriteSql($this->connection()->getDatabasePlatform()),
        );
    }

    #[Test]
    public function itNeverOverwritesTheIdentifierItMatchedOn(): void
    {
        self::assertStringNotContainsString(
            'revocation_id = EXCLUDED.revocation_id',
            $this->capturedWriteSql(new PostgreSQLPlatform()),
        );
    }

    #[Test]
    public function itRefusesAPlatformItHasNoUpsertFor(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new OraclePlatform());

        $store = new DoctrineRevocationStore($connection);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/does not support/');

        $store->revoke(new RevocationEntry('abc'));
    }

    private function capturedWriteSql(AbstractPlatform $platform): string
    {
        $captured = '';

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection->method('executeStatement')->willReturnCallback(
            static function (string $sql) use (&$captured): int {
                $captured = $sql;

                return 1;
            },
        );

        (new DoctrineRevocationStore($connection))->revoke(new RevocationEntry('abc'));

        return $captured;
    }

    private function store(?AbstractPlatform $platform = null): DoctrineRevocationStore
    {
        if (null === $platform) {
            return new DoctrineRevocationStore($this->connection());
        }

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        return new DoctrineRevocationStore($connection);
    }

    private function connection(): Connection
    {
        return DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
    }
}
