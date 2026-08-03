<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation\Message;

use Biscuit\BiscuitBundle\Event\BiscuitRevocationReceivedEvent;
use Biscuit\BiscuitBundle\Event\BiscuitTokenRevokedEvent;
use Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations;
use Biscuit\BiscuitBundle\Revocation\Message\RevocationPushHandler;
use Biscuit\BiscuitBundle\Revocation\Message\RevokeToken;
use Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationPushOperation;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\ChainRevocationWriter;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(RevocationPushHandler::class)]
final class RevocationPushHandlerTest extends TestCase
{
    #[Test]
    public function itRevokesLocallyWhenItReceivesARevokeMessage(): void
    {
        $store = new ArrayRevocationStore();
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        $handler->handleRevoke(new RevokeToken('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itAppliesTheWholeEntryAndNotJustTheIdentifier(): void
    {
        $store = new ArrayRevocationStore();
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        $handler->handleRevoke(RevokeToken::fromEntry(new RevocationEntry(
            revocationId: 'abc',
            expiresAt: new DateTimeImmutable('2026-08-03T12:30:45Z'),
            subject: 'alice',
            reason: 'logout',
        )));

        $entries = [...$store->all()];

        self::assertCount(1, $entries);
        self::assertSame('alice', $entries[0]->subject);
        self::assertSame('logout', $entries[0]->reason);
        self::assertEquals(new DateTimeImmutable('2026-08-03T12:30:45Z'), $entries[0]->expiresAt);
    }

    #[Test]
    public function itUnrevokesLocallyWhenItReceivesAnUnrevokeMessage(): void
    {
        $store = new ArrayRevocationStore([new RevocationEntry('abc')]);
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        $handler->handleUnrevoke(new UnrevokeToken('abc'));

        self::assertNull($store->findRevoked(['abc']));
    }

    #[Test]
    public function itPurgesToTheCutoffCarriedByTheMessageRatherThanItsOwnClock(): void
    {
        $store = new ArrayRevocationStore([
            new RevocationEntry('expired', new DateTimeImmutable('2026-08-01T00:00:00Z')),
            new RevocationEntry('alive', new DateTimeImmutable('2026-09-01T00:00:00Z')),
        ]);
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        $handler->handlePurge(PurgeExpiredRevocations::fromDate(new DateTimeImmutable('2026-08-15T00:00:00Z')));

        self::assertNull($store->findRevoked(['expired']));
        self::assertSame('alive', $store->findRevoked(['alive']));
    }

    #[Test]
    public function itNeverDispatchesTheRevokedEventThatTheOriginNodeAlreadyDispatched(): void
    {
        $dispatcher = new EventDispatcher();
        $seen = [];
        $dispatcher->addListener(
            BiscuitTokenRevokedEvent::class,
            static function () use (&$seen): void { $seen[] = 'revoked'; },
        );

        $handler = new RevocationPushHandler(
            new ChainRevocationWriter(['store' => new ArrayRevocationStore()]),
            $dispatcher,
        );

        $handler->handleRevoke(new RevokeToken('abc'));

        self::assertSame([], $seen, 'A pushed revocation must not fire the event again on every consuming node.');
    }

    #[Test]
    public function itAnnouncesAReceivedRevocationWithTheAppliedEntry(): void
    {
        $event = $this->captureReceivedEvent(
            static fn (RevocationPushHandler $handler) => $handler->handleRevoke(new RevokeToken('abc', subject: 'alice')),
        );

        self::assertSame(RevocationPushOperation::Revoke, $event->operation);
        self::assertSame('abc', $event->revocationId);
        self::assertSame('alice', $event->entry?->subject);
        self::assertSame(0, $event->purged);
    }

    #[Test]
    public function itAnnouncesAReceivedUnrevocationWithNoEntry(): void
    {
        $event = $this->captureReceivedEvent(
            static fn (RevocationPushHandler $handler) => $handler->handleUnrevoke(new UnrevokeToken('abc')),
        );

        self::assertSame(RevocationPushOperation::Unrevoke, $event->operation);
        self::assertSame('abc', $event->revocationId);
        self::assertNull($event->entry);
    }

    #[Test]
    public function itAnnouncesAReceivedPurgeWithTheLocalCount(): void
    {
        $store = new ArrayRevocationStore([
            new RevocationEntry('one', new DateTimeImmutable('2026-08-01T00:00:00Z')),
            new RevocationEntry('two', new DateTimeImmutable('2026-08-01T00:00:00Z')),
        ]);

        $event = $this->captureReceivedEvent(
            static fn (RevocationPushHandler $handler) => $handler->handlePurge(
                PurgeExpiredRevocations::fromDate(new DateTimeImmutable('2026-08-15T00:00:00Z')),
            ),
            $store,
        );

        self::assertSame(RevocationPushOperation::Purge, $event->operation);
        self::assertSame(2, $event->purged);
        self::assertNull($event->revocationId);
    }

    #[Test]
    public function itStillAppliesTheChangeWithoutADispatcherOrALogger(): void
    {
        $store = new ArrayRevocationStore();
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        $handler->handleRevoke(new RevokeToken('abc'));

        self::assertSame('abc', $store->findRevoked(['abc']));
    }

    #[Test]
    public function itWritesNothingWhenTheMessageCarriesAnEmptyRevocationId(): void
    {
        $store = new ArrayRevocationStore();
        $handler = new RevocationPushHandler(new ChainRevocationWriter(['store' => $store]));

        try {
            $handler->handleRevoke(new RevokeToken(''));
            self::fail('Expected the malformed message to surface so Messenger can retry or dead-letter it.');
        } catch (InvalidArgumentException) {
        }

        self::assertSame([], [...$store->all()]);
    }

    /**
     * @param callable(RevocationPushHandler):void $act
     */
    private function captureReceivedEvent(
        callable $act,
        ?ArrayRevocationStore $store = null,
    ): BiscuitRevocationReceivedEvent {
        $dispatcher = new EventDispatcher();
        $received = [];
        $dispatcher->addListener(
            BiscuitRevocationReceivedEvent::class,
            static function (BiscuitRevocationReceivedEvent $event) use (&$received): void { $received[] = $event; },
        );

        $act(new RevocationPushHandler(
            new ChainRevocationWriter(['store' => $store ?? new ArrayRevocationStore()]),
            $dispatcher,
        ));

        self::assertCount(1, $received);

        return $received[0];
    }
}
