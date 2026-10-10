<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Token\Datalog\AuthorityBlockReader;
use DateTimeImmutable;
use LogicException;

final class RevocationEntryFactory
{
    /**
     * @param non-empty-string $userIdentifierFact
     */
    public function __construct(
        private readonly AuthorityBlockReader $authorityBlockReader,
        private readonly string $userIdentifierFact = 'user',
    ) {
    }

    public function fromToken(
        Biscuit|UnverifiedBiscuit $token,
        ?string $reason = null,
        ?DateTimeImmutable $now = null,
    ): RevocationEntry {
        $revocationIds = $token->revocationIds();

        if ([] === $revocationIds) {
            throw new LogicException('Cannot revoke a token that carries no revocation identifiers.');
        }

        return $this->build($revocationIds[\count($revocationIds) - 1], $token, $reason, $now);
    }

    /**
     * @return list<RevocationEntry>
     */
    public function allFromToken(
        Biscuit|UnverifiedBiscuit $token,
        ?string $reason = null,
        ?DateTimeImmutable $now = null,
    ): array {
        $entries = [];

        foreach ($token->revocationIds() as $revocationId) {
            $entries[] = $this->build($revocationId, $token, $reason, $now);
        }

        return $entries;
    }

    /**
     * @param non-empty-string $revocationId
     */
    private function build(
        string $revocationId,
        Biscuit|UnverifiedBiscuit $token,
        ?string $reason,
        ?DateTimeImmutable $now,
    ): RevocationEntry {
        $authoritySource = $token->blockSource(0);

        return new RevocationEntry(
            revocationId: $revocationId,
            expiresAt: $this->authorityBlockReader->readExpiry($authoritySource),
            revokedAt: $now ?? new DateTimeImmutable(),
            subject: $this->authorityBlockReader->readFact($authoritySource, $this->userIdentifierFact),
            reason: $reason,
        );
    }
}
