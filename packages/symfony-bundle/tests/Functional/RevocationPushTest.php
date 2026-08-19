<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevocationPushHandler;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\PublishingRevocationWriter;
use Biscuit\BiscuitBundle\Test\BiscuitRevocationTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class RevocationPushTest extends KernelTestCase
{
    use BiscuitRevocationTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itHandsUserlandThePublisherOncePushIsEnabled(): void
    {
        $this->bootPushKernel();

        self::assertInstanceOf(PublishingRevocationWriter::class, $this->writer());
    }

    #[Test]
    public function itRevokesLocallyAndQueuesTheBroadcastInOneCall(): void
    {
        $this->bootPushKernel();

        $this->writer()->revoke(new RevocationEntry('abc', subject: 'alice', reason: 'logout'));

        self::assertTrue($this->checker()->checkIds(['abc'])->isRevoked());

        $sent = $this->transport()->getSent();

        self::assertCount(1, $sent);
        $message = $sent[0]->getMessage();
        self::assertInstanceOf(RevokeToken::class, $message);
        self::assertSame('abc', $message->revocationId);
        self::assertSame('alice', $message->subject);
        self::assertSame('logout', $message->reason);
    }

    #[Test]
    public function itQueuesAnUnrevokeBroadcast(): void
    {
        $this->bootPushKernel();

        $this->writer()->unrevoke('abc');

        $sent = $this->transport()->getSent();

        self::assertCount(1, $sent);
        self::assertInstanceOf(UnrevokeToken::class, $sent[0]->getMessage());
    }

    #[Test]
    public function itQueuesAPurgeBroadcastWithAResolvedCutoff(): void
    {
        $this->bootPushKernel();

        $this->writer()->purgeExpired();

        $sent = $this->transport()->getSent();

        self::assertCount(1, $sent);
        $message = $sent[0]->getMessage();
        self::assertInstanceOf(PurgeExpiredRevocations::class, $message);
        self::assertEqualsWithDelta(time(), $message->toDate()->getTimestamp(), 5);
    }

    #[Test]
    public function itNeverRepublishesAMessageItConsumed(): void
    {
        $this->bootPushKernel();

        $handler = $this->handler();
        $handler->handleRevoke(new RevokeToken('def', expiresAt: '2027-01-01T00:00:00.000+00:00'));

        self::assertTrue(
            $this->checker()->checkIds(['def'])->isRevoked(),
            'A consumed message must reach the local store.',
        );
        self::assertSame(
            [],
            $this->transport()->getSent(),
            'A consumed message must not be broadcast again, or the cluster loops forever.',
        );
    }

    #[Test]
    public function itLetsAConsumedUnrevokeUndoAPushedRevocation(): void
    {
        $this->bootPushKernel();

        $handler = $this->handler();
        $handler->handleRevoke(new RevokeToken('ghi'));
        $handler->handleUnrevoke(new UnrevokeToken('ghi'));

        self::assertFalse($this->checker()->checkIds(['ghi'])->isRevoked());
        self::assertSame([], $this->transport()->getSent());
    }

    #[Test]
    public function itLetsAConsumedPurgeDropAnExpiredEntry(): void
    {
        $this->bootPushKernel();

        $handler = $this->handler();
        $handler->handleRevoke(RevokeToken::fromEntry(
            new RevocationEntry('jkl', new DateTimeImmutable('2026-08-01T00:00:00Z')),
        ));

        self::assertTrue($this->checker()->checkIds(['jkl'])->isRevoked());

        $handler->handlePurge(PurgeExpiredRevocations::fromDate(new DateTimeImmutable('2026-08-15T00:00:00Z')));

        self::assertFalse($this->checker()->checkIds(['jkl'])->isRevoked());
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

    private function writer(): RevocationWriterInterface
    {
        $writer = self::getContainer()->get(RevocationWriterInterface::class);

        self::assertInstanceOf(RevocationWriterInterface::class, $writer);

        return $writer;
    }

    private function handler(): RevocationPushHandler
    {
        $handler = self::getContainer()->get(RevocationPushHandler::class);

        self::assertInstanceOf(RevocationPushHandler::class, $handler);

        return $handler;
    }

    private function checker(): RevocationChecker
    {
        $checker = self::getContainer()->get(RevocationChecker::class);

        self::assertInstanceOf(RevocationChecker::class, $checker);

        return $checker;
    }

    private function transport(): InMemoryTransport
    {
        $transport = self::getContainer()->get('messenger.transport.biscuit_revocation');

        self::assertInstanceOf(InMemoryTransport::class, $transport);

        return $transport;
    }
}
