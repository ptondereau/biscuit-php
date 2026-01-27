<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Voter;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Security\Voter\BiscuitVoter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(BiscuitVoter::class)]
final class BiscuitVoterTest extends TestCase
{
    #[Test]
    public function itExtendsVoter(): void
    {
        $registry = new PolicyRegistry();
        $voter = new BiscuitVoter($registry);

        self::assertInstanceOf(Voter::class, $voter);
    }

    #[Test]
    public function itSupportsPolicyWhenRegistryHasIt(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if role("admin")',
        ]);
        $voter = new BiscuitVoter($registry);

        $result = $this->callSupports($voter, 'admin_access', null);

        self::assertTrue($result);
    }

    #[Test]
    public function itDoesNotSupportPolicyWhenRegistryDoesNotHaveIt(): void
    {
        $registry = new PolicyRegistry();
        $voter = new BiscuitVoter($registry);

        $result = $this->callSupports($voter, 'unknown_policy', null);

        self::assertFalse($result);
    }

    #[Test]
    public function itSupportsInlinePolicies(): void
    {
        $registry = new PolicyRegistry();
        $voter = new BiscuitVoter($registry);

        $result = $this->callSupports($voter, 'allow if true', null);

        self::assertTrue($result);
    }

    #[Test]
    public function itSupportsDenyInlinePolicies(): void
    {
        $registry = new PolicyRegistry();
        $voter = new BiscuitVoter($registry);

        $result = $this->callSupports($voter, 'deny if false', null);

        self::assertTrue($result);
    }

    #[Test]
    public function itReturnsFalseForNonBiscuitUser(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if true',
        ]);
        $token = $this->createMock(TokenInterface::class);
        $regularUser = $this->createMock(UserInterface::class);

        $token
            ->method('getUser')
            ->willReturn($regularUser);

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, null, ['admin_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itReturnsFalseWhenUserIsNull(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if true',
        ]);
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn(null);

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, null, ['admin_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itAbstainsWhenPolicyNotSupported(): void
    {
        $registry = new PolicyRegistry();
        $token = $this->createMock(TokenInterface::class);

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, null, ['unknown_policy']);

        self::assertSame(Voter::ACCESS_ABSTAIN, $result);
    }

    #[Test]
    public function itReturnsDeniedWhenAuthorizationFails(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if true',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $voter = new BiscuitVoter($registry);

        // Authorization fails because mocked Biscuit cannot be authorized
        $result = $voter->vote($token, null, ['admin_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itHandlesArraySubject(): void
    {
        $registry = new PolicyRegistry([
            'resource_access' => 'allow if resource({resource})',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $voter = new BiscuitVoter($registry);

        // Vote will fail at authorization (no real biscuit) but extractParams is tested
        $result = $voter->vote($token, ['resource' => 'article-456'], ['resource_access']);

        // Authorization fails because we don't have real biscuit, so ACCESS_DENIED
        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itHandlesObjectWithGetIdMethod(): void
    {
        $registry = new PolicyRegistry([
            'resource_access' => 'allow if resource({resource})',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $subject = new class {
            public function getId(): string
            {
                return 'entity-789';
            }
        };

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, $subject, ['resource_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itHandlesObjectWithIntegerId(): void
    {
        $registry = new PolicyRegistry([
            'resource_access' => 'allow if resource({resource})',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $subject = new class {
            public function getId(): int
            {
                return 42;
            }
        };

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, $subject, ['resource_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itHandlesNullSubject(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if true',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, null, ['admin_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itHandlesObjectWithoutGetIdMethod(): void
    {
        $registry = new PolicyRegistry([
            'admin_access' => 'allow if true',
        ]);
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        $subject = new class {
            public function getName(): string
            {
                return 'some-name';
            }
        };

        $voter = new BiscuitVoter($registry);

        $result = $voter->vote($token, $subject, ['admin_access']);

        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    /**
     * Helper method to call the protected supports method.
     */
    private function callSupports(BiscuitVoter $voter, string $attribute, mixed $subject): bool
    {
        $reflection = new ReflectionMethod($voter, 'supports');

        return $reflection->invoke($voter, $attribute, $subject);
    }
}
