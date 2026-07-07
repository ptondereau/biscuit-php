<?php

declare(strict_types=1);

namespace Biscuit\Exception;

use Exception;

/**
 * Base class for all exceptions thrown by the biscuit_php extension.
 */
class BiscuitException extends Exception
{
    private function __construct() {}
}
