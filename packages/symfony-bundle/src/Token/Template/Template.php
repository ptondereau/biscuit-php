<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

final class Template
{
    /**
     * @param list<string> $facts
     * @param list<string> $checks
     * @param list<string> $rules
     */
    public function __construct(
        public readonly array $facts = [],
        public readonly array $checks = [],
        public readonly array $rules = [],
    ) {
    }

    /**
     * @param array{facts?: list<string>, checks?: list<string>, rules?: list<string>} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['facts'] ?? [],
            $config['checks'] ?? [],
            $config['rules'] ?? [],
        );
    }
}
