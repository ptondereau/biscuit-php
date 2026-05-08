<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;

final class BiscuitBuilderAdapter implements TemplatableBuilder
{
    public function __construct(
        private readonly BiscuitBuilder $builder,
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
