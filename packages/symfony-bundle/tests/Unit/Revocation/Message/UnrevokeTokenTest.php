<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Message;

use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnrevokeToken::class)]
final class UnrevokeTokenTest extends TestCase
{
    #[Test]
    public function itReturnsTheRevocationIdItWasGiven(): void
    {
        self::assertSame('abc123', (new UnrevokeToken('abc123'))->toRevocationId());
    }

    #[Test]
    public function itRejectsAnEmptyRevocationIdFromTheWire(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty revocation id');

        (new UnrevokeToken(''))->toRevocationId();
    }
}
