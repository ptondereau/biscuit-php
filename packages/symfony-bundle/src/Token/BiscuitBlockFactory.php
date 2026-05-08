<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Biscuit\BiscuitBundle\Token\Template\BlockBuilderAdapter;
use Biscuit\BiscuitBundle\Token\Template\Template;
use InvalidArgumentException;

final class BiscuitBlockFactory
{
    /**
     * @param array<string, array{facts?: list<string>, checks?: list<string>, rules?: list<string>}> $templates
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
    public function attenuate(Biscuit $biscuit, string $template, array $params = []): Biscuit
    {
        return $this->tokenManager->attenuate($biscuit, $this->buildBlock($template, $params));
    }

    /**
     * @param array<string, mixed> $params
     */
    public function buildBlock(string $template, array $params = []): BlockBuilder
    {
        if (!isset($this->templates[$template])) {
            throw new InvalidArgumentException(sprintf('Unknown block template: %s', $template));
        }

        $block = $this->tokenManager->createBlockBuilder();
        $this->applier->populate(
            new BlockBuilderAdapter($block),
            Template::fromArray($this->templates[$template]),
            $params,
        );

        return $block;
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
