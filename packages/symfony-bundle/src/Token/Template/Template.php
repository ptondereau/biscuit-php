<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

final class Template
{
    /**
     * @param list<non-empty-string> $facts
     * @param list<non-empty-string> $checks
     * @param list<non-empty-string> $rules
     */
    public function __construct(
        public readonly array $facts = [],
        public readonly array $checks = [],
        public readonly array $rules = [],
    ) {
    }

    /**
     * @param array{facts?: list<non-empty-string>, checks?: list<non-empty-string>, rules?: list<non-empty-string>} $config
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
