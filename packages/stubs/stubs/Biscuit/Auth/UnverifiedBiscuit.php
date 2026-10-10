<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\Base64Exception;
use Biscuit\Exception\BlockAppendException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\SignatureException;
use Biscuit\Exception\SnapshotException;
use Error;
use TypeError;

/**
 * A token parsed without cryptographic signature verification.
 *
 * Use this to attenuate or inspect the content of a token without
 * verifying it. Call {@see UnverifiedBiscuit::verify()} to check the
 * signatures and obtain a {@see Biscuit} usable for authorization.
 *
 * ```php
 * $utoken = UnverifiedBiscuit::fromBase64($serialized);
 * $rootKeyHint = $utoken->rootKeyId();
 *
 * $verified = $utoken->verify($rootPublicKey);
 * ```
 */
class UnverifiedBiscuit
{
    /**
     * Instances are obtained through {@see UnverifiedBiscuit::fromBase64()}.
     */
    private function __construct() {}

    /**
     * Deserializes a token from a URL-safe base64 string, without verifying
     * its signatures.
     *
     * @throws Base64Exception If the input is not a valid token.
     */
    public static function fromBase64(string $data): UnverifiedBiscuit
    {
        throw new Error(
            'Biscuit\Auth\UnverifiedBiscuit::fromBase64() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the optional root key identifier stored in the token, a hint
     * for public key selection during verification
     * (see {@see BiscuitBuilder::setRootKeyId()}).
     */
    public function rootKeyId(): ?int
    {
        throw new Error(
            'Biscuit\Auth\UnverifiedBiscuit::rootKeyId() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the number of blocks in the token (at least 1).
     */
    public function blockCount(): int
    {
        throw new Error(
            'Biscuit\Auth\UnverifiedBiscuit::blockCount() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Returns the content of the given block as Datalog source code.
     *
     * @throws SnapshotException If the block index is out of range.
     */
    public function blockSource(int $index): string
    {
        throw new Error(
            'Biscuit\Auth\UnverifiedBiscuit::blockSource() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Adds an attenuation block and returns the new token; the original
     * token is left unchanged.
     *
     * @throws BlockAppendException If the block cannot be appended.
     * @throws BuilderStateException If $block has already been consumed.
     */
    public function append(BlockBuilder $block): UnverifiedBiscuit
    {
        throw new Error('Biscuit\Auth\UnverifiedBiscuit::append() should be implemented by the biscuit_php extension.');
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
        throw new Error(
            'Biscuit\Auth\UnverifiedBiscuit::revocationIds() should be implemented by the biscuit_php extension.',
        );
    }

    /**
     * Checks the token signatures against the root public key and converts
     * it to a {@see Biscuit} usable for authorization.
     *
     * $root is one public key or a key set. The key set uses the root key ID
     * as the index. The extension uses the key that has the same ID as
     * {@see UnverifiedBiscuit::rootKeyId()}.
     *
     * @param PublicKey|array<int, PublicKey> $root
     *
     * @throws SignatureException If the key set has no key for the root key ID, or the signature is not correct.
     * @throws TypeError If $root is not a PublicKey and not an array of PublicKey.
     */
    public function verify(PublicKey|array $root): Biscuit
    {
        throw new Error('Biscuit\Auth\UnverifiedBiscuit::verify() should be implemented by the biscuit_php extension.');
    }
}
