<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Voter;

use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Throwable;

/**
 * @extends Voter<string, mixed>
 */
final class BiscuitVoter extends Voter
{
    public function __construct(
        private readonly PolicyRegistry $policyRegistry,
        private readonly ?BiscuitDataCollector $dataCollector = null,
        private readonly AuthorizerBuilderFactory $authorizerBuilderFactory = new AuthorizerBuilderFactory(),
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->policyRegistry->has($attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof BiscuitUser) {
            return false;
        }

        $biscuit = $user->getBiscuit();

        $params = $this->extractParams($subject);

        $policy = $this->policyRegistry->get($attribute, $params);

        // Pass all policies to the data collector for the sandbox
        $this->dataCollector?->setPolicies($this->policyRegistry->all());

        try {
            $authBuilder = $this->authorizerBuilderFactory->create($attribute, $params);
            $authBuilder->addPolicy($policy);

            $authorizer = $authBuilder->build($biscuit);
        } catch (Throwable $e) {
            $this->logger?->warning('Biscuit policy check could not be evaluated', [
                'policy' => $attribute,
                'exception' => $e,
            ]);

            $this->dataCollector?->recordPolicyCheck($attribute, false, $params);

            return false;
        }

        try {
            $authorizer->authorize();

            $this->dataCollector?->recordPolicyCheck($attribute, true, $params);

            return true;
        } catch (Throwable) {
            $this->dataCollector?->recordPolicyCheck($attribute, false, $params);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractParams(mixed $subject): array
    {
        if (\is_array($subject)) {
            return $subject;
        }

        if (\is_string($subject)) {
            return ['resource' => $subject];
        }

        if (\is_object($subject) && method_exists($subject, 'getId')) {
            return ['resource' => (string) $subject->getId()];
        }

        return [];
    }
}
