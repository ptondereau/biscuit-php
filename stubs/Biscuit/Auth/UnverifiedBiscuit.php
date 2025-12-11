<?php

/**
 * auto generated file by PHPExtensionStubGenerator
 */
namespace Biscuit\Auth;

class UnverifiedBiscuit
{
    public static function fromBase64(string $data): \Biscuit\Auth\UnverifiedBiscuit
    {
    }

    public function rootKeyId(): null|int
    {
    }

    public function blockCount(): int
    {
    }

    public function blockSource(int $index): string
    {
    }

    public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\UnverifiedBiscuit
    {
    }

    public function revocationIds(): array
    {
    }

    public function verify(\Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit
    {
    }

    public function __construct() {}
}
