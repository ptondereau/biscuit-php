<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class BiscuitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.php');

        $this->setParameters($container, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setParameters(ContainerBuilder $container, array $config): void
    {
        // Keys parameters
        $container->setParameter('biscuit.keys.public_key', $config['keys']['public_key']);
        $container->setParameter('biscuit.keys.private_key', $config['keys']['private_key']);
        $container->setParameter('biscuit.keys.public_key_file', $config['keys']['public_key_file']);
        $container->setParameter('biscuit.keys.private_key_file', $config['keys']['private_key_file']);
        $container->setParameter('biscuit.keys.algorithm', $config['keys']['algorithm']);

        // Security parameters
        $container->setParameter('biscuit.security.token_extractor.header', $config['security']['token_extractor']['header']);
        $container->setParameter('biscuit.security.token_extractor.cookie', $config['security']['token_extractor']['cookie']);

        // Cache parameters
        $container->setParameter('biscuit.cache.enabled', $config['cache']['enabled']);
        $container->setParameter('biscuit.cache.pool', $config['cache']['pool']);
        $container->setParameter('biscuit.cache.ttl', $config['cache']['ttl']);

        // Revocation parameters
        $container->setParameter('biscuit.revocation.enabled', $config['revocation']['enabled']);
        $container->setParameter('biscuit.revocation.service', $config['revocation']['service']);

        // Policies
        $container->setParameter('biscuit.policies', $config['policies']);

        // Token templates
        $container->setParameter('biscuit.token_templates', $config['token_templates']);
    }
}
