<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;

final class StaticRevocationStore implements EnumerableRevocationStoreInterface
{
    /**
     * @var list<non-empty-string>|null
     */
    private ?array $ids = null;

    /**
     * @var array<string, true>
     */
    private array $index = [];

    /**
     * @param array<array-key, mixed>|string $revocationIds a list of ids, or an env placeholder
     */
    public function __construct(
        private readonly array|string $revocationIds = [],
        private readonly ?string $file = null,
    ) {
    }

    public function findRevoked(array $revocationIds): ?string
    {
        if ([] === $this->ids()) {
            return null;
        }

        foreach ($revocationIds as $revocationId) {
            if (isset($this->index[strtolower($revocationId)])) {
                return $revocationId;
            }
        }

        return null;
    }

    public function all(): iterable
    {
        foreach ($this->ids() as $revocationId) {
            yield new RevocationEntry($revocationId);
        }
    }

    /**
     * @return list<non-empty-string>
     */
    private function ids(): array
    {
        if (null !== $this->ids) {
            return $this->ids;
        }

        $ids = $this->normalize($this->revocationIds);

        if (null !== $this->file) {
            $ids = [...$ids, ...$this->normalize($this->readFile($this->file))];
        }

        $index = [];
        $unique = [];

        foreach ($ids as $id) {
            if (isset($index[$id])) {
                continue;
            }

            $index[$id] = true;
            $unique[] = $id;
        }

        $this->index = $index;

        return $this->ids = $unique;
    }

    /**
     * @return list<non-empty-string>
     */
    private function normalize(mixed $value): array
    {
        $input = \is_array($value) ? $value : [$value];
        $normalized = [];

        array_walk_recursive($input, static function (mixed $id) use (&$normalized): void {
            if (!\is_string($id)) {
                return;
            }

            $id = strtolower(trim($id));

            if ('' !== $id) {
                $normalized[] = $id;
            }
        });

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private function readFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new RevocationStoreUnavailableException(sprintf('Revocation list file "%s" is missing or not readable.', $file));
        }

        $contents = @file_get_contents($file);

        if (false === $contents) {
            throw new RevocationStoreUnavailableException(sprintf('Revocation list file "%s" could not be read.', $file));
        }

        $trimmed = trim($contents);

        if ('' === $trimmed) {
            return [];
        }

        if (str_starts_with($trimmed, '[')) {
            $decoded = json_decode($trimmed, true);

            if (!\is_array($decoded)) {
                throw new RevocationStoreUnavailableException(sprintf('Revocation list file "%s" does not contain a valid JSON list.', $file));
            }

            return array_values(array_filter($decoded, 'is_string'));
        }

        $lines = preg_split('/\R/', $trimmed);

        return false === $lines ? [] : $lines;
    }
}
