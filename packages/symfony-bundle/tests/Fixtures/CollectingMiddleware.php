<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Fixtures;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class CollectingMiddleware implements MiddlewareInterface
{
    /**
     * @var list<object>
     */
    public array $messages = [];

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->messages[] = $envelope->getMessage();

        return $envelope;
    }
}
