<?php

declare(strict_types=1);

namespace Biscuit\Exception;

/**
 * Thrown when using a builder or third-party request that has already been
 * consumed, for example a {@see \Biscuit\Auth\BlockBuilder} passed to a
 * merge method, or a {@see \Biscuit\Auth\ThirdPartyRequest} whose
 * createBlock() was already called.
 */
class BuilderStateException extends BiscuitException
{
    private function __construct() {}
}
