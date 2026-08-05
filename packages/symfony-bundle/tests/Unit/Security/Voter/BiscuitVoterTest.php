<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Security\Voter;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Security\Voter\BiscuitVoter;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

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

    #[Test]
    public function itHandlesStringSubjectAsResource(): void
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

        // Vote will fail at authorization (no real biscuit) but extractParams handles string
        $result = $voter->vote($token, 'my-resource-id', ['resource_access']);

        // Authorization fails because we don't have real biscuit, so ACCESS_DENIED
        self::assertSame(Voter::ACCESS_DENIED, $result);
    }

    #[Test]
    public function itInjectsAuthorizerFactsFromTemplateBeforeAuthorizing(): void
    {
        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent"), amount($a), $a <= 100',
        ]);
        $voter = new BiscuitVoter(
            $registry,
            null,
            new AuthorizerBuilderFactory(new Applier(), ['credit' => ['facts' => ['amount({amount})']]]),
        );

        $user = new BiscuitUser($this->buildAgentToken(), 'agent-1');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // The fact template injects amount(50) into the authorizer: 50 <= 100, granted.
        self::assertSame(Voter::ACCESS_GRANTED, $voter->vote($token, ['amount' => 50], ['credit']));

        // amount(150) injected: 150 <= 100 fails, denied.
        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, ['amount' => 150], ['credit']));
    }

    #[Test]
    public function itGrantsWhenTheTokenExpiryIsInTheFuture(): void
    {
        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent")',
        ]);
        $voter = new BiscuitVoter($registry);

        $user = new BiscuitUser($this->buildAgentToken('2099-01-01T00:00:00Z'), 'agent-1');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        self::assertSame(Voter::ACCESS_GRANTED, $voter->vote($token, null, ['credit']));
    }

    #[Test]
    public function itDeniesWhenTheTokenHasExpired(): void
    {
        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent")',
        ]);
        $voter = new BiscuitVoter($registry);

        $user = new BiscuitUser($this->buildAgentToken('2020-01-01T00:00:00Z'), 'agent-1');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, null, ['credit']));
    }

    #[Test]
    public function itDeniesWhenFactTemplateParameterIsMissing(): void
    {
        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent"), amount($a), $a <= 100',
        ]);
        $voter = new BiscuitVoter(
            $registry,
            null,
            new AuthorizerBuilderFactory(new Applier(), ['credit' => ['facts' => ['amount({amount})']]]),
        );

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new BiscuitUser($this->buildAgentToken(), 'agent-1'));

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, ['wrong_key' => 50], ['credit']));
    }

    #[Test]
    public function itDeniesWhenPolicyParameterIsMissing(): void
    {
        $registry = new PolicyRegistry([
            'resource_access' => 'allow if resource({resource})',
        ]);
        $voter = new BiscuitVoter($registry);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new BiscuitUser($this->buildAgentToken(), 'agent-1'));

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, [], ['resource_access']));
    }

    #[Test]
    public function itRecordsAFailedCheckWhenTheTemplateCannotBeApplied(): void
    {
        $collector = new BiscuitDataCollector();
        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent"), amount($a), $a <= 100',
        ]);
        $voter = new BiscuitVoter(
            $registry,
            $collector,
            new AuthorizerBuilderFactory(new Applier(), ['credit' => ['facts' => ['amount({amount})']]]),
        );

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new BiscuitUser($this->buildAgentToken(), 'agent-1'));

        $voter->vote($token, [], ['credit']);
        $collector->collect(new Request(), new Response());

        self::assertSame(1, $collector->getFailedChecks());
        self::assertSame(0, $collector->getPassedChecks());
        self::assertSame('credit', $collector->getPolicyChecks()[0]['policy']);
    }

    #[Test]
    public function itLogsTheReasonWhenAPolicyCheckCannotBeEvaluated(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'Biscuit policy check could not be evaluated',
                self::callback(static fn (array $context): bool => 'credit' === $context['policy']
                    && $context['exception'] instanceof Throwable),
            );

        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent"), amount($a), $a <= 100',
        ]);
        $voter = new BiscuitVoter(
            $registry,
            null,
            new AuthorizerBuilderFactory(new Applier(), ['credit' => ['facts' => ['amount({amount})']]]),
            $logger,
        );

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new BiscuitUser($this->buildAgentToken(), 'agent-1'));

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, [], ['credit']));
    }

    #[Test]
    public function itDoesNotLogWhenAPolicySimplyDenies(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $registry = new PolicyRegistry([
            'credit' => 'allow if role("agent"), amount($a), $a <= 100',
        ]);
        $voter = new BiscuitVoter(
            $registry,
            null,
            new AuthorizerBuilderFactory(new Applier(), ['credit' => ['facts' => ['amount({amount})']]]),
            $logger,
        );

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new BiscuitUser($this->buildAgentToken(), 'agent-1'));

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($token, ['amount' => 150], ['credit']));
    }

    private function buildAgentToken(?string $expiry = null): Biscuit
    {
        $keyPair = new KeyPair();
        $builder = new BiscuitBuilder();
        $builder->addFact(new Fact('role("agent")'));

        if (null !== $expiry) {
            $builder->addCheck(new Check('check if time($t), $t < ' . $expiry));
        }

        return $builder->build($keyPair->getPrivateKey());
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
