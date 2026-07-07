<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Biscuit\Exception\PublicKeyException;
use Error;

/**
 * A public key used to verify Biscuit token signatures.
 *
 * Its canonical string form is an algorithm-prefixed hex string,
 * e.g. `ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189`.
 *
 * ```php
 * $publicKey = new PublicKey('ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189');
 * $token = Biscuit::fromBase64($serialized, $publicKey);
 * ```
 */
class PublicKey
{
    /**
     * Parses a public key from its algorithm-prefixed hex form (`<algorithm>/<hex>`).
     *
     * @param non-empty-string $data
     *
     * @throws PublicKeyException If the input is not a valid public key.
     */
    public function __construct(string $data)
    {
        throw new Error('Biscuit\Auth\PublicKey::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a public key from raw bytes (binary string).
     *
     * @param Algorithm|null $alg Defaults to {@see Algorithm::Ed25519} when null.
     *
     * @throws PublicKeyException If the bytes do not form a valid public key.
     */
    public static function fromBytes(string $data, ?Algorithm $alg = null): PublicKey
    {
        throw new Error('Biscuit\Auth\PublicKey::fromBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a public key from a PEM document.
     *
     * @throws PublicKeyException If the PEM input is invalid.
     */
    public static function fromPem(string $pem): PublicKey
    {
        throw new Error('Biscuit\Auth\PublicKey::fromPem() should be implemented by the biscuit_php extension.');
    }

    /**
     * Deserializes a public key from DER bytes (binary string).
     *
     * @throws PublicKeyException If the DER input is invalid.
     */
    public static function fromDer(string $der): PublicKey
    {
        throw new Error('Biscuit\Auth\PublicKey::fromDer() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the key to raw bytes.
     *
     * @return list<int>
     */
    public function toBytes(): array
    {
        throw new Error('Biscuit\Auth\PublicKey::toBytes() should be implemented by the biscuit_php extension.');
    }

    /**
     * Serializes the key to its algorithm-prefixed hex form (`<algorithm>/<hex>`).
     *
     * @return non-empty-string
     */
    public function toHex(): string
    {
        throw new Error('Biscuit\Auth\PublicKey::toHex() should be implemented by the biscuit_php extension.');
    }

    /**
     * Same representation as {@see PublicKey::toHex()}.
     */
    public function __toString(): string
    {
        throw new Error('Biscuit\Auth\PublicKey::__toString() should be implemented by the biscuit_php extension.');
    }
}
