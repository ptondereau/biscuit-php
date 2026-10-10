<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Integration\DependencyInjection;

use Biscuit\BiscuitBundle\BiscuitBundle;
use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class ContainerCompilationTest extends TestCase
{
    #[Test]
    public function itCompilesWithNoConfiguration(): void
    {
        $container = $this->compile(keepUnusedDefinitions: false);

        self::assertTrue($container->isCompiled());
    }

    #[Test]
    public function itRegistersTheTokenManagerAndItsAliases(): void
    {
        $container = $this->compile();

        self::assertTrue($container->hasDefinition('biscuit.token_manager'));
        self::assertTrue($container->has(BiscuitTokenManager::class));
    }

    #[Test]
    public function itLeavesTheAuthenticatorWithoutARevocationCheckerByDefault(): void
    {
        $container = $this->compile();

        self::assertNull($container->getDefinition('biscuit.authenticator')->getArgument(2));
    }

    #[Test]
    public function itBuildsTheExtractorChainFromTheConfiguredExtractors(): void
    {
        $container = $this->compile([
            'security' => ['token_extractor' => ['header' => true, 'cookie' => 'auth']],
        ]);

        $arguments = $container->getDefinition('biscuit.token_extractor')->getArguments();

        self::assertCount(2, $arguments);
    }

    #[Test]
    public function itKeepsOnlyTheHeaderExtractorByDefault(): void
    {
        $arguments = $this->compile()->getDefinition('biscuit.token_extractor')->getArguments();

        self::assertCount(1, $arguments);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function compile(array $config = [], bool $keepUnusedDefinitions = true): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.project_dir' => \dirname(__DIR__, 3),
            'kernel.bundles' => [],
            'kernel.bundles_metadata' => [],
            'kernel.container_class' => 'BiscuitTestContainer',
            'kernel.charset' => 'UTF-8',
        ]));

        (new BiscuitBundle())->build($container);
        (new BiscuitExtension())->load([$config], $container);

        if ($keepUnusedDefinitions) {
            $container->getCompiler()->getPassConfig()->setRemovingPasses([]);
        }

        $container->compile();

        return $container;
    }
}
