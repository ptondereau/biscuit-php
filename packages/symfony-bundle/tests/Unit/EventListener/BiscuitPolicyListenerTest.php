<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\EventListener;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;
use Biscuit\BiscuitBundle\EventListener\BiscuitPolicyListener;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(BiscuitPolicyListener::class)]
final class BiscuitPolicyListenerTest extends TestCase
{
    #[Test]
    public function itDoesNothingWhenNoPoliciesOnController(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $registry = new PolicyRegistry();
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);

        $listener($event);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function itThrowsWhenNoTokenPresent(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $registry = new PolicyRegistry(['test_policy' => 'allow if true']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new #[BiscuitPolicy('test_policy')] class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('no authentication token');

        $listener($event);
    }

    #[Test]
    public function itThrowsWhenUserIsNotBiscuitUser(): void
    {
        $regularUser = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($regularUser);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $registry = new PolicyRegistry(['test_policy' => 'allow if true']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new #[BiscuitPolicy('test_policy')] class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid user type');

        $listener($event);
    }

    #[Test]
    public function itReadsPoliciesFromMethodAttribute(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $registry = new PolicyRegistry(['test_policy' => 'allow if true']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new class {
            #[BiscuitPolicy('test_policy')]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);

        $this->expectException(AccessDeniedHttpException::class);

        $listener($event);
    }

    #[Test]
    public function itReadsPoliciesFromClassAttribute(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $registry = new PolicyRegistry(['test_policy' => 'allow if true']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new #[BiscuitPolicy('test_policy')] class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);

        $this->expectException(AccessDeniedHttpException::class);

        $listener($event);
    }

    #[Test]
    public function itHandlesInvokableController(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $registry = new PolicyRegistry(['test_policy' => 'allow if true']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new #[BiscuitPolicy('test_policy')] class {
            public function __invoke(): void
            {
            }
        };

        $event = $this->createEvent($controller);

        $this->expectException(AccessDeniedHttpException::class);

        $listener($event);
    }

    #[Test]
    public function itResolvesParamsWithAtPrefix(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $user = new BiscuitUser($biscuit, 'user-123');
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $registry = new PolicyRegistry(['resource_policy' => 'allow if right({resource}, "read")']);
        $listener = new BiscuitPolicyListener($tokenStorage, $registry);

        $controller = new class {
            #[BiscuitPolicy('resource_policy', params: ['resource' => '@dog'])]
            public function action(): void
            {
            }
        };

        $request = new Request();
        $request->attributes->set('dog', 'puna');

        $event = $this->createEvent([$controller, 'action'], $request);

        $this->expectException(AccessDeniedHttpException::class);

        $listener($event);
    }

    /**
     * @param callable|array{0: object, 1: string} $controller
     */
    private function createEvent(callable|array $controller, ?Request $request = null): ControllerArgumentsEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request ??= new Request();

        return new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
