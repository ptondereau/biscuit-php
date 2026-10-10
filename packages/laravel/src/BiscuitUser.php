<?php

declare(strict_types=1);

namespace Biscuit\Laravel;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\Policy;
use Biscuit\Exception\AuthorizationException;
use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\PolicyException;
use Biscuit\Exception\SnapshotException;
use Biscuit\Exception\TermException;
use Illuminate\Contracts\Auth\Authenticatable;

final class BiscuitUser implements Authenticatable
{
    public function __construct(
        public readonly Biscuit $biscuit,
        public readonly string $identifier,
    ) {}

    /**
     * @throws SnapshotException
     */
    public static function fromBiscuit(Biscuit $biscuit, string $userFact): self
    {
        $pattern = '/\b' . preg_quote($userFact, delimiter: '/') . '\((?:"([^"]+)"|(\d+))\)/';
        $matches = [];
        $found = preg_match($pattern, $biscuit->blockSource(0), $matches, \PREG_UNMATCHED_AS_NULL);
        $identifier = 1 === $found ? $matches[1] ?? $matches[2] ?? null : null;

        return new self($biscuit, $identifier ?? $biscuit->revocationIds()[0] ?? 'anonymous');
    }

    /**
     * @throws AuthorizerBuildException
     * @throws BuilderStateException
     * @throws PolicyException
     * @throws TermException
     */
    public function authorize(Policy $policy): bool
    {
        $builder = new AuthorizerBuilder();
        $builder->setTime();
        $builder->addPolicy($policy);

        try {
            $builder->build($this->biscuit)->authorize();
        } catch (AuthorizationException) {
            return false;
        }

        return true;
    }

    public function getAuthIdentifierName(): string
    {
        return 'identifier';
    }

    public function getAuthIdentifier(): string
    {
        return $this->identifier;
    }

    public function getAuthPasswordName(): string
    {
        return '';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }
}
