<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle;

use Biscuit\BiscuitBundle\DependencyInjection\Compiler\RegisterRevocationStoresPass;
use Biscuit\BiscuitBundle\DependencyInjection\Compiler\ResolveRevocationPushBusPass;
use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BiscuitBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(RevocationStoreInterface::class)
            ->addTag(RegisterRevocationStoresPass::STORE_TAG);

        $container->registerForAutoconfiguration(RevocationWriterInterface::class)
            ->addTag(RegisterRevocationStoresPass::WRITER_TAG);

        $container->registerForAutoconfiguration(EnumerableRevocationStoreInterface::class)
            ->addTag(RegisterRevocationStoresPass::ENUMERABLE_TAG);

        $container->addCompilerPass(new RegisterRevocationStoresPass());
        $container->addCompilerPass(
            new ResolveRevocationPushBusPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            ResolveRevocationPushBusPass::PRIORITY,
        );
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
