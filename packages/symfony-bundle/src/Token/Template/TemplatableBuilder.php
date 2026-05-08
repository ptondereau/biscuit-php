<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;

interface TemplatableBuilder
{
    public function addFact(Fact $fact): void;

    public function addCheck(Check $check): void;

    public function addRule(Rule $rule): void;
}
