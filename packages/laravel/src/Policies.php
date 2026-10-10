<?php

declare(strict_types=1);

namespace Biscuit\Laravel;

use Biscuit\Auth\Policy;
use Biscuit\Exception\PolicyException;
use Biscuit\Exception\ScopeException;
use Biscuit\Exception\TermException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class Policies
{
    /** @var array<string, non-empty-string> */
    private readonly array $policies;

    /**
     * @param array<array-key, mixed> $policies
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $policies)
    {
        $names = array_map(strval(...), array_keys($policies));

        $this->policies = array_combine($names, array_map(self::source(...), $names, $policies));
    }

    /**
     * @param array<array-key, mixed> $arguments
     *
     * @throws InvalidArgumentException
     * @throws PolicyException
     * @throws ScopeException
     * @throws TermException
     */
    public function resolve(string $ability, array $arguments): ?Policy
    {
        $source = $this->policies[$ability] ?? (self::isInline($ability) ? $ability : null);

        if (null === $source) {
            return null;
        }

        $used = array_filter(
            self::params($arguments),
            static fn(int|string $name): bool => str_contains($source, '{' . $name . '}'),
            \ARRAY_FILTER_USE_KEY,
        );
        $names = array_map(strval(...), array_keys($used));

        return new Policy($source, array_combine($names, array_map(self::term(...), $names, $used)));
    }

    /**
     * @pure
     *
     * @psalm-assert-if-true non-empty-string $ability
     */
    private static function isInline(string $ability): bool
    {
        return str_starts_with($ability, 'allow ') || str_starts_with($ability, 'deny ');
    }

    /**
     * @return non-empty-string
     *
     * @throws InvalidArgumentException
     */
    private static function source(string $name, mixed $source): string
    {
        if (!\is_string($source) || '' === $source) {
            throw new InvalidArgumentException(sprintf(
                'Biscuit policy "%s" must be a non-empty Datalog string.',
                $name,
            ));
        }

        return $source;
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function term(string $name, mixed $value): string|int|bool
    {
        if (!\is_string($value) && !\is_int($value) && !\is_bool($value)) {
            throw new InvalidArgumentException(sprintf(
                'Biscuit policy parameter "%s" must be a string, an integer or a boolean.',
                $name,
            ));
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $arguments
     *
     * @return array<array-key, mixed>
     */
    private static function params(array $arguments): array
    {
        return array_is_list($arguments) ? self::subjectParams($arguments[0] ?? null) : $arguments;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function subjectParams(mixed $subject): array
    {
        return match (true) {
            $subject instanceof Model => self::subjectParams($subject->getKey()),
            \is_array($subject) => $subject,
            \is_string($subject), \is_int($subject) => ['resource' => (string) $subject],
            default => [],
        };
    }
}
