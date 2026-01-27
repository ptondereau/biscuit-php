<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\User;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(BiscuitUser::class)]
final class BiscuitUserTest extends TestCase
{
    #[Test]
    public function itReturnsBiscuitInstance(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');

        self::assertSame($biscuit, $user->getBiscuit());
    }

    #[Test]
    public function itReturnsUserIdentifier(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');

        self::assertSame('user-123', $user->getUserIdentifier());
    }

    #[Test]
    public function itReturnsBiscuitUserRole(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');

        self::assertSame(['ROLE_BISCUIT_USER'], $user->getRoles());
    }

    #[Test]
    public function itCanEraseCredentialsWithoutError(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');

        $user->eraseCredentials();

        self::assertTrue(true);
    }

    #[Test]
    public function itImplementsUserInterface(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');

        self::assertInstanceOf(UserInterface::class, $user);
    }
}
