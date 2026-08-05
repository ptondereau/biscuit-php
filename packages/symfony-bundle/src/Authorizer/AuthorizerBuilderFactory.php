<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Authorizer;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Biscuit\BiscuitBundle\Token\Template\AuthorizerBuilderAdapter;
use Biscuit\BiscuitBundle\Token\Template\Template;

/**
 * Single place where an authorizer is set up, so `BiscuitVoter` and
 * `biscuit:policy:test` evaluate a policy against the same facts.
 */
final class AuthorizerBuilderFactory
{
    /**
     * @param array<string, array{facts?: list<non-empty-string>, checks?: list<non-empty-string>, rules?: list<non-empty-string>}> $authorizerFactTemplates
     */
    public function __construct(
        private readonly ?Applier $applier = null,
        private readonly array $authorizerFactTemplates = [],
    ) {
    }

    /**
     * The current time lets token checks reason about expiry. It can only make
     * those checks stricter, so it grants nothing on its own.
     *
     * Facts from the template named after the policy carry request context the
     * token itself cannot know (amount, geo, wallet tier, ...). An inline policy
     * matches no template name and gets the time fact alone.
     *
     * @param array<string, mixed> $params
     */
    public function create(string $policyName, array $params = []): AuthorizerBuilder
    {
        $builder = new AuthorizerBuilder();
        $builder->setTime();

        if (null === $this->applier || !isset($this->authorizerFactTemplates[$policyName])) {
            return $builder;
        }

        $this->applier->populate(
            new AuthorizerBuilderAdapter($builder),
            Template::fromArray($this->authorizerFactTemplates[$policyName]),
            $params,
        );

        return $builder;
    }
}
