<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

use Biscuit\Auth\BlockBuilder;
use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;

final class BlockBuilderAdapter implements TemplatableBuilder
{
    public function __construct(
        private readonly BlockBuilder $builder,
    ) {
    }

    public function addFact(Fact $fact): void
    {
        $this->builder->addFact($fact);
    }

    public function addCheck(Check $check): void
    {
        $this->builder->addCheck($check);
    }

    public function addRule(Rule $rule): void
    {
        $this->builder->addRule($rule);
    }
}
