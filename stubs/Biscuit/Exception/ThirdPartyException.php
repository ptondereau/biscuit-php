<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when a third-party request or block cannot be created
 * ({@see \Biscuit\Auth\Biscuit::thirdPartyRequest()},
 * {@see \Biscuit\Auth\ThirdPartyRequest::createBlock()}).
 */
class ThirdPartyException extends BiscuitException
{
    private function __construct() {}
}
