<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Policy;

use Biscuit\Auth\Policy;

final class PolicyRegistry
{
    /** @var array<string, string> */
    private array $policies;

    /**
     * @param array<string, string> $policies
     */
    public function __construct(array $policies = [])
    {
        $this->policies = $policies;
    }

    public function has(string $name): bool
    {
        return isset($this->policies[$name]) || $this->isInlinePolicy($name);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function get(string $name, array $params = []): Policy
    {
        $policyString = $this->policies[$name] ?? $name;

        return new Policy($policyString, $params);
    }

    public function add(string $name, string $policy): void
    {
        $this->policies[$name] = $policy;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->policies;
    }

    private function isInlinePolicy(string $name): bool
    {
        return str_starts_with($name, 'allow ') || str_starts_with($name, 'deny ');
    }
}
