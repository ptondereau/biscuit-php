<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests;

use Biscuit\BiscuitBundle\BiscuitBundle;
use Psr\Log\NullLogger;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var array<string, mixed>
     */
    public static array $biscuitConfig = [];

    public static bool $withFirewall = false;

    /**
     * @param array<string, mixed> $biscuitConfig
     */
    public static function configure(array $biscuitConfig = [], bool $withFirewall = false): void
    {
        self::$biscuitConfig = $biscuitConfig;
        self::$withFirewall = $withFirewall;
    }

    public static function reset(): void
    {
        self::configure();
    }

    /**
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new TwigBundle();
        yield new WebProfilerBundle();
        yield new BiscuitBundle();
    }

    public function getCacheDir(): string
    {
        return $this->temporaryDir() . '/cache';
    }

    public function getLogDir(): string
    {
        return $this->temporaryDir() . '/log';
    }

    public function getConfigDir(): string
    {
        return $this->temporaryDir() . '/config';
    }

    public function protectedAction(): Response
    {
        return new JsonResponse(['status' => 'authenticated']);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'biscuit-test',
            'test' => true,
            'php_errors' => ['log' => false, 'throw' => false],
            'profiler' => ['enabled' => true, 'collect' => true, 'only_exceptions' => false],
        ]);

        $container->extension('security', $this->securityConfig());
        $container->extension('twig', ['strict_variables' => true]);
        $container->extension('web_profiler', [
            'toolbar' => false,
            'intercept_redirects' => false,
        ]);
        $container->extension('biscuit', self::$biscuitConfig);

        $container->services()->set('logger', NullLogger::class);

        $container->parameters()->set('container.dumper.inline_factories', false);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('protected', '/protected')->controller('kernel::protectedAction');

        $routes->import($this->profilerRoutingFile())->prefix('/_profiler');
    }

    private function profilerRoutingFile(): string
    {
        $bundleDir = \dirname((string) (new ReflectionClass(WebProfilerBundle::class))->getFileName());
        $base = $bundleDir . '/Resources/config/routing/profiler';

        return is_file($base . '.php') ? $base . '.php' : $base . '.xml';
    }

    /**
     * @return array<string, mixed>
     */
    private function securityConfig(): array
    {
        $firewall = [
            'pattern' => '^/',
            'stateless' => true,
            'security' => self::$withFirewall,
        ];

        $config = [
            'providers' => [
                'biscuit_users' => ['memory' => ['users' => []]],
            ],
            'firewalls' => ['main' => $firewall],
        ];

        if (self::$withFirewall) {
            $firewall['provider'] = 'biscuit_users';
            $firewall['custom_authenticators'] = ['biscuit.authenticator'];
            $firewall['entry_point'] = 'biscuit.authenticator';
            $config['firewalls']['main'] = $firewall;

            $config['access_control'] = [
                ['path' => '^/protected', 'roles' => 'IS_AUTHENTICATED_FULLY'],
            ];
        }

        return $config;
    }

    private function temporaryDir(): string
    {
        $fingerprint = substr(hash('xxh128', serialize([self::$biscuitConfig, self::$withFirewall])), 0, 16);

        return sys_get_temp_dir() . '/biscuit-bundle-tests/' . $fingerprint;
    }
}
