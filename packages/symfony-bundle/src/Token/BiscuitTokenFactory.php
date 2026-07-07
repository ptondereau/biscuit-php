<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Biscuit\BiscuitBundle\Token\Template\BiscuitBuilderAdapter;
use Biscuit\BiscuitBundle\Token\Template\Template;
use InvalidArgumentException;

final class BiscuitTokenFactory
{
    /**
     * @param array<string, array{facts?: list<non-empty-string>, checks?: list<non-empty-string>, rules?: list<non-empty-string>}> $templates
     */
    public function __construct(
        private readonly BiscuitTokenManagerInterface $tokenManager,
        private readonly Applier $applier,
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

        $builder = $this->tokenManager->createBuilder();
        $this->applier->populate(
            new BiscuitBuilderAdapter($builder),
            Template::fromArray($this->templates[$template]),
            $params,
        );

        return $this->tokenManager->build($builder);
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
