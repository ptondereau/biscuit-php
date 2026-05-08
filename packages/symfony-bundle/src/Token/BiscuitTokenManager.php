<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Event\BiscuitTokenAttenuatedEvent;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class BiscuitTokenManager implements BiscuitTokenManagerInterface
{
    public function __construct(
        private readonly KeyManager $keyManager,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<string, mixed>|null $scopeParams
     */
    public function createBuilder(?string $code = null, ?array $params = null, ?array $scopeParams = null): BiscuitBuilder
    {
        return new BiscuitBuilder($code, $params, $scopeParams);
    }

    public function build(BiscuitBuilder $builder): Biscuit
    {
        return $builder->build($this->keyManager->getPrivateKey());
    }

    public function serialize(Biscuit $biscuit): string
    {
        return $biscuit->toBase64();
    }

    public function parse(string $token): Biscuit
    {
        return Biscuit::fromBase64($token, $this->keyManager->getPublicKey());
    }

    public function attenuate(Biscuit $biscuit, BlockBuilder $block): Biscuit
    {
        $blockSource = (string) $block;
        $child = $biscuit->append($block);

        $this->dispatcher?->dispatch(new BiscuitTokenAttenuatedEvent($biscuit, $blockSource, $child));

        return $child;
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<string, mixed>|null $scopeParams
     */
    public function createBlockBuilder(?string $code = null, ?array $params = null, ?array $scopeParams = null): BlockBuilder
    {
        return new BlockBuilder($code, $params, $scopeParams);
    }
}
