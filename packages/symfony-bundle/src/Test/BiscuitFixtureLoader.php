<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Test;

use Biscuit\Auth\Biscuit;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads Biscuit token fixtures from YAML files.
 *
 * This class allows you to define test tokens in YAML format for easier
 * maintenance and readability of your test fixtures.
 *
 * @example
 * ```yaml
 * # fixtures/biscuit_tokens.yaml
 * tokens:
 *   admin_token:
 *     code: |
 *       user("admin");
 *       role("admin");
 *       right("read");
 *       right("write");
 *   read_only_token:
 *     code: |
 *       user("reader");
 *       right("read");
 *     params:
 *       user_id: 123
 * ```
 * @example
 * ```php
 * $loader = new BiscuitFixtureLoader();
 * $fixtures = $loader->load('/path/to/fixtures/biscuit_tokens.yaml');
 * $adminToken = $fixtures->getToken('admin_token');
 * ```
 */
final class BiscuitFixtureLoader
{
    use BiscuitTestTrait;

    /**
     * Loads fixtures from a YAML file.
     *
     * @throws RuntimeException If the file cannot be read or parsed
     */
    public function load(string $filePath): BiscuitFixtures
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('Fixture file not found: %s', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Fixture file is not readable: %s', $filePath));
        }

        $content = file_get_contents($filePath);

        if (false === $content) {
            throw new RuntimeException(sprintf('Failed to read fixture file: %s', $filePath));
        }

        if (!class_exists(Yaml::class)) {
            throw new RuntimeException('Symfony YAML component is required for fixture loading. Install with: composer require symfony/yaml');
        }

        /** @var array<string, mixed> $data */
        $data = Yaml::parse($content);

        return $this->createFixturesFromData($data);
    }

    /**
     * Creates fixtures from a parsed YAML array.
     *
     * @param array<string, mixed> $data The parsed YAML data
     *
     * @throws RuntimeException If the YAML structure is invalid
     */
    public function loadFromArray(array $data): BiscuitFixtures
    {
        return $this->createFixturesFromData($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createFixturesFromData(array $data): BiscuitFixtures
    {
        if (!isset($data['tokens']) || !is_array($data['tokens'])) {
            throw new RuntimeException('Fixture file must contain a "tokens" key with an array of token definitions');
        }

        $tokens = [];

        /** @var array<string, array{code?: string, params?: array<string, mixed>, scope_params?: array<string, mixed>}> $tokenDefinitions */
        $tokenDefinitions = $data['tokens'];

        foreach ($tokenDefinitions as $name => $definition) {
            if (!is_array($definition)) {
                throw new RuntimeException(sprintf('Token definition "%s" must be an array', $name));
            }

            $code = $definition['code'] ?? 'user("test")';
            $params = $definition['params'] ?? null;
            $scopeParams = $definition['scope_params'] ?? null;

            if (!is_string($code)) {
                throw new RuntimeException(sprintf('Token definition "%s" must have a string "code" field', $name));
            }

            $tokens[$name] = $this->createTestToken($code, $params, $scopeParams);
        }

        return new BiscuitFixtures($tokens, self::getTestPublicKey());
    }
}
