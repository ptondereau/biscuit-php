<?php

declare(strict_types=1);

namespace Biscuit\Auth;

use Error;

/**
 * A cryptographic key pair used to sign Biscuit tokens.
 *
 * ```php
 * $root = new KeyPair();
 *
 * $token = (new BiscuitBuilder('user("alice")'))->build($root->getPrivateKey());
 * $parsed = Biscuit::fromBase64($token->toBase64(), $root->getPublicKey());
 * ```
 */
class KeyPair
{
    /**
     * Generates a new random key pair.
     *
     * @param Algorithm|null $alg Defaults to {@see Algorithm::Ed25519} when null.
     */
    public function __construct(?Algorithm $alg = null)
    {
        throw new Error('Biscuit\Auth\KeyPair::__construct() should be implemented by the biscuit_php extension.');
    }

    /**
     * Rebuilds the key pair corresponding to an existing private key.
     */
    public static function fromPrivateKey(PrivateKey $private_key): KeyPair
    {
        throw new Error('Biscuit\Auth\KeyPair::fromPrivateKey() should be implemented by the biscuit_php extension.');
    }

    public function getPublicKey(): PublicKey
    {
        throw new Error('Biscuit\Auth\KeyPair::getPublicKey() should be implemented by the biscuit_php extension.');
    }

    public function getPrivateKey(): PrivateKey
    {
        throw new Error('Biscuit\Auth\KeyPair::getPrivateKey() should be implemented by the biscuit_php extension.');
    }
}
