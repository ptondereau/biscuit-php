<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Store;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use Biscuit\BiscuitBundle\Revocation\Store\PublishingRevocationWriter;
use Biscuit\BiscuitBundle\Tests\Fixtures\CollectingMiddleware;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;

#[CoversClass(PublishingRevocationWriter::class)]
final class PublishingRevocationWriterTest extends TestCase
{
    private CollectingMiddleware $bus;

    protected function setUp(): void
    {
        $this->bus = new CollectingMiddleware();
    }

    #[Test]
    public function itWritesLocallyBeforeItPublishes(): void
    {
        $store = new ArrayRevocationStore();

        $this->writerFor($store)->revoke(new RevocationEntry('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']), 'The node doing the revoking must not wait for its own message.');
    }

    #[Test]
    public function itPublishesARevokeMessageThatRestoresTheEntryItWrote(): void
    {
        $entry = new RevocationEntry(
            revocationId: 'abc',
            expiresAt: new DateTimeImmutable('2026-08-03T12:30:45Z'),
            revokedAt: new DateTimeImmutable('2026-08-01T09:00:00Z'),
            subject: 'alice',
            reason: 'logout',
        );

        $this->writerFor(new ArrayRevocationStore())->revoke($entry);

        self::assertCount(1, $this->bus->messages);
        $message = $this->bus->messages[0];
        self::assertInstanceOf(RevokeToken::class, $message);

        $restored = $message->toEntry();

        self::assertSame('abc', $restored->revocationId);
        self::assertSame('alice', $restored->subject);
        self::assertSame('logout', $restored->reason);
        self::assertEquals($entry->expiresAt, $restored->expiresAt);
        self::assertEquals($entry->revokedAt, $restored->revokedAt);
    }

    #[Test]
    public function itPublishesAnUnrevokeMessage(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('abc')]);

        $this->writerFor($store)->unrevoke('abc');

        self::assertNull($store->findRevoked(['abc']));
        self::assertEquals([new UnrevokeToken('abc')], $this->bus->messages);
    }

    #[Test]
    public function itPinsTheCutoffSoEveryNodePurgesToTheSameInstant(): void
    {
        $this->writerFor(new ArrayRevocationStore())->purgeExpired();

        self::assertCount(1, $this->bus->messages);
        $message = $this->bus->messages[0];
        self::assertInstanceOf(PurgeExpiredRevocations::class, $message);
        self::assertNotSame('', $message->before, 'A null cutoff must be resolved before dispatch, not left to the consumer clock.');
    }

    #[Test]
    public function itPublishesTheCutoffItWasGiven(): void
    {
        $now = new DateTimeImmutable('2026-08-15T00:00:00Z');

        $this->writerFor(new ArrayRevocationStore())->purgeExpired($now);

        $message = $this->bus->messages[0];
        self::assertInstanceOf(PurgeExpiredRevocations::class, $message);
        self::assertEquals($now, $message->toDate());
    }

    #[Test]
    public function itReportsTheLocalPurgeCount(): void
    {
        $store = new ArrayRevocationStore([
            new RevocationEntry('one', new DateTimeImmutable('2026-08-01T00:00:00Z')),
            new RevocationEntry('two', new DateTimeImmutable('2026-08-01T00:00:00Z')),
        ]);

        $purged = $this->writerFor($store)->purgeExpired(new DateTimeImmutable('2026-08-15T00:00:00Z'));

        self::assertSame(2, $purged);
    }

    #[Test]
    public function itPublishesNothingWhenTheLocalRevokeFailed(): void
    {
        $writer = new PublishingRevocationWriter($this->failingWriter(), $this->messageBus());

        try {
            $writer->revoke(new RevocationEntry('abc'));
            self::fail('Expected the failing store to surface.');
        } catch (RevocationStoreUnavailableException) {
        }

        self::assertSame([], $this->bus->messages, 'Broadcasting a revocation the local node could not apply would leave the cluster inconsistent.');
    }

    #[Test]
    public function itPublishesNothingWhenTheLocalUnrevokeFailed(): void
    {
        $writer = new PublishingRevocationWriter($this->failingWriter(), $this->messageBus());

        try {
            $writer->unrevoke('abc');
            self::fail('Expected the failing store to surface.');
        } catch (RevocationStoreUnavailableException) {
        }

        self::assertSame([], $this->bus->messages);
    }

    #[Test]
    public function itPublishesNothingWhenTheLocalPurgeFailed(): void
    {
        $writer = new PublishingRevocationWriter($this->failingWriter(), $this->messageBus());

        try {
            $writer->purgeExpired();
            self::fail('Expected the failing store to surface.');
        } catch (RevocationStoreUnavailableException) {
        }

        self::assertSame([], $this->bus->messages);
    }

    private function writerFor(ArrayRevocationStore $store): PublishingRevocationWriter
    {
        return new PublishingRevocationWriter(
            new ChainRevocationWriter(['store' => $store]),
            $this->messageBus(),
        );
    }

    private function messageBus(): MessageBus
    {
        return new MessageBus([$this->bus]);
    }

    private function failingWriter(): RevocationWriterInterface
    {
        $writer = $this->createMock(RevocationWriterInterface::class);
        $writer->method('revoke')->willThrowException(new RevocationStoreUnavailableException('down'));
        $writer->method('unrevoke')->willThrowException(new RevocationStoreUnavailableException('down'));
        $writer->method('purgeExpired')->willThrowException(new RevocationStoreUnavailableException('down'));

        return $writer;
    }
}
