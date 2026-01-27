<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Test;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\PublicKey;
use RuntimeException;

/**
 * Container for loaded Biscuit test fixtures.
 *
 * This class holds a collection of named Biscuit tokens loaded from fixtures
 * and provides easy access to them by name.
 */
final class BiscuitFixtures
{
    /**
     * @param array<string, Biscuit> $tokens Map of token names to Biscuit instances
     * @param PublicKey $publicKey The public key used to create the tokens
     */
    public function __construct(
        private readonly array $tokens,
        private readonly PublicKey $publicKey,
    ) {
    }

    /**
     * Gets a token by name.
     *
     * @throws RuntimeException If the token is not found
     */
    public function getToken(string $name): Biscuit
    {
        if (!isset($this->tokens[$name])) {
            throw new RuntimeException(sprintf('Token "%s" not found in fixtures. Available tokens: %s', $name, implode(', ', array_keys($this->tokens))));
        }

        return $this->tokens[$name];
    }

    /**
     * Gets a token as base64 by name.
     *
     * @throws RuntimeException If the token is not found
     */
    public function getTokenBase64(string $name): string
    {
        return $this->getToken($name)->toBase64();
    }

    /**
     * Checks if a token with the given name exists.
     */
    public function hasToken(string $name): bool
    {
        return isset($this->tokens[$name]);
    }

    /**
     * Gets all token names.
     *
     * @return list<string>
     */
    public function getTokenNames(): array
    {
        return array_keys($this->tokens);
    }

    /**
     * Gets all tokens.
     *
     * @return array<string, Biscuit>
     */
    public function getAllTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Gets the public key used to create the tokens.
     */
    public function getPublicKey(): PublicKey
    {
        return $this->publicKey;
    }

    /**
     * Gets the count of tokens in this fixture collection.
     */
    public function count(): int
    {
        return count($this->tokens);
    }
}
