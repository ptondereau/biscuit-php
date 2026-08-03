<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Revocation;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Event\BiscuitRevocationCheckedEvent;
use Biscuit\BiscuitBundle\Event\BiscuitRevocationDegradedEvent;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationEventPolicy;
use Biscuit\BiscuitBundle\Revocation\RevocationFailurePolicy;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(RevocationChecker::class)]
final class RevocationCheckerTest extends TestCase
{
    #[Test]
    public function itReportsATokenAsNotRevokedWhenNoStoreMatches(): void
    {
        $checker = new RevocationChecker([new ArrayRevocationStore()], RevocationFailurePolicy::Deny);

        $result = $checker->checkIds(['abc', 'def']);

        self::assertFalse($result->isRevoked());
        self::assertNull($result->revokedId);
        self::assertNull($result->store);
        self::assertSame(['abc', 'def'], $result->checkedIds);
    }

    #[Test]
    public function itReportsTheMatchedIdAndStoreName(): void
    {
        $checker = new RevocationChecker(
            ['cache' => new ArrayRevocationStore([new RevocationEntry('def')])],
            RevocationFailurePolicy::Deny,
        );

        $result = $checker->checkIds(['abc', 'def']);

        self::assertTrue($result->isRevoked());
        self::assertSame('def', $result->revokedId);
        self::assertSame('cache', $result->store);
    }

    #[Test]
    public function itStopsAtTheFirstStoreThatMatches(): void
    {
        $second = $this->createMock(RevocationStoreInterface::class);
        $second->expects(self::never())->method('findRevoked');

        $checker = new RevocationChecker(
            [
                'first' => new ArrayRevocationStore([new RevocationEntry('abc')]),
                'second' => $second,
            ],
            RevocationFailurePolicy::Deny,
        );

        self::assertSame('first', $checker->checkIds(['abc'])->store);
    }

    #[Test]
    public function itQueriesStoresInTheGivenOrder(): void
    {
        $queried = [];

        $checker = new RevocationChecker(
            [
                'first' => $this->recordingStore('first', $queried),
                'second' => $this->recordingStore('second', $queried),
            ],
            RevocationFailurePolicy::Deny,
        );

        $checker->checkIds(['abc']);

        self::assertSame(['first', 'second'], $queried);
    }

    #[Test]
    public function itSkipsEveryStoreWhenTheTokenHasNoRevocationIds(): void
    {
        $store = $this->createMock(RevocationStoreInterface::class);
        $store->expects(self::never())->method('findRevoked');

        $checker = new RevocationChecker(['store' => $store], RevocationFailurePolicy::Deny);

        self::assertFalse($checker->checkIds([])->isRevoked());
    }

    #[Test]
    public function itRecordsAnOutcomePerQueriedStore(): void
    {
        $checker = new RevocationChecker(
            [
                'static' => new ArrayRevocationStore(),
                'cache' => new ArrayRevocationStore([new RevocationEntry('abc')]),
            ],
            RevocationFailurePolicy::Deny,
        );

        $outcomes = $checker->checkIds(['abc'])->outcomes;

        self::assertCount(2, $outcomes);
        self::assertSame('static', $outcomes[0]->store);
        self::assertNull($outcomes[0]->revokedId);
        self::assertSame('cache', $outcomes[1]->store);
        self::assertSame('abc', $outcomes[1]->revokedId);
    }

    #[Test]
    public function itMarksAVerifiedTokenAsVerified(): void
    {
        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('revocationIds')->willReturn(['abc']);

        $checker = new RevocationChecker([new ArrayRevocationStore()], RevocationFailurePolicy::Deny);

        self::assertTrue($checker->check($biscuit)->verified);
    }

    #[Test]
    public function itMarksAnUnverifiedTokenAsUnverified(): void
    {
        $unverified = $this->createMock(UnverifiedBiscuit::class);
        $unverified->method('revocationIds')->willReturn(['abc']);

        $checker = new RevocationChecker([new ArrayRevocationStore()], RevocationFailurePolicy::Deny);

        self::assertFalse($checker->check($unverified)->verified);
    }

    #[Test]
    public function itRethrowsWhenAStoreFailsAndThePolicyIsDeny(): void
    {
        $checker = new RevocationChecker(
            ['broken' => $this->failingStore()],
            RevocationFailurePolicy::Deny,
        );

        $this->expectException(RevocationStoreUnavailableException::class);

        $checker->checkIds(['abc']);
    }

    #[Test]
    public function itSkipsAFailingStoreAndMarksTheCheckDegradedWhenThePolicyIsAllow(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $checker = new RevocationChecker(
            [
                'broken' => $this->failingStore(),
                'healthy' => new ArrayRevocationStore([new RevocationEntry('abc')]),
            ],
            RevocationFailurePolicy::Allow,
            RevocationEventPolicy::OnRevoke,
            null,
            $logger,
        );

        $result = $checker->checkIds(['abc']);

        self::assertTrue($result->degraded);
        self::assertSame('healthy', $result->store);
        self::assertSame('down', $result->outcomes[0]->error);
    }

    #[Test]
    public function itDispatchesADegradedEventBeforeRethrowingUnderDeny(): void
    {
        $events = [];

        $checker = new RevocationChecker(
            ['broken' => $this->failingStore()],
            RevocationFailurePolicy::Deny,
            RevocationEventPolicy::Always,
            $this->recordingDispatcher($events),
        );

        try {
            $checker->checkIds(['abc']);
        } catch (RevocationStoreUnavailableException) {
        }

        self::assertCount(1, $events);
        self::assertInstanceOf(BiscuitRevocationDegradedEvent::class, $events[0]);
        self::assertSame('broken', $events[0]->store);
        self::assertSame(RevocationFailurePolicy::Deny, $events[0]->policy);
    }

    #[Test]
    public function itDoesNotDispatchACheckEventForAValidTokenUnderTheDefaultPolicy(): void
    {
        $events = [];

        $checker = new RevocationChecker(
            [new ArrayRevocationStore()],
            RevocationFailurePolicy::Deny,
            RevocationEventPolicy::OnRevoke,
            $this->recordingDispatcher($events),
        );

        $checker->checkIds(['abc']);

        self::assertSame([], $events);
    }

    #[Test]
    public function itDispatchesACheckEventForARevokedTokenUnderTheDefaultPolicy(): void
    {
        $events = [];

        $checker = new RevocationChecker(
            [new ArrayRevocationStore([new RevocationEntry('abc')])],
            RevocationFailurePolicy::Deny,
            RevocationEventPolicy::OnRevoke,
            $this->recordingDispatcher($events),
        );

        $checker->checkIds(['abc']);

        self::assertCount(1, $events);
        self::assertInstanceOf(BiscuitRevocationCheckedEvent::class, $events[0]);
        self::assertSame('abc', $events[0]->result->revokedId);
    }

    #[Test]
    public function itDispatchesEveryCheckUnderTheAlwaysPolicy(): void
    {
        $events = [];

        $checker = new RevocationChecker(
            [new ArrayRevocationStore()],
            RevocationFailurePolicy::Deny,
            RevocationEventPolicy::Always,
            $this->recordingDispatcher($events),
        );

        $checker->checkIds(['abc']);

        self::assertCount(1, $events);
        self::assertInstanceOf(BiscuitRevocationCheckedEvent::class, $events[0]);
    }

    #[Test]
    public function itDispatchesNothingUnderTheNeverPolicy(): void
    {
        $events = [];

        $checker = new RevocationChecker(
            [new ArrayRevocationStore([new RevocationEntry('abc')])],
            RevocationFailurePolicy::Deny,
            RevocationEventPolicy::Never,
            $this->recordingDispatcher($events),
        );

        $checker->checkIds(['abc']);

        self::assertSame([], $events);
    }

    private function failingStore(): RevocationStoreInterface
    {
        $store = $this->createMock(RevocationStoreInterface::class);
        $store->method('findRevoked')->willThrowException(new RevocationStoreUnavailableException('down'));

        return $store;
    }

    /**
     * @param list<string> $queried
     */
    private function recordingStore(string $name, array &$queried): RevocationStoreInterface
    {
        $store = $this->createMock(RevocationStoreInterface::class);
        $store->method('findRevoked')->willReturnCallback(
            static function () use ($name, &$queried): ?string {
                $queried[] = $name;

                return null;
            },
        );

        return $store;
    }

    /**
     * @param list<object> $events
     */
    private function recordingDispatcher(array &$events): EventDispatcherInterface
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(
            static function (object $event) use (&$events): object {
                $events[] = $event;

                return $event;
            },
        );

        return $eventDispatcher;
    }
}
