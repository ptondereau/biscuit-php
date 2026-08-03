<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection\Compiler;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
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

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::CHECKER_ID)) {
            return;
        }

        $stores = $container->findTaggedServiceIds(self::STORE_TAG, true);

        if ([] === $stores) {
            throw new InvalidConfigurationException('biscuit.revocation.enabled is true but no revocation store is registered. Set biscuit.revocation.stores.static.ids, enable biscuit.revocation.stores.cache, or tag a service implementing ' . RevocationStoreInterface::class . ' with "' . self::STORE_TAG . '" (autoconfiguration adds the tag for you).');
        }

        $this->assertTaggedServicesImplement($container, $stores, self::STORE_TAG, RevocationStoreInterface::class);
        $this->assertTaggedServicesImplement(
            $container,
            $container->findTaggedServiceIds(self::WRITER_TAG, true),
            self::WRITER_TAG,
            RevocationWriterInterface::class,
        );
        $this->assertTaggedServicesImplement(
            $container,
            $container->findTaggedServiceIds(self::ENUMERABLE_TAG, true),
            self::ENUMERABLE_TAG,
            EnumerableRevocationStoreInterface::class,
        );
        $this->assertCachePoolExists($container);
        $this->assertPushBusExists($container);
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

    /**
     * @param array<string, array<array<string, mixed>>> $taggedServiceIds
     * @param class-string $contract
     */
    private function assertTaggedServicesImplement(
        ContainerBuilder $container,
        array $taggedServiceIds,
        string $tag,
        string $contract,
    ): void {
        foreach (array_keys($taggedServiceIds) as $id) {
            $class = $container->findDefinition($id)->getClass();

            if (null === $class) {
                continue;
            }

            $class = $container->getParameterBag()->resolveValue($class);

            if (!\is_string($class) || !class_exists($class)) {
                continue;
            }

            if (!is_a($class, $contract, true)) {
                throw new InvalidConfigurationException(sprintf('Service "%s" is tagged "%s" but "%s" does not implement "%s".', $id, $tag, $class, $contract));
            }
        }
    }
}
