<?php

/**
 * auto generated file by PHPExtensionStubGenerator
 */
namespace Biscuit\Auth;

class AuthorizerBuilder implements \Stringable
{
    public function addCode(string $source): void
    {
    }

    public function addCodeWithParams(string $source, array $params, array $scope_params): void
    {
    }

    public function addFact(\Biscuit\Auth\Fact $fact): void
    {
    }

    public function addRule(\Biscuit\Auth\Rule $rule): void
    {
    }

    public function addCheck(\Biscuit\Auth\Check $check): void
    {
    }

    public function addPolicy(\Biscuit\Auth\Policy $policy): void
    {
    }

    public function setTime()
    {
    }

    public function merge(\Biscuit\Auth\AuthorizerBuilder $other)
    {
    }

    public function mergeBlock(\Biscuit\Auth\BlockBuilder $block)
    {
    }

    public function base64Snapshot(): string
    {
    }

    public function rawSnapshot(): array
    {
    }

    public static function fromBase64Snapshot(string $input): \Biscuit\Auth\AuthorizerBuilder
    {
    }

    public static function fromRawSnapshot(string $input): \Biscuit\Auth\AuthorizerBuilder
    {
    }

    public function build(#[\SensitiveParameter] \Biscuit\Auth\Biscuit $token): \Biscuit\Auth\Authorizer
    {
    }

    public function buildUnauthenticated(): \Biscuit\Auth\Authorizer
    {
    }

    public function __toString(): string
    {
    }

    public function __construct() {}
}
