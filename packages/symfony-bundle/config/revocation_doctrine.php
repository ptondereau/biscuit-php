<?php

declare(strict_types=1);

use Biscuit\BiscuitBundle\Command\RevocationDoctrineSetupCommand;
use Biscuit\BiscuitBundle\Revocation\Doctrine\RevocationSchemaListener;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('biscuit.revocation.store.doctrine', DoctrineRevocationStore::class)
        ->autoconfigure(false)
        ->arg('$connection', service('doctrine.dbal.default_connection'))
        ->arg('$table', '%biscuit.revocation.stores.doctrine.table%')
        ->tag('biscuit.revocation_store', ['priority' => 64, 'key' => 'doctrine'])
        ->tag('biscuit.revocation_writer', ['key' => 'doctrine'])
        ->tag('biscuit.revocation_enumerable_store', ['key' => 'doctrine']);

    $services->set('biscuit.revocation.doctrine.setup_command', RevocationDoctrineSetupCommand::class)
        ->arg('$store', service('biscuit.revocation.store.doctrine'))
        ->arg('$connection', service('doctrine.dbal.default_connection'))
        ->tag('console.command');

    $services->set('biscuit.revocation.doctrine.schema_listener', RevocationSchemaListener::class)
        ->arg('$store', service('biscuit.revocation.store.doctrine'))
        ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema']);
};
