<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\DependencyInjection;

use Biscuit\BiscuitBundle\BiscuitBundle;
use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use Biscuit\BiscuitBundle\DependencyInjection\Compiler\RegisterRevocationStoresPass;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Messenger\MessageBus;

#[CoversNothing]
final class RevocationDoctrineContainerTest extends TestCase
{
    #[Test]
    public function itRegistersNothingWhenTheStoreIsOff(): void
    {
        $container = $this->compile();

        self::assertFalse($container->hasDefinition('biscuit.revocation.store.doctrine'));
        self::assertFalse($container->hasDefinition('biscuit.revocation.doctrine.setup_command'));
        self::assertFalse($container->hasDefinition('biscuit.revocation.doctrine.schema_listener'));
    }

    #[Test]
    public function itConsultsTheDatabaseAfterEveryCheaperStore(): void
    {
        $container = $this->compile([
            'static' => ['ids' => ['abc']],
            'cache' => ['enabled' => true],
            'in_memory' => ['enabled' => true],
            'doctrine' => ['enabled' => true],
        ]);

        self::assertSame(
            ['static', 'in_memory', 'cache', 'doctrine'],
            $this->storeKeys($container),
            'The database does the slowest I/O, so it must answer last.',
        );
    }

    #[Test]
    public function itRegistersTheStoreAsAWriterAndAsEnumerable(): void
    {
        $container = $this->compile(['doctrine' => ['enabled' => true]]);

        self::assertArrayHasKey(
            'biscuit.revocation.store.doctrine',
            $container->findTaggedServiceIds(RegisterRevocationStoresPass::WRITER_TAG, true),
        );
        self::assertArrayHasKey(
            'biscuit.revocation.store.doctrine',
            $container->findTaggedServiceIds(RegisterRevocationStoresPass::ENUMERABLE_TAG, true),
        );
    }

    #[Test]
    public function itRegistersTheSchemaListenerSoMigrationsDoNotDropTheTable(): void
    {
        $container = $this->compile(['doctrine' => ['enabled' => true]]);

        $tags = $container->getDefinition('biscuit.revocation.doctrine.schema_listener')
            ->getTag('doctrine.event_listener');

        self::assertSame([['event' => 'postGenerateSchema']], $tags);
    }

    #[Test]
    public function itUsesTheDefaultTableWhenNoneIsConfigured(): void
    {
        $container = $this->compile(['doctrine' => ['enabled' => true]]);

        self::assertSame(
            DoctrineRevocationStore::DEFAULT_TABLE,
            $container->getParameter('biscuit.revocation.stores.doctrine.table'),
        );
    }

    #[Test]
    public function itPassesAConfiguredTableToTheStore(): void
    {
        $container = $this->compile(['doctrine' => ['enabled' => true, 'table' => 'app_revocations']]);

        self::assertSame(
            'app_revocations',
            $this->argument($container, 'biscuit.revocation.store.doctrine', DoctrineRevocationStore::class, 'table'),
        );
    }

    #[Test]
    public function itPointsTheStoreAtAConfiguredConnection(): void
    {
        $container = $this->compile([
            'doctrine' => ['enabled' => true, 'connection' => 'app.reporting_connection'],
        ]);

        self::assertSame(
            'app.reporting_connection',
            (string) $this->argument($container, 'biscuit.revocation.store.doctrine', DoctrineRevocationStore::class, 'connection'),
        );
    }

    #[Test]
    public function itNamesTheMissingConnectionRatherThanFailingOnServiceLookup(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/connection "doctrine.dbal.default_connection" does not exist/');

        $this->compile(['doctrine' => ['enabled' => true]], registerConnections: false);
    }

    #[Test]
    public function itRejectsATableNameThatCouldCarrySql(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/must match/');

        $this->compile(['doctrine' => ['enabled' => true, 'table' => 'revoked; DROP TABLE users']]);
    }

    #[Test]
    public function itRejectsATableNameThatIsTooLongForEveryPlatform(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->compile(['doctrine' => ['enabled' => true, 'table' => str_repeat('t', 64)]]);
    }

    #[Test]
    public function itWiresTheDatabaseStoreAndMessengerPushTogether(): void
    {
        $container = $this->compile(
            ['in_memory' => ['enabled' => true], 'doctrine' => ['enabled' => true]],
            push: ['enabled' => true],
        );

        self::assertContains('doctrine', $this->storeKeys($container));
        self::assertTrue($container->hasDefinition('biscuit.revocation.publisher'));
        self::assertTrue(
            $container->hasDefinition('biscuit.revocation.store.doctrine'),
            'A durable store behind push is the documented way to survive a restart.',
        );
    }

    /**
     * @return list<string>
     */
    private function storeKeys(ContainerBuilder $container): array
    {
        $stores = $this->argument($container, 'biscuit.revocation.checker', RevocationChecker::class, 'stores');

        self::assertInstanceOf(IteratorArgument::class, $stores);

        return array_map('strval', array_keys($stores->getValues()));
    }

    /**
     * @param class-string $class
     */
    private function argument(
        ContainerBuilder $container,
        string $serviceId,
        string $class,
        string $name,
    ): mixed {
        $arguments = $container->getDefinition($serviceId)->getArguments();

        if (\array_key_exists('$' . $name, $arguments)) {
            return $arguments['$' . $name];
        }

        foreach ((new ReflectionClass($class))->getConstructor()?->getParameters() ?? [] as $index => $parameter) {
            if ($parameter->getName() === $name) {
                return $arguments[$index] ?? null;
            }
        }

        self::fail(sprintf('Constructor of "%s" has no parameter named "$%s".', $class, $name));
    }

    /**
     * @param array<string, mixed> $stores
     * @param array<string, mixed> $push
     */
    private function compile(
        array $stores = [],
        array $push = [],
        bool $registerConnections = true,
    ): ContainerBuilder {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.project_dir' => \dirname(__DIR__, 3),
            'kernel.bundles' => [],
            'kernel.bundles_metadata' => [],
            'kernel.container_class' => 'BiscuitRevocationDoctrineTestContainer',
            'kernel.charset' => 'UTF-8',
        ]));

        $container->register('cache.app', ArrayAdapter::class);
        $container->register('messenger.bus.default', MessageBus::class);
        $container->setAlias('messenger.default_bus', 'messenger.bus.default');

        if ($registerConnections) {
            $container->register('doctrine.dbal.default_connection', Connection::class);
            $container->register('app.reporting_connection', Connection::class);
        }

        if ([] === $stores) {
            $stores = ['static' => ['ids' => ['abc']]];
        }

        $revocation = [
            'enabled' => true,
            'on_unavailable' => 'deny',
            'stores' => $stores,
        ];

        if ([] !== $push) {
            $revocation['push'] = $push;
        }

        (new BiscuitBundle())->build($container);
        (new BiscuitExtension())->load([['revocation' => $revocation]], $container);

        $container->getCompiler()->getPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
