<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\DependencyInjection;

use Biscuit\BiscuitBundle\BiscuitBundle;
use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use Biscuit\BiscuitBundle\DependencyInjection\Compiler\RegisterRevocationStoresPass;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Tests\Integration\Fixtures\CustomEnumerableRevocationStore;
use Biscuit\BiscuitBundle\Tests\Integration\Fixtures\CustomRevocationStore;
use Biscuit\BiscuitBundle\Tests\Integration\Fixtures\NotARevocationStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(RegisterRevocationStoresPass::class)]
final class RevocationContainerTest extends TestCase
{
    #[Test]
    public function itInjectsNullIntoTheAuthenticatorWhenRevocationIsDisabled(): void
    {
        $container = $this->compile();

        self::assertFalse($container->hasDefinition('biscuit.revocation.checker'));
        self::assertNull(
            $this->argument($container, 'biscuit.authenticator', BiscuitAuthenticator::class, 'revocationChecker'),
        );
    }

    #[Test]
    public function itInjectsTheCheckerIntoTheAuthenticatorWhenRevocationIsEnabled(): void
    {
        $container = $this->compile([
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['static' => ['ids' => ['abc']]],
            ],
        ]);

        $checker = $this->argument($container, 'biscuit.authenticator', BiscuitAuthenticator::class, 'revocationChecker');

        self::assertInstanceOf(Reference::class, $checker);
        self::assertSame('biscuit.revocation.checker', (string) $checker);
    }

    #[Test]
    public function itRefusesToCompileWhenRevocationIsEnabledWithNoStores(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/no revocation store is registered/');

        $this->compile(['revocation' => ['enabled' => true, 'on_unavailable' => 'deny']]);
    }

    #[Test]
    public function itNamesTheMissingPoolWhenTheCacheStoreCannotResolveIt(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/cache pool "cache.app" does not exist/');

        $this->compile(
            [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['cache' => ['enabled' => true]],
                ],
            ],
            registerCachePool: false,
        );
    }

    #[Test]
    public function itOrdersStoresByDescendingPriority(): void
    {
        $container = $this->compile([
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => [
                    'static' => ['ids' => ['abc']],
                    'cache' => ['enabled' => true],
                    'in_memory' => ['enabled' => true],
                ],
            ],
        ]);

        self::assertSame(
            ['static', 'in_memory', 'cache'],
            $this->storeKeys($container),
            'Cheap in-memory lookups must be consulted before anything that does I/O.',
        );
    }

    #[Test]
    public function itPicksUpAnAutoconfiguredUserlandStoreWithNoTagInTheAppConfig(): void
    {
        $container = $this->compile(
            ['revocation' => ['enabled' => true, 'on_unavailable' => 'deny']],
            static function (ContainerBuilder $container): void {
                $container->register('app.custom_store', CustomRevocationStore::class)
                    ->setAutoconfigured(true)
                    ->setPublic(true);
            },
        );

        self::assertContains('app.custom_store', $this->storeKeys($container));
    }

    #[Test]
    public function itPlacesUserlandStoresAfterTheBundleStores(): void
    {
        $container = $this->compile(
            [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['static' => ['ids' => ['abc']]],
                ],
            ],
            static function (ContainerBuilder $container): void {
                $container->register('app.custom_store', CustomRevocationStore::class)
                    ->setAutoconfigured(true);
            },
        );

        self::assertSame(['static', 'app.custom_store'], $this->storeKeys($container));
    }

    #[Test]
    public function itRejectsATaggedServiceThatIsNotAStore(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/does not implement/');

        $this->compile(
            ['revocation' => ['enabled' => true, 'on_unavailable' => 'deny']],
            static function (ContainerBuilder $container): void {
                $container->register('app.broken', NotARevocationStore::class)
                    ->addTag(RegisterRevocationStoresPass::STORE_TAG);
            },
        );
    }

    #[Test]
    public function itRejectsATaggedServiceThatCannotEnumerateItsEntries(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches(
            '/tagged "biscuit\.revocation_enumerable_store".+does not implement.+EnumerableRevocationStoreInterface/',
        );

        $this->compile(
            ['revocation' => ['enabled' => true, 'on_unavailable' => 'deny']],
            static function (ContainerBuilder $container): void {
                $container->register('app.not_enumerable', CustomRevocationStore::class)
                    ->addTag(RegisterRevocationStoresPass::STORE_TAG)
                    ->addTag(RegisterRevocationStoresPass::ENUMERABLE_TAG);
            },
        );
    }

    #[Test]
    public function itAutoconfiguresAUserlandEnumerableStoreOnBothTags(): void
    {
        $container = $this->compile(
            ['revocation' => ['enabled' => true, 'on_unavailable' => 'deny']],
            static function (ContainerBuilder $container): void {
                $container->register('app.enumerable_store', CustomEnumerableRevocationStore::class)
                    ->setAutoconfigured(true);
            },
        );

        self::assertArrayHasKey(
            'app.enumerable_store',
            $container->findTaggedServiceIds(RegisterRevocationStoresPass::ENUMERABLE_TAG, true),
        );
        self::assertContains('app.enumerable_store', $this->storeKeys($container));
    }

    #[Test]
    public function itNeverPassesTheCheckerToItself(): void
    {
        $container = $this->compile([
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['static' => ['ids' => ['abc']]],
            ],
        ]);

        self::assertNotContains('biscuit.revocation.checker', $this->storeKeys($container));
    }

    #[Test]
    public function itResolvesTheFailurePolicyIntoTheChecker(): void
    {
        $container = $this->compile([
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'allow',
                'stores' => ['static' => ['ids' => ['abc']]],
            ],
        ]);

        self::assertSame(
            'allow',
            $this->argument($container, 'biscuit.revocation.checker', RevocationChecker::class, 'failurePolicy'),
        );
    }

    #[Test]
    public function itBuildsAUsableCheckerFromTheCompiledContainer(): void
    {
        $container = $this->compile(
            [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['static' => ['ids' => ['ABC']]],
                ],
            ],
            keepUnusedDefinitions: false,
        );

        $checker = $container->get('biscuit.revocation.checker');

        self::assertInstanceOf(RevocationChecker::class, $checker);
        self::assertTrue($checker->checkIds(['abc'])->isRevoked());
        self::assertSame('static', $checker->checkIds(['abc'])->store);
        self::assertFalse($checker->checkIds(['def'])->isRevoked());
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
     * @param array<string, mixed> $config
     * @param callable(ContainerBuilder):void|null $register
     */
    private function compile(
        array $config = [],
        ?callable $register = null,
        bool $keepUnusedDefinitions = true,
        bool $registerCachePool = true,
    ): ContainerBuilder {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.project_dir' => \dirname(__DIR__, 3),
            'kernel.bundles' => [],
            'kernel.bundles_metadata' => [],
            'kernel.container_class' => 'BiscuitRevocationTestContainer',
            'kernel.charset' => 'UTF-8',
        ]));

        if ($registerCachePool) {
            $container->register('cache.app', ArrayAdapter::class);
        }

        (new BiscuitBundle())->build($container);
        (new BiscuitExtension())->load([$config], $container);

        if (null !== $register) {
            $register($container);
        }

        if ($keepUnusedDefinitions) {
            $container->getCompiler()->getPassConfig()->setRemovingPasses([]);
        } else {
            $container->getDefinition('biscuit.revocation.checker')->setPublic(true);
        }

        $container->compile();

        return $container;
    }
}
