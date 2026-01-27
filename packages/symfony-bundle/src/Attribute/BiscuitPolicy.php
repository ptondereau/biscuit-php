<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class BiscuitPolicy
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        public readonly string $policy,
        public readonly array $params = [],
    ) {
    }
}
