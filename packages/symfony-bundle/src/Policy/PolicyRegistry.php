<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Policy;

use Biscuit\Auth\Policy;
use InvalidArgumentException;

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

        if ('' === $policyString) {
            throw new InvalidArgumentException(sprintf('Policy "%s" resolves to an empty policy string.', $name));
        }

        return new Policy($policyString, $this->usedParams($policyString, $params));
    }

    /**
     * Biscuit rejects a policy built with parameters it does not reference. Keep
     * only the placeholders the policy string actually uses so a caller may pass
     * a wider context (e.g. one shared with an authorizer fact template) without
     * every value needing to appear in the policy.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function usedParams(string $policyString, array $params): array
    {
        $used = [];
        foreach ($params as $name => $value) {
            if (str_contains($policyString, '{' . $name . '}')) {
                $used[$name] = $value;
            }
        }

        return $used;
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
