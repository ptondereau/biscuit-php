<?php

/**
 * auto generated file by PHPExtensionStubGenerator
 */
namespace Biscuit\Auth;

class KeyPair
{
    public static function newWithAlgorithm(null|Algorithm $alg = null): \Biscuit\Auth\KeyPair
    {
    }

    public static function fromPrivateKey(\Biscuit\Auth\PrivateKey $private_key): \Biscuit\Auth\KeyPair
    {
    }

    public function public(): \Biscuit\Auth\PublicKey
    {
    }

    public function private(): \Biscuit\Auth\PrivateKey
    {
    }

    public function __construct() {}
}
