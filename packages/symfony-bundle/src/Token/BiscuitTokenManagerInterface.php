<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;

interface BiscuitTokenManagerInterface
{
    /**
     * @param array<string, mixed>|null $params
     * @param array<string, mixed>|null $scopeParams
     */
    public function createBuilder(?string $code = null, ?array $params = null, ?array $scopeParams = null): BiscuitBuilder;

    public function build(BiscuitBuilder $builder): Biscuit;

    public function serialize(Biscuit $biscuit): string;

    public function parse(string $token): Biscuit;

    public function attenuate(Biscuit $biscuit, BlockBuilder $block): Biscuit;

    /**
     * @param array<string, mixed>|null $params
     * @param array<string, mixed>|null $scopeParams
     */
    public function createBlockBuilder(?string $code = null, ?array $params = null, ?array $scopeParams = null): BlockBuilder;
}
