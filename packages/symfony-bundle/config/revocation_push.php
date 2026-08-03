<?php

declare(strict_types=1);

use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevocationPushHandler;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use Biscuit\BiscuitBundle\Revocation\Store\PublishingRevocationWriter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('biscuit.revocation.writer.local', ChainRevocationWriter::class)
        ->arg('$writers', tagged_iterator('biscuit.revocation_writer', 'key'))
        ->arg('$eventDispatcher', null);

    $services->set('biscuit.revocation.publisher', PublishingRevocationWriter::class)
        ->arg('$writer', service('biscuit.revocation.writer'))
        ->arg('$bus', service('messenger.default_bus'));

    $services->set('biscuit.revocation.push_handler', RevocationPushHandler::class)
        ->arg('$writer', service('biscuit.revocation.writer.local'))
        ->arg('$eventDispatcher', service('event_dispatcher')->nullOnInvalid())
        ->arg('$logger', service('logger')->nullOnInvalid())
        ->tag('monolog.logger', ['channel' => 'biscuit'])
        ->tag('messenger.message_handler', [
            'bus' => 'messenger.default_bus',
            'handles' => RevokeToken::class,
            'method' => 'handleRevoke',
        ])
        ->tag('messenger.message_handler', [
            'bus' => 'messenger.default_bus',
            'handles' => UnrevokeToken::class,
            'method' => 'handleUnrevoke',
        ])
        ->tag('messenger.message_handler', [
            'bus' => 'messenger.default_bus',
            'handles' => PurgeExpiredRevocations::class,
            'method' => 'handlePurge',
        ]);

    $services->alias(RevocationWriterInterface::class, 'biscuit.revocation.publisher')
        ->public();

    $services->alias(RevocationPushHandler::class, 'biscuit.revocation.push_handler')
        ->public();
};
