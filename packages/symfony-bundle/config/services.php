<?php

declare(strict_types=1);

use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\Command\AttenuateTokenCommand;
use Biscuit\BiscuitBundle\Command\CreateTokenCommand;
use Biscuit\BiscuitBundle\Command\GenerateKeysCommand;
use Biscuit\BiscuitBundle\Command\InspectTokenCommand;
use Biscuit\BiscuitBundle\Command\RevocationCheckCommand;
use Biscuit\BiscuitBundle\Command\RevocationListCommand;
use Biscuit\BiscuitBundle\Command\RevocationPurgeCommand;
use Biscuit\BiscuitBundle\Command\RevocationRevokeCommand;
use Biscuit\BiscuitBundle\Command\TestPolicyCommand;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Revocation\RevocationCheckerInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Security\Http\AuthenticationFailureResponseFactory;
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
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set('biscuit.key_manager', KeyManager::class)
        ->args([
            '%biscuit.keys.public_key%',
            '%biscuit.keys.private_key%',
            '%biscuit.keys.public_key_file%',
            '%biscuit.keys.private_key_file%',
            '%biscuit.keys.algorithm%',
        ]);

    $services->set('biscuit.token_manager', BiscuitTokenManager::class)
        ->args([
            service('biscuit.key_manager'),
            service('event_dispatcher')->nullOnInvalid(),
        ]);

    $services->set('biscuit.template_applier', Applier::class);

    $services->set('biscuit.authorizer_builder_factory', AuthorizerBuilderFactory::class)
        ->args([
            service('biscuit.template_applier'),
            '%biscuit.authorizer_fact_templates%',
        ]);

    $services->set('biscuit.token_factory', BiscuitTokenFactory::class)
        ->args([
            service('biscuit.token_manager'),
            service('biscuit.template_applier'),
            '%biscuit.token_templates%',
        ]);

    $services->set('biscuit.block_factory', BiscuitBlockFactory::class)
        ->args([
            service('biscuit.token_manager'),
            service('biscuit.template_applier'),
            '%biscuit.block_templates%',
        ]);

    $services->set('biscuit.policy_registry', PolicyRegistry::class)
        ->args([
            '%biscuit.policies%',
        ]);

    $services->set('biscuit.token_extractor.header', HeaderTokenExtractor::class);

    $services->set('biscuit.token_extractor.cookie', CookieTokenExtractor::class)
        ->args([
            '%biscuit.security.token_extractor.cookie%',
        ]);

    $services->set('biscuit.data_collector', BiscuitDataCollector::class)
        ->tag('data_collector', [
            'template' => '@Biscuit/data_collector/biscuit.html.twig',
            'id' => 'biscuit',
        ]);

    $services->set('biscuit.token_extractor', ChainTokenExtractor::class)
        ->args([
            service('biscuit.token_extractor.header'),
        ]);

    $services->set('biscuit.authentication_failure_response_factory', AuthenticationFailureResponseFactory::class)
        ->args([
            '%biscuit.security.www_authenticate%',
            '%biscuit.security.realm%',
        ]);

    $services->set('biscuit.authenticator', BiscuitAuthenticator::class)
        ->arg('$tokenExtractor', service('biscuit.token_extractor'))
        ->arg('$tokenManager', service('biscuit.token_manager'))
        ->arg('$revocationChecker', service(RevocationCheckerInterface::class)->nullOnInvalid())
        ->arg('$userIdentifierFact', '%biscuit.security.user_identifier_fact%')
        ->arg('$dataCollector', service('biscuit.data_collector')->nullOnInvalid())
        ->arg('$keyManager', service('biscuit.key_manager'))
        ->arg('$failureResponseFactory', service('biscuit.authentication_failure_response_factory'));

    $services->set('biscuit.voter', BiscuitVoter::class)
        ->args([
            service('biscuit.policy_registry'),
            service('biscuit.data_collector')->nullOnInvalid(),
            service('biscuit.authorizer_builder_factory'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('security.voter');

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
            service('biscuit.authorizer_builder_factory'),
        ])
        ->tag('console.command');

    $services->set(RevocationRevokeCommand::class)
        ->args([
            service(RevocationWriterInterface::class)->nullOnInvalid(),
            service(RevocationEntryFactory::class)->nullOnInvalid(),
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    $services->set(RevocationCheckCommand::class)
        ->args([
            service(RevocationCheckerInterface::class)->nullOnInvalid(),
            service('biscuit.token_manager'),
        ])
        ->tag('console.command');

    $services->set(RevocationListCommand::class)
        ->args([
            tagged_iterator('biscuit.revocation_enumerable_store', 'key'),
        ])
        ->tag('console.command');

    $services->set(RevocationPurgeCommand::class)
        ->args([
            service(RevocationWriterInterface::class)->nullOnInvalid(),
            tagged_iterator('biscuit.revocation_enumerable_store', 'key'),
        ])
        ->tag('console.command');

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
