<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class ChainTokenExtractor implements TokenExtractorInterface
{
    /**
     * @var array<TokenExtractorInterface>
     */
    private readonly array $extractors;

    public function __construct(TokenExtractorInterface ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function extract(Request $request): ?string
    {
        foreach ($this->extractors as $extractor) {
            $token = $extractor->extract($request);

            if (null !== $token) {
                return $token;
            }
        }

        return null;
    }

    public function supports(Request $request): bool
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($request)) {
                return true;
            }
        }

        return false;
    }
}
