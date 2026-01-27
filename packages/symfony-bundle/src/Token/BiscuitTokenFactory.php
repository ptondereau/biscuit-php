<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;
use InvalidArgumentException;

final class BiscuitTokenFactory
{
    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
     */
    public function __construct(
        private readonly BiscuitTokenManagerInterface $tokenManager,
        private readonly array $templates = [],
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function create(string $template, array $params = []): Biscuit
    {
        if (!isset($this->templates[$template])) {
            throw new InvalidArgumentException(sprintf('Unknown token template: %s', $template));
        }

        $config = $this->templates[$template];
        $builder = $this->tokenManager->createBuilder();

        foreach ($config['facts'] ?? [] as $factCode) {
            $fact = new Fact($factCode);
            $this->bindParams($fact, $params, $factCode);
            $builder->addFact($fact);
        }

        foreach ($config['checks'] ?? [] as $checkCode) {
            $check = new Check($checkCode);
            $this->bindParams($check, $params, $checkCode);
            $builder->addCheck($check);
        }

        foreach ($config['rules'] ?? [] as $ruleCode) {
            $rule = new Rule($ruleCode);
            $this->bindParams($rule, $params, $ruleCode);
            $builder->addRule($rule);
        }

        return $this->tokenManager->build($builder);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function bindParams(Fact|Check|Rule $element, array $params, string $source): void
    {
        foreach ($params as $name => $value) {
            if ($this->hasPlaceholder($source, $name)) {
                $element->set($name, $value);
            }
        }
    }

    private function hasPlaceholder(string $source, string $name): bool
    {
        return str_contains($source, '{' . $name . '}');
    }

    public function hasTemplate(string $template): bool
    {
        return isset($this->templates[$template]);
    }

    /**
     * @return list<string>
     */
    public function getTemplateNames(): array
    {
        return array_keys($this->templates);
    }
}
