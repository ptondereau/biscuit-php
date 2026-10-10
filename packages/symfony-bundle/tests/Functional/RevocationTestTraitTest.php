<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Test\BiscuitRevocationTestTrait;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class RevocationTestTraitTest extends KernelTestCase
{
    use BiscuitRevocationTestTrait;
    use BiscuitTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itRevokesATokenByItsDeepestIdentifier(): void
    {
        $this->bootPushKernel();
        $token = $this->createTestToken('user("alice")');

        $this->receiveRevocation($token);

        $this->assertTokenRevoked($token);
    }

    #[Test]
    public function itLeavesAnUnrelatedTokenAlone(): void
    {
        $this->bootPushKernel();

        $this->receiveRevocation($this->createTestToken('user("alice")'));

        $this->assertTokenNotRevoked($this->createTestToken('user("bob")'));
    }

    #[Test]
    public function itRevokesARawIdentifier(): void
    {
        $this->bootPushKernel();

        $this->receiveRevocation('deadbeef');

        $this->assertTokenRevoked('deadbeef');
    }

    #[Test]
    public function itRevokesAPreparedEntry(): void
    {
        $this->bootPushKernel();

        $this->receiveRevocation(new RevocationEntry('abc', subject: 'alice', reason: 'logout'));

        $this->assertTokenRevoked('abc');
    }

    #[Test]
    public function itRecordsTheReasonItWasGiven(): void
    {
        $this->bootPushKernel();
        $token = $this->createTestToken('user("alice")');

        $this->receiveRevocation($token, reason: 'password_reset');

        self::assertSame('password_reset', $this->storedEntries()[0]->reason);
    }

    #[Test]
    public function itUndoesARevocation(): void
    {
        $this->bootPushKernel();
        $token = $this->createTestToken('user("alice")');
        $deepest = $token->revocationIds()[\count($token->revocationIds()) - 1];

        $this->receiveRevocation($token);
        $this->receiveUnrevocation($deepest);

        $this->assertTokenNotRevoked($token);
    }

    #[Test]
    public function itPublishesNothingSoTheHelperCannotBeMistakenForRevoking(): void
    {
        $this->bootPushKernel();

        $this->receiveRevocation($this->createTestToken('user("alice")'));

        self::assertSame(
            [],
            $this->transport()->getSent(),
            'receiveRevocation() simulates an inbound push, so it must not broadcast.',
        );
    }

    #[Test]
    public function itRefusesAnEmptyIdentifierRatherThanRevokingNothingQuietly(): void
    {
        $this->bootPushKernel();

        $this->expectException(LogicException::class);

        $this->receiveRevocation('');
    }

    #[Test]
    public function itExplainsItselfWhenPushIsNotEnabled(): void
    {
        TestKernel::configure(biscuitConfig: [
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['in_memory' => ['enabled' => true]],
            ],
        ]);
        self::bootKernel();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/biscuit\.revocation\.push/');

        $this->receiveRevocation('abc');
    }

    #[Test]
    public function itStillChecksTokensWithoutPushEnabled(): void
    {
        TestKernel::configure(biscuitConfig: [
            'revocation' => [
                'enabled' => true,
                'on_unavailable' => 'deny',
                'stores' => ['static' => ['ids' => ['abc']]],
            ],
        ]);
        self::bootKernel();

        $this->assertTokenRevoked('abc');
        $this->assertTokenNotRevoked('def');
    }

    /**
     * @return list<RevocationEntry>
     */
    private function storedEntries(): array
    {
        $store = self::getContainer()->get('biscuit.revocation.store.in_memory');

        self::assertInstanceOf(ArrayRevocationStore::class, $store);

        return array_values([...$store->all()]);
    }

    private function transport(): InMemoryTransport
    {
        $transport = self::getContainer()->get('messenger.transport.biscuit_revocation');

        self::assertInstanceOf(InMemoryTransport::class, $transport);

        return $transport;
    }

    private function bootPushKernel(): void
    {
        TestKernel::configure(
            biscuitConfig: [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['in_memory' => ['enabled' => true]],
                    'push' => ['enabled' => true],
                ],
            ],
            frameworkConfig: [
                'messenger' => [
                    'transports' => ['biscuit_revocation' => 'in-memory://'],
                    'routing' => [
                        RevokeToken::class => 'biscuit_revocation',
                        UnrevokeToken::class => 'biscuit_revocation',
                        PurgeExpiredRevocations::class => 'biscuit_revocation',
                    ],
                ],
            ],
        );

        self::bootKernel();
    }
}
