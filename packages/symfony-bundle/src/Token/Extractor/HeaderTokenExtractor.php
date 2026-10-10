<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class HeaderTokenExtractor implements TokenExtractorInterface
{
    private const AUTHORIZATION_HEADER = 'Authorization';
    private const BEARER_PREFIX = 'Bearer ';

    public function extract(Request $request): ?string
    {
        if (!$this->supports($request)) {
            return null;
        }

        $authHeader = $request->headers->get(self::AUTHORIZATION_HEADER);
        \assert(null !== $authHeader);

        return substr($authHeader, \strlen(self::BEARER_PREFIX));
    }

    public function supports(Request $request): bool
    {
        $authHeader = $request->headers->get(self::AUTHORIZATION_HEADER);

        if (null === $authHeader) {
            return false;
        }

        return str_starts_with($authHeader, self::BEARER_PREFIX);
    }
}
