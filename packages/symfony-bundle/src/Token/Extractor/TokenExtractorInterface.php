<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

interface TokenExtractorInterface
{
    public function extract(Request $request): ?string;

    public function supports(Request $request): bool;
}
