<?php

declare(strict_types=1);

use Biscuit\BiscuitBundle\Command\AttenuateTokenCommand;
use Biscuit\BiscuitBundle\Command\CreateTokenCommand;
use Biscuit\BiscuitBundle\Command\GenerateKeysCommand;
use Biscuit\BiscuitBundle\Command\InspectTokenCommand;
use Biscuit\BiscuitBundle\Command\TestPolicyCommand;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Security\Voter\BiscuitVoter;
use Biscuit\BiscuitBundle\Token\BiscuitBlockFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Biscuit\BiscuitBundle\Token\Extractor\ChainTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\CookieTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\HeaderTokenExtractor;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    // Key Manager
    $services->set('biscuit.key_manager', KeyManager::class)
        ->args([
            '%biscuit.keys.public_key%',
            '%biscuit.keys.private_key%',
            '%biscuit.keys.public_key_file%',
            '%biscuit.keys.private_key_file%',
            '%biscuit.keys.algorithm%',
        ]);

    // Token Manager
    $services->set('biscuit.token_manager', BiscuitTokenManager::class)
        ->args([
            service('biscuit.key_manager'),
            service('event_dispatcher')->nullOnInvalid(),
        ]);

    // Template Applier (shared by both factories)
    $services->set('biscuit.template_applier', Applier::class);

    // Token Factory
    $services->set('biscuit.token_factory', BiscuitTokenFactory::class)
        ->args([
            service('biscuit.token_manager'),
            service('biscuit.template_applier'),
            '%biscuit.token_templates%',
        ]);

    // Block Factory (attenuation)
    $services->set('biscuit.block_factory', BiscuitBlockFactory::class)
        ->args([
            service('biscuit.token_manager'),
            service('biscuit.template_applier'),
            '%biscuit.block_templates%',
        ]);

    // Policy Registry
    $services->set('biscuit.policy_registry', PolicyRegistry::class)
        ->args([
            '%biscuit.policies%',
        ]);

    // Token Extractors
    $services->set('biscuit.token_extractor.header', HeaderTokenExtractor::class);

    $services->set('biscuit.token_extractor.cookie', CookieTokenExtractor::class)
        ->args([
            '%biscuit.security.token_extractor.cookie%',
        ]);

    // Data Collector (for Web Profiler)
    $services->set('biscuit.data_collector', BiscuitDataCollector::class)
        ->tag('data_collector', [
            'template' => '@Biscuit/data_collector/biscuit.html.twig',
            'id' => 'biscuit',
        ]);

    $services->set('biscuit.token_extractor', ChainTokenExtractor::class)
        ->args([
            service('biscuit.token_extractor.header'),
        ]);

    // Security
    $services->set('biscuit.authenticator', BiscuitAuthenticator::class)
        ->args([
            service('biscuit.token_extractor'),
            service('biscuit.token_manager'),
            null,
            'user',
            service('biscuit.data_collector')->nullOnInvalid(),
            service('biscuit.key_manager'),
        ]);

    $services->set('biscuit.voter', BiscuitVoter::class)
        ->args([
            service('biscuit.policy_registry'),
            service('biscuit.data_collector')->nullOnInvalid(),
        ])
        ->tag('security.voter');

    // Commands
    $services->set(GenerateKeysCommand::class)
        ->tag('console.command');

    $services->set(CreateTokenCommand::class)
        ->args([
            service('biscuit.token_factory'),
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    $services->set(InspectTokenCommand::class)
        ->args([
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    $services->set(AttenuateTokenCommand::class)
        ->args([
            service('biscuit.block_factory'),
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    $services->set(TestPolicyCommand::class)
        ->args([
            service('biscuit.policy_registry'),
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    // Aliases
    $services->alias(KeyManager::class, 'biscuit.key_manager')
        ->public();

    $services->alias(BiscuitTokenManager::class, 'biscuit.token_manager')
        ->public();

    $services->alias(BiscuitTokenManagerInterface::class, 'biscuit.token_manager')
        ->public();

    $services->alias(BiscuitTokenFactory::class, 'biscuit.token_factory')
        ->public();

    $services->alias(BiscuitBlockFactory::class, 'biscuit.block_factory')
        ->public();

    $services->alias(PolicyRegistry::class, 'biscuit.policy_registry')
        ->public();

    $services->alias(TokenExtractorInterface::class, 'biscuit.token_extractor')
        ->public();

    $services->alias(BiscuitAuthenticator::class, 'biscuit.authenticator')
        ->public();

    $services->alias(BiscuitVoter::class, 'biscuit.voter')
        ->public();

    $services->alias(BiscuitDataCollector::class, 'biscuit.data_collector')
        ->public();
};
