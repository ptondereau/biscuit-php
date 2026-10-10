<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\DependencyInjection;

use Biscuit\BiscuitBundle\BiscuitBundle;
use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use Biscuit\BiscuitBundle\DependencyInjection\Compiler\RegisterRevocationStoresPass;
use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevocationPushHandler;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use Biscuit\BiscuitBundle\Revocation\Store\PublishingRevocationWriter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Messenger\MessageBus;

#[CoversNothing]
final class RevocationPushContainerTest extends TestCase
{
    #[Test]
    public function itRegistersNothingWhenPushIsOff(): void
    {
        $container = $this->compile();

        self::assertFalse($container->hasDefinition('biscuit.revocation.publisher'));
        self::assertFalse($container->hasDefinition('biscuit.revocation.push_handler'));
        self::assertFalse($container->hasDefinition('biscuit.revocation.writer.local'));
    }

    #[Test]
    public function itLeavesTheWriterAliasOnTheLocalChainWhenPushIsOff(): void
    {
        $container = $this->compile();

        self::assertSame(
            'biscuit.revocation.writer',
            (string) $container->getAlias(RevocationWriterInterface::class),
        );
    }

    #[Test]
    public function itIgnoresPushWhenRevocationItselfIsOff(): void
    {
        $container = $this->compile(['enabled' => false], ['enabled' => true]);

        self::assertFalse($container->hasDefinition('biscuit.revocation.publisher'));
    }

    #[Test]
    public function itMovesTheWriterAliasToThePublisherWhenPushIsOn(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        self::assertSame(
            'biscuit.revocation.publisher',
            (string) $container->getAlias(RevocationWriterInterface::class),
            'Commands and userland must publish; that is the whole point of enabling push.',
        );
    }

    #[Test]
    public function itNeverHandsThePublisherToTheHandler(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        self::assertSame(
            'biscuit.revocation.writer.local',
            (string) $this->argument($container, 'biscuit.revocation.push_handler', RevocationPushHandler::class, 'writer'),
            'A handler holding the publisher would re-broadcast every message it consumed.',
        );
    }

    #[Test]
    public function itSilencesTheWriterTheHandlerUsesSoConsumersDoNotRefireRevokedEvents(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        self::assertNull(
            $this->argument($container, 'biscuit.revocation.writer.local', ChainRevocationWriter::class, 'eventDispatcher'),
        );
    }

    #[Test]
    public function itGivesThePublisherTheEventDispatchingChainWriter(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        self::assertSame(
            'biscuit.revocation.writer',
            (string) $this->argument($container, 'biscuit.revocation.publisher', PublishingRevocationWriter::class, 'writer'),
        );
    }

    #[Test]
    public function itRegistersOneHandlerMethodPerMessage(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        $handled = [];

        foreach ($container->getDefinition('biscuit.revocation.push_handler')->getTag('messenger.message_handler') as $tag) {
            $handled[(string) $tag['handles']] = [$tag['method'], $tag['bus']];
        }

        self::assertSame([
            RevokeToken::class => ['handleRevoke', 'messenger.bus.default'],
            UnrevokeToken::class => ['handleUnrevoke', 'messenger.bus.default'],
            PurgeExpiredRevocations::class => ['handlePurge', 'messenger.bus.default'],
        ], $handled);
    }

    #[Test]
    public function itRoutesBothTheHandlersAndThePublisherToACustomBus(): void
    {
        $container = $this->compile(push: ['enabled' => true, 'bus' => 'app.audit_bus']);

        self::assertSame(
            'app.audit_bus',
            (string) $this->argument($container, 'biscuit.revocation.publisher', PublishingRevocationWriter::class, 'bus'),
        );

        foreach ($container->getDefinition('biscuit.revocation.push_handler')->getTag('messenger.message_handler') as $tag) {
            self::assertSame('app.audit_bus', $tag['bus']);
        }
    }

    #[Test]
    public function itKeepsThePublisherOutOfTheWriterChainItWrites(): void
    {
        $container = $this->compile(push: ['enabled' => true]);

        $writers = array_keys($container->findTaggedServiceIds(RegisterRevocationStoresPass::WRITER_TAG, true));

        self::assertNotContains(
            'biscuit.revocation.publisher',
            $writers,
            'The publisher implements RevocationWriterInterface, so a stray writer tag would make it dispatch to itself forever.',
        );
        self::assertNotContains('biscuit.revocation.writer', $writers);
        self::assertNotContains('biscuit.revocation.writer.local', $writers);
    }

    #[Test]
    public function itResolvesABusAliasToTheServiceMessengerActuallyKnows(): void
    {
        $container = $this->compile(
            push: ['enabled' => true, 'bus' => 'app.bus_alias'],
            register: static function (ContainerBuilder $container): void {
                $container->setAlias('app.bus_alias', 'messenger.bus.default');
            },
        );

        foreach ($container->getDefinition('biscuit.revocation.push_handler')->getTag('messenger.message_handler') as $tag) {
            self::assertSame(
                'messenger.bus.default',
                $tag['bus'],
                'MessengerPass matches the tag against real bus ids, so an alias has to be resolved before it runs.',
            );
        }
    }

    #[Test]
    public function itNamesTheMissingBusWhenItCannotBeResolved(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/message bus "app.nope" does not exist/');

        $this->compile(push: ['enabled' => true, 'bus' => 'app.nope']);
    }

    #[Test]
    public function itBuildsAPublisherThatWritesAndPublishesFromTheCompiledContainer(): void
    {
        $container = $this->compile(
            push: ['enabled' => true],
            keepUnusedDefinitions: false,
        );

        $publisher = $container->get('biscuit.revocation.publisher');

        self::assertInstanceOf(PublishingRevocationWriter::class, $publisher);
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
     * @param array<string, mixed> $revocation
     * @param array<string, mixed> $push
     * @param callable(ContainerBuilder):void|null $register
     */
    private function compile(
        array $revocation = [],
        array $push = [],
        bool $keepUnusedDefinitions = true,
        ?callable $register = null,
    ): ContainerBuilder {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.project_dir' => \dirname(__DIR__, 3),
            'kernel.bundles' => [],
            'kernel.bundles_metadata' => [],
            'kernel.container_class' => 'BiscuitRevocationPushTestContainer',
            'kernel.charset' => 'UTF-8',
        ]));

        $container->register('cache.app', ArrayAdapter::class);
        $container->register('messenger.bus.default', MessageBus::class);
        $container->setAlias('messenger.default_bus', 'messenger.bus.default');
        $container->register('app.audit_bus', MessageBus::class);

        if (null !== $register) {
            $register($container);
        }

        $config = ['revocation' => [
            'enabled' => true,
            'on_unavailable' => 'deny',
            'stores' => ['in_memory' => ['enabled' => true]],
            ...([] === $push ? [] : ['push' => $push]),
            ...$revocation,
        ]];

        (new BiscuitBundle())->build($container);
        (new BiscuitExtension())->load([$config], $container);

        if ($keepUnusedDefinitions) {
            $container->getCompiler()->getPassConfig()->setRemovingPasses([]);
        } elseif ($container->hasDefinition('biscuit.revocation.publisher')) {
            $container->getDefinition('biscuit.revocation.publisher')->setPublic(true);
        }

        $container->compile();

        return $container;
    }
}
