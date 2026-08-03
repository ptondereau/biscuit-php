<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Datalog;

use DateTimeImmutable;
use Exception;

final class AuthorityBlockReader
{
    private const DATE_PATTERN = '\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})';

    /**
     * @return non-empty-string|null
     */
    public function readFact(string $source, string $factName): ?string
    {
        $quotedName = preg_quote($factName, '/');

        if (1 === preg_match('/' . $quotedName . '\("([^"]+)"\)/', $source, $matches) && '' !== $matches[1]) {
            return $matches[1];
        }

        if (1 === preg_match('/' . $quotedName . '\((\d+)\)/', $source, $matches) && '' !== $matches[1]) {
            return $matches[1];
        }

        return null;
    }

    public function readExpiry(string $source): ?DateTimeImmutable
    {
        $pattern = '/time\(\s*\$[A-Za-z_][A-Za-z0-9_]*\s*\)\s*,\s*\$[A-Za-z_][A-Za-z0-9_]*\s*<=?\s*(' . self::DATE_PATTERN . ')/';

        if (0 === preg_match_all($pattern, $source, $matches)) {
            return null;
        }

        $earliest = null;

        foreach ($matches[1] as $candidate) {
            try {
                $date = new DateTimeImmutable($candidate);
            } catch (Exception) {
                continue;
            }

            if (null === $earliest || $date < $earliest) {
                $earliest = $date;
            }
        }

        return $earliest;
    }
}
