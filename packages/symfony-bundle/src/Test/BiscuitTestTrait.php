<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Test;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;

/**
 * Trait providing test utilities for Biscuit token testing.
 *
 * Use this trait in your test classes to easily create test tokens
 * without needing to manage real keys.
 *
 * @example
 * ```php
 * class MyApiTest extends TestCase
 * {
 *     use BiscuitTestTrait;
 *
 *     public function testEndpoint(): void
 *     {
 *         $token = $this->createTestTokenBase64('user("john"); right("read")');
 *         // Use $token in your API request
 *     }
 * }
 * ```
 */
trait BiscuitTestTrait
{
    private static ?KeyPair $testKeyPair = null;

    /**
     * Gets the test key pair, creating one if it doesn't exist.
     */
    protected static function getTestKeyPair(): KeyPair
    {
        if (null === self::$testKeyPair) {
            self::$testKeyPair = new KeyPair();
        }

        return self::$testKeyPair;
    }

    /**
     * Gets the public key from the test key pair.
     */
    protected static function getTestPublicKey(): PublicKey
    {
        return self::getTestKeyPair()->getPublicKey();
    }

    /**
     * Gets the private key from the test key pair.
     */
    protected static function getTestPrivateKey(): PrivateKey
    {
        return self::getTestKeyPair()->getPrivateKey();
    }

    /**
     * Creates a Biscuit token with the given code.
     *
     * @param string $code Biscuit datalog code for the token
     * @param array<string, mixed>|null $params Optional parameters for the code
     * @param array<string, mixed>|null $scopeParams Optional scope parameters
     */
    protected function createTestToken(
        string $code = 'user("test")',
        ?array $params = null,
        ?array $scopeParams = null,
    ): Biscuit {
        $builder = new BiscuitBuilder($code, $params, $scopeParams);

        return $builder->build(self::getTestPrivateKey());
    }

    /**
     * Creates a base64-encoded Biscuit token with the given code.
     *
     * @param string $code Biscuit datalog code for the token
     * @param array<string, mixed>|null $params Optional parameters for the code
     * @param array<string, mixed>|null $scopeParams Optional scope parameters
     */
    protected function createTestTokenBase64(
        string $code = 'user("test")',
        ?array $params = null,
        ?array $scopeParams = null,
    ): string {
        return $this->createTestToken($code, $params, $scopeParams)->toBase64();
    }

    /**
     * Resets the test key pair.
     *
     * Use this in tearDownAfterClass() or when you need a fresh key pair.
     */
    protected static function resetTestKeyPair(): void
    {
        self::$testKeyPair = null;
    }
}
