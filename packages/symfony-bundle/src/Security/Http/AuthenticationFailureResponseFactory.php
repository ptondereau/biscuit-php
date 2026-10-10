<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Security\Http;

use Biscuit\BiscuitBundle\Security\Exception\MissingTokenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AuthenticationFailureResponseFactory
{
    public const ERROR_INVALID_TOKEN = 'invalid_token';

    public const ERROR_INVALID_REQUEST = 'invalid_request';

    public function __construct(
        private readonly bool $wwwAuthenticate = true,
        private readonly string $realm = 'api',
    ) {
    }

    public function create(AuthenticationException $exception): Response
    {
        $credentialsPresented = !$exception instanceof MissingTokenException;
        $error = $credentialsPresented ? self::ERROR_INVALID_TOKEN : self::ERROR_INVALID_REQUEST;
        $description = $exception->getMessage();

        if ('' === $description) {
            $description = $exception->getMessageKey();
        }

        $response = new JsonResponse(
            [
                'error' => $error,
                'error_description' => $description,
            ],
            Response::HTTP_UNAUTHORIZED,
        );

        if ($this->wwwAuthenticate) {
            $response->headers->set('WWW-Authenticate', $this->challenge($credentialsPresented, $description));
        }

        return $response;
    }

    private function challenge(bool $credentialsPresented, string $description): string
    {
        $challenge = sprintf('Bearer realm="%s"', $this->escape($this->realm));

        if (!$credentialsPresented) {
            return $challenge;
        }

        return sprintf(
            '%s, error="%s", error_description="%s"',
            $challenge,
            self::ERROR_INVALID_TOKEN,
            $this->escape($description),
        );
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', ' ', ' '], $value);
    }
}
