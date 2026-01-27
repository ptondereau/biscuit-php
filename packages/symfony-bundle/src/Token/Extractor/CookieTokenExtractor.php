<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class CookieTokenExtractor implements TokenExtractorInterface
{
    public function __construct(
        private readonly string $cookieName,
    ) {
    }

    public function extract(Request $request): ?string
    {
        if (!$this->supports($request)) {
            return null;
        }

        return $request->cookies->get($this->cookieName);
    }

    public function supports(Request $request): bool
    {
        return $request->cookies->has($this->cookieName);
    }
}
