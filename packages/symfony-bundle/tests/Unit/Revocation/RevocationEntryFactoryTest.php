<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Token\Datalog\AuthorityBlockReader;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RevocationEntryFactory::class)]
final class RevocationEntryFactoryTest extends TestCase
{
    private const AUTHORITY_SOURCE = 'user("alice");' . "\n" . 'check if time($time), $time <= 2026-08-03T12:00:00Z;';

    private RevocationEntryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RevocationEntryFactory(new AuthorityBlockReader());
    }

    #[Test]
    public function itRevokesTheDeepestIdSoAncestorsKeepWorking(): void
    {
        $token = $this->token(['authority', 'first-attenuation', 'second-attenuation']);

        self::assertSame('second-attenuation', $this->factory->fromToken($token)->revocationId);
    }

    #[Test]
    public function itRevokesTheAuthorityIdForANonAttenuatedToken(): void
    {
        $token = $this->token(['authority']);

        self::assertSame('authority', $this->factory->fromToken($token)->revocationId);
    }

    #[Test]
    public function itReadsTheExpirationFromTheAuthorityBlock(): void
    {
        $entry = $this->factory->fromToken($this->token(['authority', 'child']));

        self::assertNotNull($entry->expiresAt);
        self::assertSame('2026-08-03T12:00:00+00:00', $entry->expiresAt->format(DateTimeImmutable::ATOM));
    }

    #[Test]
    public function itLeavesTheExpirationNullWhenTheAuthorityBlockSetsNoDeadline(): void
    {
        $entry = $this->factory->fromToken($this->token(['authority'], 'user("alice");'));

        self::assertNull($entry->expiresAt);
    }

    #[Test]
    public function itReadsTheSubjectFromTheConfiguredFact(): void
    {
        self::assertSame('alice', $this->factory->fromToken($this->token(['authority']))->subject);
    }

    #[Test]
    public function itHonoursACustomUserIdentifierFact(): void
    {
        $factory = new RevocationEntryFactory(new AuthorityBlockReader(), 'account');

        $entry = $factory->fromToken($this->token(['authority'], 'account("acme");'));

        self::assertSame('acme', $entry->subject);
    }

    #[Test]
    public function itRecordsTheReasonAndRevocationTime(): void
    {
        $now = new DateTimeImmutable('2026-08-03T10:00:00Z');

        $entry = $this->factory->fromToken($this->token(['authority']), 'compromised', $now);

        self::assertSame('compromised', $entry->reason);
        self::assertSame($now, $entry->revokedAt);
    }

    #[Test]
    public function itStampsTheCurrentTimeWhenNoneIsGiven(): void
    {
        self::assertNotNull($this->factory->fromToken($this->token(['authority']))->revokedAt);
    }

    #[Test]
    public function itBuildsAnEntryForEveryIdInTheChain(): void
    {
        $entries = $this->factory->allFromToken($this->token(['authority', 'child', 'grandchild']));

        $ids = array_map(static fn (RevocationEntry $entry): string => $entry->revocationId, $entries);

        self::assertSame(['authority', 'child', 'grandchild'], $ids);
    }

    #[Test]
    public function itSharesTheAuthorityMetadataAcrossEveryEntry(): void
    {
        $entries = $this->factory->allFromToken($this->token(['authority', 'child']), 'leaked');

        foreach ($entries as $entry) {
            self::assertSame('alice', $entry->subject);
            self::assertSame('leaked', $entry->reason);
            self::assertNotNull($entry->expiresAt);
        }
    }

    #[Test]
    public function itRefusesATokenThatCarriesNoRevocationIds(): void
    {
        $this->expectException(LogicException::class);

        $this->factory->fromToken($this->token([]));
    }

    #[Test]
    public function itReturnsNoEntriesForATokenThatCarriesNoRevocationIds(): void
    {
        self::assertSame([], $this->factory->allFromToken($this->token([])));
    }

    #[Test]
    public function itAcceptsAnUnverifiedToken(): void
    {
        $token = $this->createMock(UnverifiedBiscuit::class);
        $token->method('revocationIds')->willReturn(['authority', 'child']);
        $token->method('blockSource')->with(0)->willReturn(self::AUTHORITY_SOURCE);

        self::assertSame('child', $this->factory->fromToken($token)->revocationId);
    }

    /**
     * @param list<non-empty-string> $revocationIds
     */
    private function token(array $revocationIds, string $authoritySource = self::AUTHORITY_SOURCE): Biscuit
    {
        $token = $this->createMock(Biscuit::class);
        $token->method('revocationIds')->willReturn($revocationIds);
        $token->method('blockSource')->with(0)->willReturn($authoritySource);

        return $token;
    }
}
