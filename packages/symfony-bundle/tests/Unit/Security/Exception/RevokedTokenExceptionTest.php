<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Exception;

use Biscuit\BiscuitBundle\Security\Exception\RevokedTokenException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[CoversClass(RevokedTokenException::class)]
final class RevokedTokenExceptionTest extends TestCase
{
    #[Test]
    public function itExtendsAuthenticationException(): void
    {
        $exception = new RevokedTokenException();

        self::assertInstanceOf(AuthenticationException::class, $exception);
    }

    #[Test]
    public function itReturnsCorrectMessageKey(): void
    {
        $exception = new RevokedTokenException();

        self::assertSame('Token has been revoked.', $exception->getMessageKey());
    }
}
