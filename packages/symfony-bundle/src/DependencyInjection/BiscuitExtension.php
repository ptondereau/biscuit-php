<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
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
        // Keys parameters
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

        // Security parameters
        $container->setParameter(
            'biscuit.security.token_extractor.header',
            $config['security']['token_extractor']['header'],
        );
        $container->setParameter(
            'biscuit.security.token_extractor.cookie',
            $config['security']['token_extractor']['cookie'],
        );

        // Cache parameters
        $container->setParameter(
            'biscuit.cache.enabled',
            $config['cache']['enabled'],
        );
        $container->setParameter(
            'biscuit.cache.pool',
            $config['cache']['pool'],
        );
        $container->setParameter('biscuit.cache.ttl', $config['cache']['ttl']);

        // Revocation parameters
        $container->setParameter(
            'biscuit.revocation.enabled',
            $config['revocation']['enabled'],
        );
        $container->setParameter(
            'biscuit.revocation.service',
            $config['revocation']['service'],
        );

        // Policies
        $container->setParameter('biscuit.policies', $config['policies']);

        // Token templates
        $container->setParameter(
            'biscuit.token_templates',
            $config['token_templates'],
        );
    }
}
