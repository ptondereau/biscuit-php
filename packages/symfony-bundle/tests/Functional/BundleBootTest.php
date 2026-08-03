<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BundleBootTest extends KernelTestCase
{
    use ResetsTestKernel;

    #[Test]
    public function itCompilesTheContainerWithNoConfiguration(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->has('biscuit.token_manager'));
    }

    #[Test]
    public function itRegistersThePublicServiceAliases(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        foreach ([
            KeyManager::class,
            BiscuitTokenManagerInterface::class,
            PolicyRegistry::class,
            TokenExtractorInterface::class,
            BiscuitAuthenticator::class,
            BiscuitDataCollector::class,
        ] as $id) {
            self::assertTrue($container->has($id), sprintf('Service "%s" should be public.', $id));
        }
    }

    #[Test]
    public function itRegistersEveryConsoleCommand(): void
    {
        $names = array_keys((new Application(self::bootKernel()))->all());

        foreach ([
            'biscuit:keys:generate',
            'biscuit:token:create',
            'biscuit:token:inspect',
            'biscuit:token:attenuate',
            'biscuit:policy:test',
        ] as $name) {
            self::assertContains($name, $names);
        }
    }

    #[Test]
    public function itCompilesTheContainerWithAFirewallUsingTheAuthenticator(): void
    {
        TestKernel::configure([], withFirewall: true);

        self::bootKernel();

        self::assertTrue(self::getContainer()->has('biscuit.authenticator'));
    }

    #[Test]
    public function itAppliesConfiguredPoliciesToTheRegistry(): void
    {
        TestKernel::configure([
            'policies' => ['read_only' => 'allow if right("read");'],
        ]);

        self::bootKernel();

        $registry = self::getContainer()->get(PolicyRegistry::class);
        self::assertInstanceOf(PolicyRegistry::class, $registry);
        self::assertTrue($registry->has('read_only'));
    }
}
