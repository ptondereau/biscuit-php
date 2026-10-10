<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\DataCollector;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Event\BiscuitTokenAttenuatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BiscuitDataCollector::class)]
final class BiscuitDataCollectorTest extends TestCase
{
    #[Test]
    public function itExtendsAbstractDataCollector(): void
    {
        $collector = new BiscuitDataCollector();

        self::assertInstanceOf(AbstractDataCollector::class, $collector);
    }

    #[Test]
    public function itHasCorrectName(): void
    {
        $collector = new BiscuitDataCollector();

        self::assertSame('biscuit', $collector->getName());
    }

    #[Test]
    public function itReturnsCorrectTemplate(): void
    {
        self::assertSame('@Biscuit/data_collector/biscuit.html.twig', BiscuitDataCollector::getTemplate());
    }

    #[Test]
    public function itCollectsWithoutToken(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $collector->collect($request, $response);

        self::assertFalse($collector->hasToken());
        self::assertSame(0, $collector->getBlockCount());
        self::assertSame([], $collector->getBlocks());
        self::assertSame([], $collector->getRevocationIds());
        self::assertSame([], $collector->getPolicyChecks());
        self::assertSame(0, $collector->getPolicyCheckCount());
        self::assertSame(0, $collector->getPassedChecks());
        self::assertSame(0, $collector->getFailedChecks());
    }

    #[Test]
    public function itCollectsWithToken(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('blockCount')->willReturn(2);
        $biscuit->method('blockSource')
            ->willReturnCallback(static fn (int $index): string => match ($index) {
                0 => 'user("alice");',
                1 => 'check if time($time), $time < 2030-01-01T00:00:00Z;',
                default => '',
            });
        $biscuit->method('revocationIds')->willReturn(['abc123', 'def456']);

        $collector->setBiscuit($biscuit);
        $collector->collect($request, $response);

        self::assertTrue($collector->hasToken());
        self::assertSame(2, $collector->getBlockCount());
        self::assertSame(['user("alice");', 'check if time($time), $time < 2030-01-01T00:00:00Z;'], $collector->getBlocks());
        self::assertSame(['abc123', 'def456'], $collector->getRevocationIds());
    }

    #[Test]
    public function itRecordsPolicyChecks(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('blockCount')->willReturn(1);
        $biscuit->method('blockSource')->willReturn('user("alice");');
        $biscuit->method('revocationIds')->willReturn([]);

        $collector->setBiscuit($biscuit);
        $collector->recordPolicyCheck('admin_access', true);
        $collector->recordPolicyCheck('read_resource', false, ['resource' => 'article-123']);
        $collector->recordPolicyCheck('write_resource', true, ['resource' => 'article-456', 'action' => 'update']);
        $collector->collect($request, $response);

        self::assertSame(3, $collector->getPolicyCheckCount());
        self::assertSame(2, $collector->getPassedChecks());
        self::assertSame(1, $collector->getFailedChecks());

        $checks = $collector->getPolicyChecks();
        self::assertCount(3, $checks);

        self::assertSame('admin_access', $checks[0]['policy']);
        self::assertTrue($checks[0]['result']);
        self::assertSame([], $checks[0]['params']);

        self::assertSame('read_resource', $checks[1]['policy']);
        self::assertFalse($checks[1]['result']);
        self::assertSame(['resource' => 'article-123'], $checks[1]['params']);

        self::assertSame('write_resource', $checks[2]['policy']);
        self::assertTrue($checks[2]['result']);
        self::assertSame(['resource' => 'article-456', 'action' => 'update'], $checks[2]['params']);
    }

    #[Test]
    public function itResetsCorrectly(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('blockCount')->willReturn(1);
        $biscuit->method('blockSource')->willReturn('user("alice");');
        $biscuit->method('revocationIds')->willReturn(['abc']);

        $collector->setBiscuit($biscuit);
        $collector->recordPolicyCheck('test', true);
        $collector->collect($request, $response);

        self::assertTrue($collector->hasToken());
        self::assertSame(1, $collector->getPolicyCheckCount());

        $collector->reset();
        $collector->collect($request, $response);

        self::assertFalse($collector->hasToken());
        self::assertSame(0, $collector->getBlockCount());
        self::assertSame([], $collector->getBlocks());
        self::assertSame([], $collector->getRevocationIds());
        self::assertSame([], $collector->getPolicyChecks());
        self::assertSame(0, $collector->getPolicyCheckCount());
    }

    #[Test]
    public function itHandlesTokenWithNoBlocks(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('blockCount')->willReturn(0);
        $biscuit->method('revocationIds')->willReturn([]);

        $collector->setBiscuit($biscuit);
        $collector->collect($request, $response);

        self::assertTrue($collector->hasToken());
        self::assertSame(0, $collector->getBlockCount());
        self::assertSame([], $collector->getBlocks());
    }

    #[Test]
    public function itCollectsWithException(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();
        $exception = new RuntimeException('Test exception');

        $biscuit = $this->createMock(Biscuit::class);
        $biscuit->method('blockCount')->willReturn(1);
        $biscuit->method('blockSource')->willReturn('user("alice");');
        $biscuit->method('revocationIds')->willReturn([]);

        $collector->setBiscuit($biscuit);
        $collector->collect($request, $response, $exception);

        self::assertTrue($collector->hasToken());
    }

    #[Test]
    public function itCountsAllPassedChecks(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $collector->recordPolicyCheck('policy1', true);
        $collector->recordPolicyCheck('policy2', true);
        $collector->recordPolicyCheck('policy3', true);
        $collector->collect($request, $response);

        self::assertSame(3, $collector->getPassedChecks());
        self::assertSame(0, $collector->getFailedChecks());
    }

    #[Test]
    public function itRecordsAttenuationFromEvent(): void
    {
        $collector = new BiscuitDataCollector();

        $parent = $this->createMock(Biscuit::class);
        $parent->method('revocationIds')->willReturn(['parent-rev']);
        $parent->method('toBase64')->willReturn('PARENT');

        $child = $this->createMock(Biscuit::class);
        $child->method('revocationIds')->willReturn(['parent-rev', 'child-rev']);
        $child->method('toBase64')->willReturn('PARENT_PLUS_BLOCK');

        $event = new BiscuitTokenAttenuatedEvent($parent, 'check if operation("read")', $child);
        $collector->onAttenuated($event);

        $collector->collect(new Request(), new Response());

        self::assertSame(1, $collector->getAttenuationCount());

        $records = $collector->getAttenuations();
        self::assertCount(1, $records);
        self::assertSame(['parent-rev'], $records[0]['parentRevocationIds']);
        self::assertSame(['parent-rev', 'child-rev'], $records[0]['childRevocationIds']);
        self::assertSame('check if operation("read")', $records[0]['blockSource']);
        self::assertSame(\strlen('PARENT_PLUS_BLOCK') - \strlen('PARENT'), $records[0]['sizeDelta']);
    }

    #[Test]
    public function itSubscribesToAttenuatedEvent(): void
    {
        self::assertInstanceOf(EventSubscriberInterface::class, new BiscuitDataCollector());

        $events = BiscuitDataCollector::getSubscribedEvents();
        self::assertArrayHasKey(BiscuitTokenAttenuatedEvent::class, $events);
    }

    #[Test]
    public function resetClearsRecordedAttenuations(): void
    {
        $collector = new BiscuitDataCollector();

        $parent = $this->createMock(Biscuit::class);
        $parent->method('revocationIds')->willReturn([]);
        $parent->method('toBase64')->willReturn('PARENT');
        $child = $this->createMock(Biscuit::class);
        $child->method('revocationIds')->willReturn([]);
        $child->method('toBase64')->willReturn('PARENT_PLUS');

        $collector->onAttenuated(new BiscuitTokenAttenuatedEvent($parent, 'check if true', $child));
        $collector->collect(new Request(), new Response());
        self::assertSame(1, $collector->getAttenuationCount());

        $collector->reset();
        $collector->collect(new Request(), new Response());

        self::assertSame(0, $collector->getAttenuationCount());
        self::assertSame([], $collector->getAttenuations());
    }

    #[Test]
    public function itCountsAllFailedChecks(): void
    {
        $collector = new BiscuitDataCollector();
        $request = new Request();
        $response = new Response();

        $collector->recordPolicyCheck('policy1', false);
        $collector->recordPolicyCheck('policy2', false);
        $collector->collect($request, $response);

        self::assertSame(0, $collector->getPassedChecks());
        self::assertSame(2, $collector->getFailedChecks());
    }
}
