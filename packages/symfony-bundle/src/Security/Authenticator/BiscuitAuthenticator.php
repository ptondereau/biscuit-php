<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Authenticator;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Cache\Revocation\RevocationCheckerInterface;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Security\Badge\BiscuitBadge;
use Biscuit\BiscuitBundle\Security\Exception\RevokedTokenException;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class BiscuitAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly BiscuitTokenManagerInterface $tokenManager,
        private readonly ?RevocationCheckerInterface $revocationChecker = null,
        private readonly string $userIdentifierFact = 'user',
        private readonly ?BiscuitDataCollector $dataCollector = null,
        private readonly ?KeyManager $keyManager = null,
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
            throw new CustomUserMessageAuthenticationException('No biscuit token provided');
        }

        try {
            $biscuit = $this->tokenManager->parse($token);
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('Invalid biscuit token: ' . $e->getMessage());
        }

        if (null !== $this->revocationChecker && $this->revocationChecker->isRevoked($biscuit)) {
            throw new RevokedTokenException();
        }

        $this->dataCollector?->setBiscuit($biscuit);
        $this->dataCollector?->setSerializedToken($token);

        if (null !== $this->keyManager && $this->keyManager->hasPublicKey()) {
            $this->dataCollector?->setPublicKey((string) $this->keyManager->getPublicKey());
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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'error' => $exception->getMessageKey(),
                'message' => $exception->getMessage(),
            ],
            Response::HTTP_UNAUTHORIZED,
        );
    }

    /**
     * @return non-empty-string
     */
    private function extractUserIdentifier(Biscuit $biscuit): string
    {
        $source = $biscuit->blockSource(0);

        $factName = preg_quote($this->userIdentifierFact, '/');
        $stringPattern = '/' . $factName . '\("([^"]+)"\)/';
        if (1 === preg_match($stringPattern, $source, $matches) && '' !== $matches[1]) {
            return $matches[1];
        }

        $intPattern = '/' . $factName . '\((\d+)\)/';
        if (1 === preg_match($intPattern, $source, $matches) && '' !== $matches[1]) {
            return $matches[1];
        }

        $revocationIds = $biscuit->revocationIds();
        $firstRevocationId = $revocationIds[0] ?? '';

        return '' !== $firstRevocationId ? $firstRevocationId : 'anonymous';
    }
}
