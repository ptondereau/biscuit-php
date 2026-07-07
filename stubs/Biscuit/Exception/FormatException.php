<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Base class for serialization and deserialization failures.
 */
class FormatException extends BiscuitException
{
    private function __construct() {}
}
