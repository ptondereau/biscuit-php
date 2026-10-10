<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

/**
 * Parses repeated key=value --param options into typed values.
 *
 * @internal
 */
trait CliParams
{
    /**
     * Entries without "=" are skipped; a parameter a template needs but does not
     * receive still fails loudly as an unbound Datalog parameter.
     *
     * @param array<int, string> $paramStrings
     *
     * @return array<string, mixed>
     */
    private function parseParams(array $paramStrings): array
    {
        $params = [];

        foreach ($paramStrings as $paramString) {
            if (!str_contains($paramString, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $paramString, 2);

            $params[$key] = $this->parseValue($value);
        }

        return $params;
    }

    private function parseValue(string $value): mixed
    {
        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        if ('null' === $value) {
            return null;
        }

        if (is_numeric($value) && !str_contains($value, '.')) {
            return (int) $value;
        }

        return $value;
    }
}
