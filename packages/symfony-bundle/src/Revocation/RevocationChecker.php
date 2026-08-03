<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Event\BiscuitRevocationCheckedEvent;
use Biscuit\BiscuitBundle\Event\BiscuitRevocationDegradedEvent;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RevocationChecker implements RevocationCheckerInterface
{
    private readonly RevocationFailurePolicy $failurePolicy;

    private readonly RevocationEventPolicy $eventPolicy;

    /**
     * @param iterable<array-key, RevocationStoreInterface> $stores ordered by descending tag
     */
    public function __construct(
        private readonly iterable $stores,
        RevocationFailurePolicy|string $failurePolicy,
        RevocationEventPolicy|string $eventPolicy = RevocationEventPolicy::OnRevoke,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->failurePolicy = \is_string($failurePolicy)
            ? RevocationFailurePolicy::from($failurePolicy)
            : $failurePolicy;

        $this->eventPolicy = \is_string($eventPolicy)
            ? RevocationEventPolicy::from($eventPolicy)
            : $eventPolicy;
    }

    public function check(Biscuit|UnverifiedBiscuit $token): RevocationResult
    {
        return $this->checkIds($token->revocationIds(), $token instanceof Biscuit);
    }

    public function checkIds(array $revocationIds, bool $verified = false): RevocationResult
    {
        $startedAt = hrtime(true);

        $revokedId = null;
        $matchedStore = null;
        $degraded = false;
        $outcomes = [];

        if ([] !== $revocationIds) {
            foreach ($this->stores as $name => $store) {
                $storeName = (string) $name;
                $storeStartedAt = hrtime(true);

                try {
                    $found = $store->findRevoked($revocationIds);
                } catch (RevocationStoreUnavailableException $e) {
                    $outcomes[] = new RevocationStoreOutcome(
                        $storeName,
                        null,
                        self::elapsedMs($storeStartedAt),
                        $e->getMessage(),
                    );

                    $this->eventDispatcher?->dispatch(new BiscuitRevocationDegradedEvent(
                        $storeName,
                        $e,
                        $this->failurePolicy,
                    ));

                    if (RevocationFailurePolicy::Deny === $this->failurePolicy) {
                        throw $e;
                    }

                    $degraded = true;
                    $this->logger?->error(
                        'Revocation store "{store}" is unavailable; treating the token as not revoked.',
                        ['store' => $storeName, 'exception' => $e],
                    );

                    continue;
                }

                $outcomes[] = new RevocationStoreOutcome(
                    $storeName,
                    $found,
                    self::elapsedMs($storeStartedAt),
                );

                if (null !== $found) {
                    $revokedId = $found;
                    $matchedStore = $storeName;

                    break;
                }
            }
        }

        $result = new RevocationResult(
            $revocationIds,
            $revokedId,
            $matchedStore,
            self::elapsedMs($startedAt),
            $verified,
            $degraded,
            $outcomes,
        );

        if ($this->shouldDispatch($result)) {
            $this->eventDispatcher?->dispatch(new BiscuitRevocationCheckedEvent($result));
        }

        return $result;
    }

    private function shouldDispatch(RevocationResult $result): bool
    {
        return match ($this->eventPolicy) {
            RevocationEventPolicy::Never => false,
            RevocationEventPolicy::OnRevoke => $result->isRevoked(),
            RevocationEventPolicy::Always => true,
        };
    }

    private static function elapsedMs(float|int $startedAt): float
    {
        return (hrtime(true) - $startedAt) / 1_000_000;
    }
}
