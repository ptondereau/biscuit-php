<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a third-party block cannot be appended to a token
 * ({@see \Biscuit\Auth\Biscuit::appendThirdParty()}).
 */
class ThirdPartyBlockAppendException extends BuildException
{
    private function __construct() {}
}
