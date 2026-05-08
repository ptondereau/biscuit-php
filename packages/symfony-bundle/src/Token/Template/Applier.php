<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Template;

use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Rule;

final class Applier
{
    /**
     * @param array<string, mixed> $params
     */
    public function populate(TemplatableBuilder $builder, Template $template, array $params = []): void
    {
        foreach ($template->facts as $factCode) {
            $fact = new Fact($factCode);
            $this->bind($fact, $params, $factCode);
            $builder->addFact($fact);
        }

        foreach ($template->checks as $checkCode) {
            $check = new Check($checkCode);
            $this->bind($check, $params, $checkCode);
            $builder->addCheck($check);
        }

        foreach ($template->rules as $ruleCode) {
            $rule = new Rule($ruleCode);
            $this->bind($rule, $params, $ruleCode);
            $builder->addRule($rule);
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function bind(Fact|Check|Rule $element, array $params, string $source): void
    {
        foreach ($params as $name => $value) {
            if (str_contains($source, '{' . $name . '}')) {
                $element->set($name, $value);
            }
        }
    }
}
