<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Message;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

/**
 * @internal
 */
final class Wire
{
    public const DATE_FORMAT = DateTimeInterface::RFC3339_EXTENDED;

    public static function date(DateTimeImmutable $date): string
    {
        return $date->format(self::DATE_FORMAT);
    }

    public static function nullableDate(?DateTimeImmutable $date): ?string
    {
        return null === $date ? null : self::date($date);
    }

    public static function toDate(string $value, string $field): DateTimeImmutable
    {
        if ('' === trim($value)) {
            throw new InvalidArgumentException(sprintf('Revocation message field "%s" carries an empty date.', $field));
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Revocation message field "%s" is not a valid date: "%s".', $field, $value), 0, $e);
        }
    }

    public static function toNullableDate(?string $value, string $field): ?DateTimeImmutable
    {
        return null === $value ? null : self::toDate($value, $field);
    }

    /**
     * @return non-empty-string
     */
    public static function revocationId(string $value): string
    {
        if ('' === $value) {
            throw new InvalidArgumentException('Revocation message carries an empty revocation id.');
        }

        return $value;
    }
}
