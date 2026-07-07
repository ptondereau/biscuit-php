<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\Base64Exception;
use Biscuit\Exception\BlockAppendException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\BytesException;
use Biscuit\Exception\SnapshotException;
use Biscuit\Exception\ThirdPartyBlockAppendException;
use Biscuit\Exception\ThirdPartyException;
use Error;

/**
 * A verified Biscuit authorization token.
 *
 * A token is made of an authority block followed by optional attenuation
 * blocks. Deserializing through {@see Biscuit::fromBase64()} or
 * {@see Biscuit::fromBytes()} checks its signatures against the root
 * public key.
 *
 * ```php
 * $root = new KeyPair();
 *
 * $builder = new BiscuitBuilder();
 * $builder->addCode('user({id})', ['id' => '1234']);
 * $token = $builder->build($root->getPrivateKey());
 *
 * $parsed = Biscuit::fromBase64($token->toBase64(), $root->getPublicKey());
 *
 * $authorizer = (new AuthorizerBuilder('allow if user({id})', ['id' => '1234']))->build($parsed);
 * $matched = $authorizer->authorize();
 * ```
 */
class Biscuit
{
    /**
     * Tokens are obtained through {@see Biscuit::builder()},
     * {@see Biscuit::fromBase64()}, {@see Biscuit::fromBytes()}, or
     * {@see UnverifiedBiscuit::verify()}.
     */
    private function __construct() {}

    /**
     * Creates a builder for the authority block of a new token.
     */
    public static function builder(): BiscuitBuilder
    {
        throw new Error('Biscuit\Auth\Biscuit::builder() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a token from raw bytes (binary string) and verifies its
     * signatures against the root public key.
     *
     * @throws BytesException If the input is not a valid token or signature verification fails.
     */
    public static function fromBytes(string $data, PublicKey $root): Biscuit
    {
        throw new Error('Biscuit\Auth\Biscuit::fromBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a token from a URL-safe base64 string and verifies its
     * signatures against the root public key.
     *
     * @throws Base64Exception If the input is not a valid token or signature verification fails.
     */
    public static function fromBase64(string $data, PublicKey $root): Biscuit
    {
        throw new Error('Biscuit\Auth\Biscuit::fromBase64() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the token to raw bytes.
     *
     * @return list<int>
     *
     * @throws BytesException If the token cannot be serialized.
     */
    public function toBytes(): array
    {
        throw new Error('Biscuit\Auth\Biscuit::toBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the token to a URL-safe base64 string.
     *
     * @throws Base64Exception If the token cannot be serialized.
     */
    public function toBase64(): string
    {
        throw new Error('Biscuit\Auth\Biscuit::toBase64() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the number of blocks in the token (at least 1).
     */
    public function blockCount(): int
    {
        throw new Error('Biscuit\Auth\Biscuit::blockCount() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the content of the given block as Datalog source code.
     *
     * @throws SnapshotException If the block index is out of range.
     */
    public function blockSource(int $index): string
    {
        throw new Error('Biscuit\Auth\Biscuit::blockSource() should be implemented by the biscuit_php extension.');
    }

    /**
     * Adds an attenuation block and returns the new token; the original
     * token is left unchanged.
     *
     * @throws BlockAppendException If the block cannot be appended.
     * @throws BuilderStateException If $block has already been consumed.
     */
    public function append(BlockBuilder $block): Biscuit
    {
        throw new Error('Biscuit\Auth\Biscuit::append() should be implemented by the biscuit_php extension.');
    }

    /**
     * Adds a third-party block created by {@see ThirdPartyRequest::createBlock()}
     * and returns the new token.
     *
     * @param PublicKey $external_key The third party's public key.
     *
     * @throws ThirdPartyBlockAppendException If the block cannot be appended.
     */
    public function appendThirdParty(PublicKey $external_key, ThirdPartyBlock $block): Biscuit
    {
        throw new Error('Biscuit\Auth\Biscuit::appendThirdParty() should be implemented by the biscuit_php extension.');
    }

    /**
     * Creates the request to send to a third party so it can produce a
     * signed block for this token.
     *
     * @throws ThirdPartyException If the request cannot be created.
     */
    public function thirdPartyRequest(): ThirdPartyRequest
    {
        throw new Error(
            'Biscuit\Auth\Biscuit::thirdPartyRequest() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns one hex-encoded revocation identifier per block, in order.
     *
     * Revocation identifiers are unique: tokens generated separately with
     * the same contents will have different revocation ids.
     *
     * @return list<non-empty-string>
     */
    public function revocationIds(): array
    {
        throw new Error('Biscuit\Auth\Biscuit::revocationIds() should be implemented by the biscuit_php extension.');
    }

    /**
     * Returns the external public key of the given block, or null when the
     * block has none.
     *
     * Blocks carrying an external public key are third-party blocks whose
     * contents can be trusted as coming from the holder of the corresponding
     * private key.
     *
     * @throws SnapshotException If the block index is out of range.
     */
    public function blockExternalKey(int $index): ?PublicKey
    {
        throw new Error('Biscuit\Auth\Biscuit::blockExternalKey() should be implemented by the biscuit_php extension.');
    }

    /**
     * Pretty-prints the token content.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\Biscuit::__toString() should be implemented by the biscuit_php extension.');
    }
}
