<?php
/**
 * auto generated file by PHPExtensionStubGenerator
 */
namespace Biscuit\Auth;

class Biscuit
{
    public static function fromBase64(string $biscuit, \Biscuit\Auth\PublicKey $public_key) : \Biscuit\Auth\Biscuit
    {
    }

    public function toBase64() : string
    {
    }

    public function authorizer() : \Biscuit\Auth\Authorizer
    {
    }

    public function __construct(\Biscuit\Auth\PrivateKey $root_key)
    {
    }
}
