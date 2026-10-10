<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineRevocationStore::class)]
final class DoctrineRevocationStoreTest extends TestCase
{
    private Connection $connection;

    #[Test]
    public function itFindsAnIdentifierItWrote(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itReturnsTheIdentifierExactlyAsTheCallerSpelledIt(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('ABC123'));

        self::assertSame(
            'AbC123',
            $store->findRevoked(['AbC123']),
            'Callers correlate the returned id with the token, so it must come back byte for byte.',
        );
    }

    #[Test]
    public function itIgnoresCaseWhenMatching(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('ABCDEF'));

        self::assertSame('abcdef', $store->findRevoked(['abcdef']));
    }

    #[Test]
    public function itReturnsTheFirstMatchInCallerOrderNotWhicheverRowTheDatabaseEmits(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('zzz'));
        $store->revoke(new RevocationEntry('aaa'));

        self::assertSame(
            'zzz',
            $store->findRevoked(['zzz', 'aaa']),
            'A revocation chain is ordered, so the shallowest match must win regardless of row order.',
        );
    }

    #[Test]
    public function itReportsNoMatchForAnUnknownIdentifier(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc'));

        self::assertNull($store->findRevoked(['def']));
    }

    #[Test]
    public function itQueriesNothingForAnEmptyChain(): void
    {
        $store = $this->storeWithTable();

        self::assertNull($store->findRevoked([]));
    }

    #[Test]
    public function itSkipsAnIdentifierTooLongToBeStored(): void
    {
        $store = $this->storeWithTable();

        self::assertNull($store->findRevoked([str_repeat('a', 300)]));
    }

    #[Test]
    public function itSkipsAnIdentifierThatIsNotValidUtf8(): void
    {
        $store = $this->storeWithTable();

        self::assertNull(
            $store->findRevoked(["\xB1\x31"]),
            'A malformed id must not reach the driver, or a forged token becomes a 500 on every request.',
        );
    }

    #[Test]
    public function itUpdatesTheExistingRowWhenTheSameIdentifierIsRevokedTwice(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc', subject: 'alice', reason: 'logout'));
        $store->revoke(new RevocationEntry('abc', subject: 'bob', reason: 'compromised'));

        $entries = [...$store->all()];

        self::assertCount(1, $entries);
        self::assertSame('bob', $entries[0]->subject);
        self::assertSame('compromised', $entries[0]->reason);
    }

    #[Test]
    public function itRoundTripsEveryFieldOfAnEntry(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry(
            revocationId: 'abc',
            expiresAt: new DateTimeImmutable('2026-09-01T12:30:45Z'),
            revokedAt: new DateTimeImmutable('2026-08-01T09:00:00Z'),
            subject: 'alice',
            reason: 'logout',
            metadata: ['device' => 'phone', 'attempts' => 3, 'trusted' => false, 'note' => null],
        ));

        $entry = [...$store->all()][0];

        self::assertSame('abc', $entry->revocationId);
        self::assertSame('2026-09-01 12:30:45', $entry->expiresAt?->format('Y-m-d H:i:s'));
        self::assertSame('2026-08-01 09:00:00', $entry->revokedAt?->format('Y-m-d H:i:s'));
        self::assertSame('alice', $entry->subject);
        self::assertSame('logout', $entry->reason);
        self::assertSame(['device' => 'phone', 'attempts' => 3, 'trusted' => false, 'note' => null], $entry->metadata);
    }

    #[Test]
    public function itNormalisesADateToUtcRatherThanStoringItsWallClock(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc', new DateTimeImmutable('2026-09-01T14:30:00+02:00')));

        self::assertSame(
            '2026-09-01 12:30:00',
            [...$store->all()][0]->expiresAt?->format('Y-m-d H:i:s'),
            'Storing the wall clock without its offset is the classic Doctrine timezone bug.',
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonUtcTimezones(): iterable
    {
        yield 'ahead of utc' => ['Europe/Paris'];
        yield 'behind utc' => ['America/New_York'];
        yield 'half hour offset' => ['Asia/Kolkata'];
    }

    #[Test]
    #[DataProvider('nonUtcTimezones')]
    public function itReadsADateBackAsUtcWhateverThePhpDefaultTimezoneIs(string $timezone): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set($timezone);

        try {
            $expiresAt = new DateTimeImmutable('2026-09-01T12:00:00Z');
            $store = $this->storeWithTable();
            $store->revoke(new RevocationEntry('abc', $expiresAt));

            self::assertSame(
                $expiresAt->getTimestamp(),
                [...$store->all()][0]->expiresAt?->getTimestamp(),
                'A non-UTC date.timezone must not shift the instant, or purge deletes the wrong rows.',
            );
        } finally {
            date_default_timezone_set($previous);
        }
    }

    #[Test]
    #[DataProvider('nonUtcTimezones')]
    public function itPurgesTheSameRowsWhateverThePhpDefaultTimezoneIs(string $timezone): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set($timezone);

        try {
            $store = $this->storeWithTable();
            $store->revoke(new RevocationEntry('expired', new DateTimeImmutable('2026-08-01T00:00:00Z')));
            $store->revoke(new RevocationEntry('alive', new DateTimeImmutable('2026-09-01T00:00:00Z')));

            self::assertSame(1, $store->purgeExpired(new DateTimeImmutable('2026-08-15T00:00:00Z')));
            self::assertSame('alive', $store->findRevoked(['alive']));
        } finally {
            date_default_timezone_set($previous);
        }
    }

    #[Test]
    public function itKeepsAnEntryWithNoExpiration(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc'));

        self::assertNull([...$store->all()][0]->expiresAt);
    }

    #[Test]
    public function itRemovesAnIdentifierOnUnrevoke(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc'));
        $store->unrevoke('abc');

        self::assertNull($store->findRevoked(['abc']));
    }

    #[Test]
    public function itUnrevokesRegardlessOfCase(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc'));
        $store->unrevoke('ABC');

        self::assertNull($store->findRevoked(['abc']));
    }

    #[Test]
    public function itPurgesOnlyEntriesThatHaveExpired(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('expired', new DateTimeImmutable('2026-08-01T00:00:00Z')));
        $store->revoke(new RevocationEntry('alive', new DateTimeImmutable('2026-09-01T00:00:00Z')));

        $purged = $store->purgeExpired(new DateTimeImmutable('2026-08-15T00:00:00Z'));

        self::assertSame(1, $purged);
        self::assertNull($store->findRevoked(['expired']));
        self::assertSame('alive', $store->findRevoked(['alive']));
    }

    #[Test]
    public function itNeverPurgesAnEntryWithNoExpiration(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('forever'));

        $store->purgeExpired(new DateTimeImmutable('2099-01-01T00:00:00Z'));

        self::assertSame(
            'forever',
            $store->findRevoked(['forever']),
            'A null expiration means keep forever; purging it would silently un-revoke a token.',
        );
    }

    #[Test]
    public function itEnumeratesPastOnePageOfResults(): void
    {
        $store = $this->storeWithTable();

        for ($i = 0; $i < 1050; ++$i) {
            $store->revoke(new RevocationEntry(sprintf('id%04d', $i)));
        }

        self::assertCount(1050, [...$store->all()]);
    }

    #[Test]
    public function itEnumeratesInIdentifierOrderSoPagingCannotSkipOrRepeat(): void
    {
        $store = $this->storeWithTable();

        foreach (['ccc', 'aaa', 'bbb'] as $revocationId) {
            $store->revoke(new RevocationEntry($revocationId));
        }

        $ids = array_map(
            static fn (RevocationEntry $entry): string => $entry->revocationId,
            [...$store->all()],
        );

        self::assertSame(['aaa', 'bbb', 'ccc'], $ids);
    }

    #[Test]
    public function itStillRecordsTheRevocationWhenTheSubjectIsTooLongForTheColumn(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc', subject: str_repeat('x', 400)));

        self::assertSame(
            'abc',
            $store->findRevoked(['abc']),
            'A long username must never be the reason a revocation is not recorded.',
        );
        self::assertSame(255, mb_strlen((string) [...$store->all()][0]->subject));
    }

    #[Test]
    public function itDropsMetadataItCannotEncodeRatherThanFailingTheWrite(): void
    {
        $store = $this->storeWithTable();

        $store->revoke(new RevocationEntry('abc', metadata: ['bad' => "\xB1\x31"]));

        self::assertSame('abc', $store->findRevoked(['abc']));
        self::assertSame([], [...$store->all()][0]->metadata);
    }

    #[Test]
    public function itReadsARowWithUnreadableMetadataAsHavingNone(): void
    {
        $store = $this->storeWithTable();
        $store->revoke(new RevocationEntry('abc'));
        $this->connection->executeStatement('UPDATE biscuit_revoked_tokens SET metadata = ?', ['{not json']);

        self::assertSame(
            [],
            [...$store->all()][0]->metadata,
            'One corrupt row must not abort the whole enumeration.',
        );
    }

    #[Test]
    public function itNamesTheSetupCommandWhenTheTableIsMissing(): void
    {
        $store = $this->storeWithTable();
        $this->connection->executeStatement('DROP TABLE biscuit_revoked_tokens');

        $this->expectException(RevocationStoreUnavailableException::class);
        $this->expectExceptionMessageMatches('/biscuit:revocation:doctrine:setup/');

        $store->findRevoked(['abc']);
    }

    #[Test]
    public function itReportsAMissingTableWhenEnumerating(): void
    {
        $store = $this->storeWithTable();
        $this->connection->executeStatement('DROP TABLE biscuit_revoked_tokens');

        $this->expectException(RevocationStoreUnavailableException::class);

        foreach ($store->all() as $entry) {
            self::fail('Expected the missing table to surface before any entry: ' . $entry->revocationId);
        }
    }

    #[Test]
    public function itReportsAMissingTableWhenWriting(): void
    {
        $store = $this->storeWithTable();
        $this->connection->executeStatement('DROP TABLE biscuit_revoked_tokens');

        $this->expectException(RevocationStoreUnavailableException::class);

        $store->revoke(new RevocationEntry('abc'));
    }

    #[Test]
    public function itHonoursAConfiguredTableName(): void
    {
        $store = $this->storeWithTable('app_revocations');

        $store->revoke(new RevocationEntry('abc'));

        self::assertSame(
            '1',
            (string) $this->connection->fetchOne('SELECT COUNT(*) FROM app_revocations'),
        );
    }

    #[Test]
    public function itReportsWhetherItsTableExists(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $store = new DoctrineRevocationStore($this->connection);

        self::assertFalse($store->tableExists());

        foreach ($store->schemaSql() as $sql) {
            $this->connection->executeStatement($sql);
        }

        self::assertTrue($store->tableExists());
    }

    private function storeWithTable(string $table = DoctrineRevocationStore::DEFAULT_TABLE): DoctrineRevocationStore
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $store = new DoctrineRevocationStore($this->connection, $table);

        foreach ($store->schemaSql() as $sql) {
            $this->connection->executeStatement($sql);
        }

        return $store;
    }
}
