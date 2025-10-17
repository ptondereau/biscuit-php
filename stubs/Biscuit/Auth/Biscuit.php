<?php
/**
 * auto generated file by PHPExtensionStubGenerator
 */
namespace Biscuit\Auth;

class Biscuit implements \Stringable
{
    public static function builder() : \Biscuit\Auth\BiscuitBuilder
    {
    }

    public static function fromBytes(string $data, \Biscuit\Auth\PublicKey $root) : \Biscuit\Auth\Biscuit
    {
    }

    public static function fromBase64(string $data, \Biscuit\Auth\PublicKey $root) : \Biscuit\Auth\Biscuit
    {
    }

    public function toBytes() : array
    {
    }

    public function toBase64() : string
    {
    }

    public function blockCount() : int
    {
    }

    public function blockSource(int $index) : string
    {
    }

    public function append(\Biscuit\Auth\BlockBuilder $block) : \Biscuit\Auth\Biscuit
    {
    }

    public function appendThirdParty(\Biscuit\Auth\PublicKey $external_key, \Biscuit\Auth\ThirdPartyBlock $block) : \Biscuit\Auth\Biscuit
    {
    }

    public function thirdPartyRequest() : \Biscuit\Auth\ThirdPartyRequest
    {
    }

    public function revocationIds() : array
    {
    }

    public function blockExternalKey(int $index) : ?\Biscuit\Auth\PublicKey
    {
    }

    public function __toString() : string
    {
    }

    public function __construct()
    {
    }
}
