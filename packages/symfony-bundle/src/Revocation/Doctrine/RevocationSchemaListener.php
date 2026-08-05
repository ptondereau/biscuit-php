<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation\Doctrine;

use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Declares the revocation table during schema generation, so doctrine:migrations:diff produces
 * a migration that creates it rather than one that drops it.
 *
 * This deliberately does not extend Symfony\Bridge\Doctrine\SchemaListener\AbstractSchemaListener:
 * filterSchemaChanges() and getIsSameDatabaseChecker() are not present across every
 * symfony/doctrine-bridge release this bundle supports.
 */
final class RevocationSchemaListener
{
    public function __construct(private readonly DoctrineRevocationStore $store)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        $connection = $event->getEntityManager()->getConnection();

        if (!$this->store->ownsConnection($connection)) {
            return;
        }

        $filter = $connection->getConfiguration()->getSchemaAssetsFilter();

        if (null !== $filter && !$filter($this->store->table())) {
            return;
        }

        $schema = $this->store->configureSchema($event->getSchema());

        if (method_exists($event, 'setSchema') && method_exists(Schema::class, 'edit')) {
            $event->setSchema($schema);
        }
    }
}
