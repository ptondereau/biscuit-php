<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\EventListener;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;

#[AsEventListener(event: 'kernel.controller_arguments', priority: 0)]
final class BiscuitPolicyListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PolicyRegistry $policyRegistry,
    ) {
    }

    public function __invoke(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();

        if (\is_array($controller)) {
            [$object, $method] = $controller;
            $policies = $this->getPolicies($object, $method);
        } elseif (\is_object($controller) && method_exists($controller, '__invoke')) {
            $policies = $this->getPolicies($controller, '__invoke');
        } else {
            return;
        }

        if ([] === $policies) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new AccessDeniedHttpException('Access denied: no authentication token.');
        }

        $user = $token->getUser();
        if (!$user instanceof BiscuitUser) {
            throw new AccessDeniedHttpException('Access denied: invalid user type.');
        }

        $biscuit = $user->getBiscuit();
        $request = $event->getRequest();

        foreach ($policies as $policyAttribute) {
            $params = $this->resolveParams($policyAttribute->params, $request->attributes->all());

            $policy = $this->policyRegistry->get($policyAttribute->policy, $params);

            $authBuilder = new AuthorizerBuilder();
            $authBuilder->addPolicy($policy);

            try {
                $authorizer = $authBuilder->build($biscuit);
                if (0 !== $authorizer->authorize()) {
                    throw new AccessDeniedHttpException('Access denied by Biscuit policy.');
                }
            } catch (AccessDeniedHttpException $e) {
                throw $e;
            } catch (Throwable) {
                throw new AccessDeniedHttpException('Access denied by Biscuit policy.');
            }
        }
    }

    /**
     * @return list<BiscuitPolicy>
     */
    private function getPolicies(object $controller, string $method): array
    {
        $policies = [];

        $classReflection = new ReflectionClass($controller);
        foreach ($classReflection->getAttributes(BiscuitPolicy::class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
            $policies[] = $attr->newInstance();
        }

        $methodReflection = new ReflectionMethod($controller, $method);
        foreach ($methodReflection->getAttributes(BiscuitPolicy::class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
            $policies[] = $attr->newInstance();
        }

        return $policies;
    }

    /**
     * Resolve params by replacing placeholders with request attribute values.
     *
     * @param array<string, mixed> $params
     * @param array<string, mixed> $requestAttributes
     *
     * @return array<string, mixed>
     */
    private function resolveParams(array $params, array $requestAttributes): array
    {
        $resolved = [];

        foreach ($params as $key => $value) {
            if (\is_string($value) && str_starts_with($value, '@')) {
                $attrName = substr($value, 1);
                $resolved[$key] = $requestAttributes[$attrName] ?? $value;
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}
