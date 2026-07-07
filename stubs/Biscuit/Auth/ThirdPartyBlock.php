<?php

declare(strict_types=1);

namespace Biscuit\Auth;

/**
 * Signed third-party block content.
 *
 * It must be integrated into the token that created the originating
 * {@see ThirdPartyRequest} using {@see Biscuit::appendThirdParty()}.
 */
class ThirdPartyBlock
{
    /**
     * Instances are obtained through {@see ThirdPartyRequest::createBlock()}.
     */
    private function __construct() {}
}
