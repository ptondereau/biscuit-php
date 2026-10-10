<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection\Compiler;

use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterRevocationStoresPass implements CompilerPassInterface
{
    public const STORE_TAG = 'biscuit.revocation_store';

    public const WRITER_TAG = 'biscuit.revocation_writer';

    public const ENUMERABLE_TAG = 'biscuit.revocation_enumerable_store';

    private const CHECKER_ID = 'biscuit.revocation.checker';

    private const CACHE_STORE_ID = 'biscuit.revocation.store.cache';

    private const PUSH_HANDLER_ID = 'biscuit.revocation.push_handler';

    private const DOCTRINE_STORE_ID = 'biscuit.revocation.store.doctrine';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::CHECKER_ID)) {
            return;
        }

        $stores = $container->findTaggedServiceIds(self::STORE_TAG, true);

        if ([] === $stores) {
            throw new InvalidConfigurationException('biscuit.revocation.enabled is true but no revocation store is registered. Set biscuit.revocation.stores.static.ids, enable biscuit.revocation.stores.cache, or tag a service implementing ' . RevocationStoreInterface::class . ' with "' . self::STORE_TAG . '" (autoconfiguration adds the tag for you).');
        }

        $this->assertCachePoolExists($container);
        $this->assertPushBusExists($container);
        $this->assertDoctrineConnectionExists($container);
    }

    private function assertDoctrineConnectionExists(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::DOCTRINE_STORE_ID)) {
            return;
        }

        /** @var string $connection */
        $connection = $container->getParameter('biscuit.revocation.stores.doctrine.connection');

        if ($container->has($connection)) {
            return;
        }

        throw new InvalidConfigurationException(sprintf('biscuit.revocation.stores.doctrine is enabled but the DBAL connection "%s" does not exist. Install and configure doctrine/doctrine-bundle, or set biscuit.revocation.stores.doctrine.connection to a connection your application defines. Having doctrine/dbal installed is not enough on its own.', $connection));
    }

    private function assertPushBusExists(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::PUSH_HANDLER_ID)) {
            return;
        }

        /** @var string $bus */
        $bus = $container->getParameter('biscuit.revocation.push.bus');

        if ($container->has($bus)) {
            return;
        }

        throw new InvalidConfigurationException(sprintf('biscuit.revocation.push is enabled but the message bus "%s" does not exist. Configure framework.messenger so the default bus is registered, or set biscuit.revocation.push.bus to a bus your application defines.', $bus));
    }

    private function assertCachePoolExists(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::CACHE_STORE_ID)) {
            return;
        }

        $pool = $container->getDefinition(self::CACHE_STORE_ID)->getArgument('$cachePool');

        if (!$pool instanceof Reference) {
            return;
        }

        $missing = $this->firstMissingPoolId($container, (string) $pool);

        if (null === $missing) {
            return;
        }

        throw new InvalidConfigurationException(sprintf('biscuit.revocation.stores.cache is enabled but the cache pool "%s" does not exist. Point biscuit.revocation.stores.cache.pool at a pool your application defines, or set biscuit.revocation.stores.cache.adapter to an adapter that exists.', $missing));
    }

    private function firstMissingPoolId(ContainerBuilder $container, string $poolId): ?string
    {
        $seen = [];

        while (!isset($seen[$poolId])) {
            $seen[$poolId] = true;

            if (!$container->has($poolId)) {
                return $poolId;
            }

            $definition = $container->hasDefinition($poolId) ? $container->getDefinition($poolId) : null;

            if (!$definition instanceof ChildDefinition) {
                return null;
            }

            $poolId = $definition->getParent();
        }

        return null;
    }
}
