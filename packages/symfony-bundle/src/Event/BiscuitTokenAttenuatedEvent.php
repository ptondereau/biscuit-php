<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Event;

use Biscuit\Auth\Biscuit;

final class BiscuitTokenAttenuatedEvent
{
    public function __construct(
        public readonly Biscuit $parent,
        public readonly string $blockSource,
        public readonly Biscuit $child,
    ) {
    }
}
