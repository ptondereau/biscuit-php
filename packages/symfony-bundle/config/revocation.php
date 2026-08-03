<?php

declare(strict_types=1);

use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationCheckerInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\CacheRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use Biscuit\BiscuitBundle\Revocation\Store\StaticRevocationStore;
use Biscuit\BiscuitBundle\Token\Datalog\AuthorityBlockReader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('biscuit.revocation.authority_block_reader', AuthorityBlockReader::class)
        ->autoconfigure(false);

    $services->set('biscuit.revocation.store.static', StaticRevocationStore::class)
        ->autoconfigure(false)
        ->arg('$revocationIds', '%biscuit.revocation.stores.static.ids%')
        ->arg('$file', '%biscuit.revocation.stores.static.file%')
        ->tag('biscuit.revocation_store', ['priority' => 256, 'key' => 'static'])
        ->tag('biscuit.revocation_enumerable_store', ['key' => 'static']);

    $services->set('biscuit.revocation.store.cache', CacheRevocationStore::class)
        ->autoconfigure(false)
        ->arg('$cachePool', service('cache.app'))
        ->arg('$keyPrefix', '%biscuit.revocation.stores.cache.key_prefix%')
        ->arg('$defaultTtl', '%biscuit.revocation.default_expiry%')
        ->tag('biscuit.revocation_store', ['priority' => 128, 'key' => 'cache'])
        ->tag('biscuit.revocation_writer', ['key' => 'cache']);

    $services->set('biscuit.revocation.store.in_memory', ArrayRevocationStore::class)
        ->autoconfigure(false)
        ->arg('$entries', [])
        ->tag('biscuit.revocation_store', ['priority' => 192, 'key' => 'in_memory'])
        ->tag('biscuit.revocation_writer', ['key' => 'in_memory'])
        ->tag('biscuit.revocation_enumerable_store', ['key' => 'in_memory']);

    $services->set('biscuit.revocation.checker', RevocationChecker::class)
        ->arg('$stores', tagged_iterator('biscuit.revocation_store', 'key'))
        ->arg('$failurePolicy', '%biscuit.revocation.on_unavailable%')
        ->arg('$eventPolicy', '%biscuit.revocation.dispatch_check_events%')
        ->arg('$eventDispatcher', service('event_dispatcher')->nullOnInvalid())
        ->arg('$logger', service('logger')->nullOnInvalid())
        ->tag('monolog.logger', ['channel' => 'biscuit']);

    $services->set('biscuit.revocation.writer', ChainRevocationWriter::class)
        ->arg('$writers', tagged_iterator('biscuit.revocation_writer', 'key'))
        ->arg('$eventDispatcher', service('event_dispatcher')->nullOnInvalid());

    $services->set('biscuit.revocation.entry_factory', RevocationEntryFactory::class)
        ->arg('$authorityBlockReader', service('biscuit.revocation.authority_block_reader'))
        ->arg('$userIdentifierFact', '%biscuit.security.user_identifier_fact%');

    $services->alias(RevocationCheckerInterface::class, 'biscuit.revocation.checker')
        ->public();

    $services->alias(RevocationWriterInterface::class, 'biscuit.revocation.writer')
        ->public();

    $services->alias(RevocationEntryFactory::class, 'biscuit.revocation.entry_factory')
        ->public();
};
