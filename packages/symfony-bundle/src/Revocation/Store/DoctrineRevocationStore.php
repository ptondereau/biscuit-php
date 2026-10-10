<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use JsonException;
use LogicException;
use Throwable;

final class DoctrineRevocationStore implements EnumerableRevocationStoreInterface, RevocationWriterInterface
{
    public const DEFAULT_TABLE = 'biscuit_revoked_tokens';

    private const COLUMN_ID = 'revocation_id';

    private const COLUMN_EXPIRES_AT = 'expires_at';

    private const COLUMN_REVOKED_AT = 'revoked_at';

    private const COLUMN_SUBJECT = 'subject';

    private const COLUMN_REASON = 'reason';

    private const COLUMN_METADATA = 'metadata';

    private const EXPIRES_INDEX_SUFFIX = '_expires_idx';

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private const MAX_ID_LENGTH = 255;

    private const MAX_SUBJECT_LENGTH = 255;

    private const MAX_REASON_LENGTH = 1024;

    private const MAX_METADATA_LENGTH = 8192;

    private const READ_CHUNK_SIZE = 500;

    private const PAGE_SIZE = 1000;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = self::DEFAULT_TABLE,
    ) {
    }

    public function findRevoked(array $revocationIds): ?string
    {
        $wanted = [];

        foreach ($revocationIds as $revocationId) {
            $normalized = $this->normalize($revocationId);

            if (!$this->isStorable($normalized)) {
                continue;
            }

            $wanted[$normalized] ??= true;
        }

        if ([] === $wanted) {
            return null;
        }

        $matched = [];

        foreach (array_chunk(array_keys($wanted), self::READ_CHUNK_SIZE) as $chunk) {
            foreach ($this->selectIds($chunk) as $found) {
                $matched[$this->normalize($found)] = true;
            }
        }

        foreach ($revocationIds as $revocationId) {
            if (isset($matched[$this->normalize($revocationId)])) {
                return $revocationId;
            }
        }

        return null;
    }

    public function revoke(RevocationEntry $entry): void
    {
        $parameters = [
            $this->normalize($entry->revocationId),
            $this->toDatabaseDate($entry->expiresAt),
            $this->toDatabaseDate($entry->revokedAt),
            $this->truncate($entry->subject, self::MAX_SUBJECT_LENGTH),
            $this->truncate($entry->reason, self::MAX_REASON_LENGTH),
            $this->encodeMetadata($entry->metadata),
        ];

        $this->write($this->upsertSql(), $parameters);
    }

    public function unrevoke(string $revocationId): void
    {
        $this->write(
            sprintf('DELETE FROM %s WHERE %s = ?', $this->table, self::COLUMN_ID),
            [$this->normalize($revocationId)],
        );
    }

    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();

        return $this->write(
            sprintf(
                'DELETE FROM %s WHERE %s IS NOT NULL AND %s < ?',
                $this->table,
                self::COLUMN_EXPIRES_AT,
                self::COLUMN_EXPIRES_AT,
            ),
            [$this->toDatabaseDate($now)],
        );
    }

    public function all(): iterable
    {
        $sql = sprintf(
            'SELECT %s, %s, %s, %s, %s, %s FROM %s WHERE %s > ? ORDER BY %s LIMIT %d',
            self::COLUMN_ID,
            self::COLUMN_EXPIRES_AT,
            self::COLUMN_REVOKED_AT,
            self::COLUMN_SUBJECT,
            self::COLUMN_REASON,
            self::COLUMN_METADATA,
            $this->table,
            self::COLUMN_ID,
            self::COLUMN_ID,
            self::PAGE_SIZE,
        );

        $cursor = '';

        while (true) {
            $rows = $this->guard(
                fn (): array => $this->connection->fetchAllAssociative($sql, [$cursor]),
            );

            foreach ($rows as $row) {
                $cursor = \is_string($row[self::COLUMN_ID]) ? $row[self::COLUMN_ID] : '';

                yield $this->toEntry($row);
            }

            if (\count($rows) < self::PAGE_SIZE) {
                return;
            }
        }
    }

    public function configureSchema(Schema $schema): Schema
    {
        if ($schema->hasTable($this->table)) {
            return $schema;
        }

        return $this->addTableToSchema($schema);
    }

    public function ownsConnection(Connection $connection): bool
    {
        return $connection === $this->connection;
    }

    public function addTableToSchema(Schema $schema): Schema
    {
        $table = $schema->createTable($this->table);

        $table->addColumn(self::COLUMN_ID, Types::STRING, [
            'length' => self::MAX_ID_LENGTH,
            'notnull' => true,
            'platformOptions' => $this->identifierPlatformOptions(),
        ]);
        $table->addColumn(self::COLUMN_EXPIRES_AT, Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn(self::COLUMN_REVOKED_AT, Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn(self::COLUMN_SUBJECT, Types::STRING, [
            'length' => self::MAX_SUBJECT_LENGTH,
            'notnull' => false,
        ]);
        $table->addColumn(self::COLUMN_REASON, Types::TEXT, ['notnull' => false]);
        $table->addColumn(self::COLUMN_METADATA, Types::TEXT, ['notnull' => false]);

        $table->setPrimaryKey([self::COLUMN_ID]);
        $table->addIndex([self::COLUMN_EXPIRES_AT], $this->table . self::EXPIRES_INDEX_SUFFIX);

        return $schema;
    }

    /**
     * @return list<string>
     */
    public function schemaSql(): array
    {
        return $this->guard(
            fn (): array => $this->addTableToSchema(new Schema())->toSql($this->connection->getDatabasePlatform()),
        );
    }

    public function tableExists(): bool
    {
        return $this->guard(
            fn (): bool => $this->connection->createSchemaManager()->tablesExist([$this->table]),
        );
    }

    public function table(): string
    {
        return $this->table;
    }

    /**
     * A case-insensitive default collation would match a different identifier and revoke a valid
     * token. PostgreSQL and SQLite already compare byte-exactly, and rendering COLLATE there
     * produces an unusable name.
     *
     * @return array<string, string>
     */
    private function identifierPlatformOptions(): array
    {
        if (!$this->platform() instanceof AbstractMySQLPlatform) {
            return [];
        }

        return ['collation' => 'utf8mb4_bin'];
    }

    /**
     * @param list<string> $revocationIds
     *
     * @return list<mixed>
     */
    private function selectIds(array $revocationIds): array
    {
        return $this->guard(fn (): array => $this->connection->fetchFirstColumn(
            sprintf('SELECT %s FROM %s WHERE %s IN (?)', self::COLUMN_ID, $this->table, self::COLUMN_ID),
            [$revocationIds],
            [ArrayParameterType::STRING],
        ));
    }

    /**
     * @param list<string|null> $parameters
     */
    private function write(string $sql, array $parameters): int
    {
        try {
            return $this->guard(fn (): int => (int) $this->connection->executeStatement($sql, $parameters));
        } catch (RevocationStoreUnavailableException $e) {
            if (!$e->getPrevious() instanceof RetryableException || $this->connection->isTransactionActive()) {
                throw $e;
            }
        }

        return $this->guard(fn (): int => (int) $this->connection->executeStatement($sql, $parameters));
    }

    private function upsertSql(): string
    {
        $columns = [
            self::COLUMN_ID,
            self::COLUMN_EXPIRES_AT,
            self::COLUMN_REVOKED_AT,
            self::COLUMN_SUBJECT,
            self::COLUMN_REASON,
            self::COLUMN_METADATA,
        ];
        $updatable = \array_slice($columns, 1);

        $insert = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', array_fill(0, \count($columns), '?')),
        );

        $platform = $this->platform();

        if ($platform instanceof AbstractMySQLPlatform) {
            return $insert . sprintf(
                ' ON DUPLICATE KEY UPDATE %s',
                implode(', ', array_map(
                    static fn (string $column): string => sprintf('%s = VALUES(%s)', $column, $column),
                    $updatable,
                )),
            );
        }

        if ($platform instanceof OraclePlatform
            || $platform instanceof SQLServerPlatform
            || $platform instanceof DB2Platform) {
            throw new LogicException(sprintf('The Doctrine revocation store does not support "%s". It needs an upsert, which this bundle only implements for MySQL, MariaDB, PostgreSQL and SQLite. Implement RevocationStoreInterface against your platform instead.', $platform::class));
        }

        return $insert . sprintf(
            ' ON CONFLICT (%s) DO UPDATE SET %s',
            self::COLUMN_ID,
            implode(', ', array_map(
                static fn (string $column): string => sprintf('%s = EXCLUDED.%s', $column, $column),
                $updatable,
            )),
        );
    }

    private function platform(): AbstractPlatform
    {
        return $this->guard(fn (): AbstractPlatform => $this->connection->getDatabasePlatform());
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toEntry(array $row): RevocationEntry
    {
        $revocationId = \is_string($row[self::COLUMN_ID]) ? $row[self::COLUMN_ID] : '';

        if ('' === $revocationId) {
            throw new RevocationStoreUnavailableException(sprintf('Table "%s" holds a row with an empty revocation identifier.', $this->table));
        }

        return new RevocationEntry(
            revocationId: $revocationId,
            expiresAt: $this->fromDatabaseDate($row[self::COLUMN_EXPIRES_AT]),
            revokedAt: $this->fromDatabaseDate($row[self::COLUMN_REVOKED_AT]),
            subject: \is_string($row[self::COLUMN_SUBJECT]) ? $row[self::COLUMN_SUBJECT] : null,
            reason: \is_string($row[self::COLUMN_REASON]) ? $row[self::COLUMN_REASON] : null,
            metadata: $this->decodeMetadata($row[self::COLUMN_METADATA]),
        );
    }

    private function toDatabaseDate(?DateTimeImmutable $value): ?string
    {
        return $value?->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_FORMAT);
    }

    private function fromDatabaseDate(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        $utc = new DateTimeZone('UTC');
        $parsed = DateTimeImmutable::createFromFormat('!' . self::DATE_FORMAT, $value, $utc);

        if (false !== $parsed) {
            return $parsed;
        }

        try {
            return new DateTimeImmutable($value, $utc);
        } catch (Throwable $e) {
            throw new RevocationStoreUnavailableException(sprintf('Table "%s" holds an unreadable date "%s".', $this->table, $value), 0, $e);
        }
    }

    /**
     * @param array<string, scalar|null> $metadata
     */
    private function encodeMetadata(array $metadata): ?string
    {
        if ([] === $metadata) {
            return null;
        }

        try {
            $encoded = json_encode($metadata, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
        } catch (JsonException) {
            return null;
        }

        return \strlen($encoded) > self::MAX_METADATA_LENGTH ? null : $encoded;
    }

    /**
     * @return array<string, scalar|null>
     */
    private function decodeMetadata(mixed $value): array
    {
        if (!\is_string($value) || '' === $value) {
            return [];
        }

        try {
            $decoded = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!\is_array($decoded)) {
            return [];
        }

        $metadata = [];

        foreach ($decoded as $key => $item) {
            if (null === $item || \is_scalar($item)) {
                $metadata[(string) $key] = $item;
            }
        }

        return $metadata;
    }

    private function truncate(?string $value, int $length): ?string
    {
        if (null === $value) {
            return null;
        }

        return mb_strlen($value) > $length ? mb_substr($value, 0, $length) : $value;
    }

    private function normalize(string $revocationId): string
    {
        return strtolower($revocationId);
    }

    private function isStorable(string $revocationId): bool
    {
        return '' !== $revocationId
            && \strlen($revocationId) <= self::MAX_ID_LENGTH
            && 1 === preg_match('//u', $revocationId);
    }

    /**
     * @template T
     *
     * @param callable():T $operation
     *
     * @return T
     */
    private function guard(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (TableNotFoundException $e) {
            throw new RevocationStoreUnavailableException(sprintf('Revocation table "%s" does not exist. Run "biscuit:revocation:doctrine:setup", or add the table to your migrations.', $this->table), 0, $e);
        } catch (DbalException|DriverException $e) {
            throw new RevocationStoreUnavailableException($e->getMessage(), 0, $e);
        }
    }
}
