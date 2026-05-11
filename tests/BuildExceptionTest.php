<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\BiscuitBuildException;
use Biscuit\Exception\BiscuitException;
use Biscuit\Exception\BlockAppendException;
use Biscuit\Exception\BuildException;
use Biscuit\Exception\ThirdPartyBlockAppendException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BuildExceptionTest extends TestCase
{
    #[Test]
    public function biscuitBuildExceptionExtendsBuildExceptionAndBiscuitException(): void
    {
        static::assertTrue(is_subclass_of(BiscuitBuildException::class, BuildException::class));
        static::assertTrue(is_subclass_of(BiscuitBuildException::class, BiscuitException::class));
    }

    #[Test]
    public function blockAppendExceptionExtendsBuildException(): void
    {
        static::assertTrue(is_subclass_of(BlockAppendException::class, BuildException::class));
        static::assertTrue(is_subclass_of(BlockAppendException::class, BiscuitException::class));
    }

    #[Test]
    public function authorizerBuildExceptionExtendsBuildException(): void
    {
        static::assertTrue(is_subclass_of(AuthorizerBuildException::class, BuildException::class));
        static::assertTrue(is_subclass_of(AuthorizerBuildException::class, BiscuitException::class));
    }

    #[Test]
    public function thirdPartyBlockAppendExceptionExtendsBuildException(): void
    {
        static::assertTrue(is_subclass_of(ThirdPartyBlockAppendException::class, BuildException::class));
        static::assertTrue(is_subclass_of(ThirdPartyBlockAppendException::class, BiscuitException::class));
    }
}
