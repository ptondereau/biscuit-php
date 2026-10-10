<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Test;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Revocation\Message\RevocationPushHandler;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test utilities for revocation, including revocations pushed from another instance.
 *
 * Use it in a KernelTestCase or WebTestCase subclass, with a booted kernel.
 *
 * @example
 * ```php
 * class LogoutTest extends WebTestCase
 * {
 *     use BiscuitTestTrait;
 *     use BiscuitRevocationTestTrait;
 *
 *     public function testAnotherInstanceRevokedTheToken(): void
 *     {
 *         $client = static::createClient();
 *         $token = $this->createTestToken('user("alice")');
 *
 *         $this->receiveRevocation($token);
 *
 *         $client->request('GET', '/api/me', [], [], [
 *             'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
 *         ]);
 *
 *         self::assertResponseStatusCodeSame(401);
 *     }
 * }
 * ```
 */
trait BiscuitRevocationTestTrait
{
    /**
     * Applies a revocation as though it arrived from another instance over Messenger.
     *
     * Writes to this instance's stores without publishing anything, which is what a
     * consuming node does. A Biscuit is revoked by its deepest identifier, matching
     * RevocationEntryFactory::fromToken().
     */
    protected function receiveRevocation(
        Biscuit|UnverifiedBiscuit|RevocationEntry|string $target,
        ?string $reason = null,
    ): void {
        $this->biscuitPushHandler()->handleRevoke(RevokeToken::fromEntry(
            $this->biscuitRevocationEntry($target, $reason),
        ));
    }

    /**
     * Applies an unrevocation as though it arrived from another instance.
     */
    protected function receiveUnrevocation(string $revocationId): void
    {
        $this->biscuitPushHandler()->handleUnrevoke(new UnrevokeToken($revocationId));
    }

    protected function assertTokenRevoked(Biscuit|UnverifiedBiscuit|string $target, string $message = ''): void
    {
        self::assertTrue(
            $this->biscuitRevocationChecker()->checkIds($this->biscuitRevocationIds($target))->isRevoked(),
            '' !== $message ? $message : 'Expected the token to be revoked on this instance.',
        );
    }

    protected function assertTokenNotRevoked(Biscuit|UnverifiedBiscuit|string $target, string $message = ''): void
    {
        self::assertFalse(
            $this->biscuitRevocationChecker()->checkIds($this->biscuitRevocationIds($target))->isRevoked(),
            '' !== $message ? $message : 'Expected the token to be accepted on this instance.',
        );
    }

    private function biscuitRevocationEntry(
        Biscuit|UnverifiedBiscuit|RevocationEntry|string $target,
        ?string $reason,
    ): RevocationEntry {
        if ($target instanceof RevocationEntry) {
            return $target;
        }

        if (\is_string($target)) {
            if ('' === $target) {
                throw new LogicException('Cannot revoke an empty revocation identifier.');
            }

            return new RevocationEntry(revocationId: $target, reason: $reason);
        }

        return $this->biscuitService(RevocationEntryFactory::class)->fromToken($target, $reason);
    }

    /**
     * @return list<non-empty-string>
     */
    private function biscuitRevocationIds(Biscuit|UnverifiedBiscuit|string $target): array
    {
        if (!\is_string($target)) {
            return $target->revocationIds();
        }

        if ('' === $target) {
            throw new LogicException('Cannot check an empty revocation identifier.');
        }

        return [$target];
    }

    private function biscuitPushHandler(): RevocationPushHandler
    {
        return $this->biscuitService(RevocationPushHandler::class);
    }

    private function biscuitRevocationChecker(): RevocationChecker
    {
        return $this->biscuitService(RevocationChecker::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    private function biscuitService(string $id): object
    {
        $container = static::getContainer();

        if (!$container->has($id)) {
            throw new LogicException(sprintf('Service "%s" is not available. Enable biscuit.revocation, and biscuit.revocation.push for the receive helpers.', $id));
        }

        $service = $container->get($id);

        if (!$service instanceof $id) {
            throw new LogicException(sprintf('Service "%s" did not resolve to an instance of that type.', $id));
        }

        return $service;
    }

    abstract protected static function getContainer(): ContainerInterface;
}
