<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\ThirdPartyException;
use Error;

/**
 * A request sent to a third party so it can add a signed block to a token
 * without seeing its content.
 *
 * ```php
 * $request = $token->thirdPartyRequest();
 *
 * $block = new BlockBuilder();
 * $block->addCode('external_fact({fact})', ['fact' => '56']);
 * $thirdPartyBlock = $request->createBlock($thirdPartyKeyPair->getPrivateKey(), $block);
 *
 * $token = $token->appendThirdParty($thirdPartyKeyPair->getPublicKey(), $thirdPartyBlock);
 * ```
 */
class ThirdPartyRequest
{
    /**
     * Instances are obtained through {@see Biscuit::thirdPartyRequest()}.
     */
    private function __construct() {}

    /**
     * Creates a {@see ThirdPartyBlock} signed with the third party's
     * private key.
     *
     * The request is consumed by this call: calling it a second time throws
     * a {@see BuilderStateException}.
     *
     * @throws BuilderStateException If the request or $block has already been consumed.
     * @throws ThirdPartyException If the block cannot be created.
     */
    public function createBlock(PrivateKey $private_key, BlockBuilder $block): ThirdPartyBlock
    {
        throw new Error(
            'Biscuit\Auth\ThirdPartyRequest::createBlock() should be implemented by the biscuit_php extension.',
        );
    }
}
