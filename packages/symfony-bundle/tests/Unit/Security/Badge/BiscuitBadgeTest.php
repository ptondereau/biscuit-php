<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Badge;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BiscuitBadge::class)]
final class BiscuitBadgeTest extends TestCase
{
    #[Test]
    public function itReturnsBiscuitInstance(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $badge = new BiscuitBadge($biscuit);

        self::assertSame($biscuit, $badge->getBiscuit());
    }

    #[Test]
    public function itIsAlwaysResolved(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $badge = new BiscuitBadge($biscuit);

        self::assertTrue($badge->isResolved());
    }

    #[Test]
    public function itImplementsBadgeInterface(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $badge = new BiscuitBadge($biscuit);

        self::assertInstanceOf(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface::class, $badge);
    }
}
