<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Authenticator;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use Biscuit\BiscuitBundle\Security\Exception\InvalidTokenException;
use Biscuit\BiscuitBundle\Security\Exception\MissingTokenException;
use Biscuit\BiscuitBundle\Security\Exception\RevokedTokenException;
use Biscuit\BiscuitBundle\Security\Http\AuthenticationFailureResponseFactory;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\Datalog\AuthorityBlockReader;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class BiscuitAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly BiscuitTokenManager $tokenManager,
        private readonly ?RevocationChecker $revocationChecker = null,
        private readonly string $userIdentifierFact = 'user',
        private readonly ?BiscuitDataCollector $dataCollector = null,
        private readonly ?KeyManager $keyManager = null,
        private readonly ?AuthenticationFailureResponseFactory $failureResponseFactory = null,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->tokenExtractor->supports($request);
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->tokenExtractor->extract($request);
        if (null === $token) {
            throw new MissingTokenException();
        }

        try {
            $biscuit = $this->tokenManager->parse($token);
        } catch (Exception $e) {
            throw new InvalidTokenException('Invalid biscuit token: ' . $e->getMessage(), 0, $e);
        }

        $this->dataCollector?->setBiscuit($biscuit);
        $this->dataCollector?->setSerializedToken($token);

        if (null !== $this->keyManager && $this->keyManager->hasPublicKey()) {
            $this->dataCollector?->setPublicKey((string) $this->keyManager->getPublicKey());
        }

        if (null !== $this->revocationChecker) {
            $result = $this->revocationChecker->check($biscuit);
            $this->dataCollector?->setRevocationResult($result);

            if ($result->isRevoked()) {
                throw new RevokedTokenException();
            }
        }

        $identifier = $this->extractUserIdentifier($biscuit);

        return new SelfValidatingPassport(
            new UserBadge($identifier, fn (): BiscuitUser => new BiscuitUser($biscuit, $identifier)),
            [new BiscuitBadge($biscuit)],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (null === $this->tokenExtractor->extract($request)) {
            return $this->failureResponse(new MissingTokenException());
        }

        return $this->failureResponse($authException ?? new MissingTokenException());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->failureResponse($exception);
    }

    private function failureResponse(AuthenticationException $exception): Response
    {
        $factory = $this->failureResponseFactory ?? new AuthenticationFailureResponseFactory();

        return $factory->create($exception);
    }

    /**
     * @return non-empty-string
     */
    private function extractUserIdentifier(Biscuit $biscuit): string
    {
        $identifier = (new AuthorityBlockReader())->readFact($biscuit->blockSource(0), $this->userIdentifierFact);

        if (null !== $identifier) {
            return $identifier;
        }

        $revocationIds = $biscuit->revocationIds();
        $firstRevocationId = $revocationIds[0] ?? '';

        return '' !== $firstRevocationId ? $firstRevocationId : 'anonymous';
    }
}
