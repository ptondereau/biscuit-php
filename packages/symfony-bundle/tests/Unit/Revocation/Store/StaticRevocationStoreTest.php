<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\Store\StaticRevocationStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticRevocationStore::class)]
final class StaticRevocationStoreTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->temporaryFiles = [];
    }

    #[Test]
    public function itIsAnEnumerableStore(): void
    {
        self::assertInstanceOf(EnumerableRevocationStoreInterface::class, new StaticRevocationStore());
    }

    #[Test]
    public function itReturnsNullWhenTheListIsEmpty(): void
    {
        $store = new StaticRevocationStore();

        self::assertNull($store->findRevoked(['abc', 'def']));
    }

    #[Test]
    public function itMatchesAConfiguredId(): void
    {
        $store = new StaticRevocationStore(['abc', 'def']);

        self::assertSame('def', $store->findRevoked(['xyz', 'def']));
    }

    #[Test]
    public function itMatchesCaseInsensitively(): void
    {
        $store = new StaticRevocationStore(['ABC']);

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itReturnsTheCallerSpellingOfTheId(): void
    {
        $store = new StaticRevocationStore(['abc']);

        self::assertSame('ABC', $store->findRevoked(['ABC']));
    }

    #[Test]
    public function itFlattensResolvedCsvEnvArrays(): void
    {
        $store = new StaticRevocationStore([['abc', 'def'], 'ghi']);

        self::assertSame('abc', $store->findRevoked(['abc']));
        self::assertSame('def', $store->findRevoked(['def']));
        self::assertSame('ghi', $store->findRevoked(['ghi']));
    }

    #[Test]
    public function itAcceptsASingleIdAsAString(): void
    {
        $store = new StaticRevocationStore('abc');

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itTrimsAndDropsEmptyEntries(): void
    {
        $store = new StaticRevocationStore(['  abc  ', '', '   ']);

        self::assertSame('abc', $store->findRevoked(['abc']));
        self::assertCount(1, $this->entries($store));
    }

    #[Test]
    public function itIgnoresNonStringEntries(): void
    {
        $store = new StaticRevocationStore(['abc', 42, null, true]);

        self::assertCount(1, $this->entries($store));
    }

    #[Test]
    public function itEnumeratesEntriesWithoutDuplicates(): void
    {
        $store = new StaticRevocationStore(['abc', 'ABC', 'def']);

        $ids = array_map(
            static fn (RevocationEntry $entry): string => $entry->revocationId,
            $this->entries($store),
        );

        self::assertSame(['abc', 'def'], $ids);
    }

    #[Test]
    public function itReadsNewlineDelimitedIdsFromAFile(): void
    {
        $file = $this->writeTemporaryFile("abc\ndef\n\nghi\n");

        $store = new StaticRevocationStore([], $file);

        self::assertSame('ghi', $store->findRevoked(['ghi']));
        self::assertCount(3, $this->entries($store));
    }

    #[Test]
    public function itReadsJsonListsFromAFile(): void
    {
        $file = $this->writeTemporaryFile('["abc", "def"]');

        $store = new StaticRevocationStore([], $file);

        self::assertSame('def', $store->findRevoked(['def']));
    }

    #[Test]
    public function itMergesConfiguredIdsWithFileIds(): void
    {
        $file = $this->writeTemporaryFile("def\n");

        $store = new StaticRevocationStore(['abc'], $file);

        self::assertSame('abc', $store->findRevoked(['abc']));
        self::assertSame('def', $store->findRevoked(['def']));
    }

    #[Test]
    public function itTreatsAnEmptyFileAsAnEmptyList(): void
    {
        $file = $this->writeTemporaryFile("\n  \n");

        $store = new StaticRevocationStore([], $file);

        self::assertNull($store->findRevoked(['abc']));
    }

    #[Test]
    public function itThrowsStoreUnavailableWhenTheFileIsMissing(): void
    {
        $store = new StaticRevocationStore([], '/nonexistent/biscuit-revoked.txt');

        $this->expectException(RevocationStoreUnavailableException::class);

        $store->findRevoked(['abc']);
    }

    #[Test]
    public function itThrowsStoreUnavailableWhenTheJsonFileIsMalformed(): void
    {
        $file = $this->writeTemporaryFile('[not json');

        $store = new StaticRevocationStore([], $file);

        $this->expectException(RevocationStoreUnavailableException::class);

        $store->findRevoked(['abc']);
    }

    private function writeTemporaryFile(string $contents): string
    {
        $file = tempnam(sys_get_temp_dir(), 'biscuit-revoked-');
        self::assertIsString($file);

        file_put_contents($file, $contents);
        $this->temporaryFiles[] = $file;

        return $file;
    }

    /**
     * @return list<RevocationEntry>
     */
    private function entries(EnumerableRevocationStoreInterface $store): array
    {
        $entries = [];

        foreach ($store->all() as $entry) {
            $entries[] = $entry;
        }

        return $entries;
    }
}
