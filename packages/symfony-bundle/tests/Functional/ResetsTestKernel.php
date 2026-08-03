<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\BiscuitBundle\Tests\TestKernel;

trait ResetsTestKernel
{
    protected function setUp(): void
    {
        parent::setUp();

        TestKernel::reset();
        self::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        TestKernel::reset();

        parent::tearDown();
    }
}
