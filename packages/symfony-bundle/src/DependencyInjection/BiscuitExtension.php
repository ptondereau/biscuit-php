<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class BiscuitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(\dirname(__DIR__, 2) . '/config'),
        );
        $loader->load('services.php');

        $this->setParameters($container, $config);
        $this->configureTokenExtractor($container, $config);
        $this->configureRevocation($container, $config, $loader);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureRevocation(
        ContainerBuilder $container,
        array $config,
        PhpFileLoader $loader,
    ): void {
        $revocation = $config['revocation'];

        if (false === $revocation['enabled']) {
            return;
        }

        if (null === $revocation['on_unavailable']) {
            throw new InvalidConfigurationException('biscuit.revocation.on_unavailable must be set explicitly when revocation is enabled. Use "deny" to reject requests when the revocation list cannot be read (fail closed), or "allow" to accept them and log an error (fail open). There is no default because the right answer depends on whether an unreachable list should take your API down.');
        }

        $loader->load('revocation.php');

        $this->configureStaticRevocationStore($container, $revocation['stores']['static']);
        $this->configureCacheRevocationStore($container, $revocation['stores']['cache']);

        if (false === $revocation['stores']['in_memory']['enabled']) {
            $container->removeDefinition('biscuit.revocation.store.in_memory');
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureStaticRevocationStore(ContainerBuilder $container, array $config): void
    {
        $ids = $config['ids'];
        $file = $config['file'];

        if ([] === $ids && null === $file) {
            $container->removeDefinition('biscuit.revocation.store.static');

            return;
        }

        $container->setParameter('biscuit.revocation.stores.static.ids', $ids);
        $container->setParameter('biscuit.revocation.stores.static.file', $file);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureCacheRevocationStore(ContainerBuilder $container, array $config): void
    {
        if (false === $config['enabled']) {
            $container->removeDefinition('biscuit.revocation.store.cache');

            return;
        }

        $poolId = $config['pool'];

        if (null === $poolId) {
            $poolId = 'cache.biscuit.revocation';

            $container->setDefinition($poolId, new ChildDefinition($config['adapter']))
                ->addTag('cache.pool', ['name' => 'biscuit.revocation']);
        }

        $container->getDefinition('biscuit.revocation.store.cache')
            ->setArgument('$cachePool', new Reference($poolId))
            ->setArgument('$keyPrefix', $config['key_prefix']);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureTokenExtractor(
        ContainerBuilder $container,
        array $config,
    ): void {
        $extractorConfig = $config['security']['token_extractor'];
        $extractors = [];

        if ($extractorConfig['header']) {
            $extractors[] = new Reference('biscuit.token_extractor.header');
        }

        if (false !== $extractorConfig['cookie']) {
            $extractors[] = new Reference('biscuit.token_extractor.cookie');
        }

        $chainDefinition = $container->getDefinition('biscuit.token_extractor');
        $chainDefinition->setArguments($extractors);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setParameters(
        ContainerBuilder $container,
        array $config,
    ): void {
        $container->setParameter(
            'biscuit.keys.public_key',
            $config['keys']['public_key'],
        );
        $container->setParameter(
            'biscuit.keys.private_key',
            $config['keys']['private_key'],
        );
        $container->setParameter(
            'biscuit.keys.public_key_file',
            $config['keys']['public_key_file'],
        );
        $container->setParameter(
            'biscuit.keys.private_key_file',
            $config['keys']['private_key_file'],
        );
        $container->setParameter(
            'biscuit.keys.algorithm',
            $config['keys']['algorithm'],
        );

        $container->setParameter(
            'biscuit.security.token_extractor.header',
            $config['security']['token_extractor']['header'],
        );
        $container->setParameter(
            'biscuit.security.token_extractor.cookie',
            $config['security']['token_extractor']['cookie'],
        );
        $container->setParameter(
            'biscuit.security.user_identifier_fact',
            $config['security']['user_identifier_fact'],
        );
        $container->setParameter(
            'biscuit.security.www_authenticate',
            $config['security']['www_authenticate'],
        );
        $container->setParameter(
            'biscuit.security.realm',
            $config['security']['realm'],
        );

        $container->setParameter(
            'biscuit.revocation.enabled',
            $config['revocation']['enabled'],
        );
        $container->setParameter(
            'biscuit.revocation.on_unavailable',
            $config['revocation']['on_unavailable'],
        );
        $container->setParameter(
            'biscuit.revocation.dispatch_check_events',
            $config['revocation']['dispatch_check_events'],
        );
        $container->setParameter(
            'biscuit.revocation.default_expiry',
            $config['revocation']['default_expiry'],
        );
        $container->setParameter(
            'biscuit.revocation.stores.cache.key_prefix',
            $config['revocation']['stores']['cache']['key_prefix'],
        );

        $container->setParameter('biscuit.policies', $config['policies']);

        $container->setParameter(
            'biscuit.token_templates',
            $config['token_templates'],
        );

        $container->setParameter(
            'biscuit.block_templates',
            $config['block_templates'],
        );

        $container->setParameter(
            'biscuit.authorizer_fact_templates',
            $config['authorizer_fact_templates'],
        );
    }
}
