<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\PrivateKeyException;
use Error;

/**
 * A private key used to sign Biscuit tokens.
 *
 * Its canonical string form is an algorithm-prefixed hex string,
 * e.g. `ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97`.
 *
 * ```php
 * $privateKey = PrivateKey::generate();
 * $token = (new BiscuitBuilder('user("alice")'))->build($privateKey);
 * ```
 */
class PrivateKey
{
    /**
     * Parses a private key from its algorithm-prefixed hex form
     * (`ed25519-private/<hex>` or `secp256r1-private/<hex>`).
     *
     * @param non-empty-string $data
     *
     * @throws PrivateKeyException If the input is not a valid private key.
     */
    public function __construct(string $data)
    {
        throw new Error('Biscuit\Auth\PrivateKey::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a private key from raw bytes (binary string).
     *
     * @param Algorithm|null $alg Defaults to {@see Algorithm::Ed25519} when null.
     *
     * @throws PrivateKeyException If the bytes do not form a valid private key.
     */
    public static function fromBytes(string $data, ?Algorithm $alg = null): PrivateKey
    {
        throw new Error('Biscuit\Auth\PrivateKey::fromBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a private key from a PEM document.
     *
     * @throws PrivateKeyException If the PEM input is invalid.
     */
    public static function fromPem(string $pem): PrivateKey
    {
        throw new Error('Biscuit\Auth\PrivateKey::fromPem() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a private key from DER bytes (binary string).
     *
     * @throws PrivateKeyException If the DER input is invalid.
     */
    public static function fromDer(string $der): PrivateKey
    {
        throw new Error('Biscuit\Auth\PrivateKey::fromDer() should be implemented by the biscuit_php extension.');
    }

    /**
     * Generates a new random private key.
     *
     * @param Algorithm|null $alg Defaults to {@see Algorithm::Ed25519} when null.
     */
    public static function generate(?Algorithm $alg = null): PrivateKey
    {
        throw new Error('Biscuit\Auth\PrivateKey::generate() should be implemented by the biscuit_php extension.');
    }

    /**
     * Derives the public key matching this private key.
     */
    public function getPublicKey(): PublicKey
    {
        throw new Error('Biscuit\Auth\PrivateKey::getPublicKey() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the key to raw bytes.
     *
     * @return list<int>
     */
    public function toBytes(): array
    {
        throw new Error('Biscuit\Auth\PrivateKey::toBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the key to its algorithm-prefixed hex form (`<algorithm>-private/<hex>`).
     *
     * @return non-empty-string
     */
    public function toHex(): string
    {
        throw new Error('Biscuit\Auth\PrivateKey::toHex() should be implemented by the biscuit_php extension.');
    }

    /**
     * Same representation as {@see PrivateKey::toHex()}.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\PrivateKey::__toString() should be implemented by the biscuit_php extension.');
    }
}
