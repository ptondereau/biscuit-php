<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ResolveRevocationPushBusPass implements CompilerPassInterface
{
    public const PRIORITY = 16;

    private const HANDLER_ID = 'biscuit.revocation.push_handler';

    private const HANDLER_TAG = 'messenger.message_handler';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::HANDLER_ID)) {
            return;
        }

        $definition = $container->getDefinition(self::HANDLER_ID);
        $tags = $definition->getTag(self::HANDLER_TAG);

        $definition->clearTag(self::HANDLER_TAG);

        foreach ($tags as $tag) {
            $tag['bus'] = $this->resolveBusId($container, (string) $tag['bus']);
            $definition->addTag(self::HANDLER_TAG, $tag);
        }
    }

    private function resolveBusId(ContainerBuilder $container, string $bus): string
    {
        $seen = [];

        while (!isset($seen[$bus]) && $container->hasAlias($bus)) {
            $seen[$bus] = true;
            $bus = (string) $container->getAlias($bus);
        }

        return $bus;
    }
}
