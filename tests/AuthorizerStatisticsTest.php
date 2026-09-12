<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Authorizer;
use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\Rule;
use Biscuit\Exception\AuthorizationException;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuthorizerStatisticsTest extends TestCase
{
    public function testFactCountIncludesDerivedFacts(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['ample'])->buildUnauthenticated();

        static::assertSame(1, $authorizer->factCount());
        $authorizer->authorize();
        static::assertSame(2, $authorizer->factCount());
    }

    public function testIterationsCountOnlyPassesProducingFacts(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['ample'])->buildUnauthenticated();

        static::assertSame(0, $authorizer->iterations());
        $authorizer->authorize();
        static::assertSame(1, $authorizer->iterations());
        $authorizer->authorize();
        static::assertSame(1, $authorizer->iterations());
    }

    public function testExecutionTimeIsNullableAndExpressedInSeconds(): void
    {
        $builder = new AuthorizerBuilder('allow if true;');
        $authorizer = $builder->buildUnauthenticated();
        static::assertNull($authorizer->executionTime());

        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $restored = Authorizer::fromBase64Snapshot($snapshots['known_time']);
        static::assertIsFloat($restored->executionTime());
        static::assertEqualsWithDelta(1.234_567_890, $restored->executionTime(), 1e-9);
    }

    public function testReadingStatisticsDoesNotEvaluateTheAuthorizer(): void
    {
        $builder = new AuthorizerBuilder('a(1); b($x) <- a($x); allow if b(1);');
        $authorizer = $builder->buildUnauthenticated();
        $before = $authorizer->rawSnapshot();

        static::assertSame(1, $authorizer->factCount());
        static::assertSame(0, $authorizer->iterations());
        static::assertNull($authorizer->executionTime());
        static::assertSame($before, $authorizer->rawSnapshot());
    }

    public function testExecutionTimeAccumulatesAcrossAuthorizationAndQuery(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['ample'])->buildUnauthenticated();
        $authorizer->authorize();
        $first = $authorizer->executionTime();
        static::assertIsFloat($first);
        static::assertGreaterThanOrEqual(0.0, $first);

        $authorizer->authorize();
        $second = $authorizer->executionTime();
        static::assertGreaterThanOrEqual($first, $second);

        $facts = $authorizer->query(new Rule('result($x) <- b($x)'));
        static::assertCount(1, $facts);
        static::assertGreaterThanOrEqual($second, $authorizer->executionTime());
        static::assertSame(2, $authorizer->factCount());
        static::assertSame(1, $authorizer->iterations());
    }

    public function testDeniedAuthorizationStillRecordsExecutionTime(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['denied'])->buildUnauthenticated();
        try {
            $authorizer->authorize();
            static::fail('Expected the deny policy to reject authorization.');
        } catch (AuthorizationException $exception) {
            static::assertSame('deny', $exception->getMatchedPolicy()?->getKind());
        }

        static::assertIsFloat($authorizer->executionTime());
        static::assertSame(1, $authorizer->factCount());
        static::assertSame(0, $authorizer->iterations());
    }

    #[DataProvider('inferenceLimits')]
    public function testInterruptedInferenceHasPartialCountersButNoTime(string $fixture): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots[$fixture])->buildUnauthenticated();
        try {
            $authorizer->authorize();
            static::fail('Expected the inference limit to reject authorization.');
        } catch (AuthorizationException) {
            static::assertSame(2, $authorizer->factCount());
            static::assertSame(1, $authorizer->iterations());
            static::assertNull($authorizer->executionTime());
        }
    }

    public static function inferenceLimits(): iterable
    {
        yield 'iterations' => ['iterations'];
        yield 'facts' => ['facts'];
        yield 'timeout' => ['inference_timeout'];
    }

    public function testTimeoutAfterInferenceKeepsTheRecordedTime(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot(
            $snapshots['authorization_timeout'],
        )->buildUnauthenticated();
        try {
            $authorizer->authorize();
            static::fail('Expected the zero time limit to reject authorization.');
        } catch (AuthorizationException) {
            static::assertIsFloat($authorizer->executionTime());
            static::assertSame(0, $authorizer->iterations());
        }
    }

    public function testFactCountDistinguishesOrigins(): void
    {
        $root = new KeyPair();
        $builder = new BiscuitBuilder('a(1);');
        $token = $builder->build($root->getPrivateKey());
        $authBuilder = new AuthorizerBuilder('a(1); allow if true;');
        $authorizer = $authBuilder->build($token);

        static::assertSame(2, $authorizer->factCount());
    }

    public function testSnapshotsPreserveStatisticsBeforeEvaluation(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['ample'])->buildUnauthenticated();

        $restored = [
            Authorizer::fromBase64Snapshot($authorizer->base64Snapshot()),
            Authorizer::fromRawSnapshot(pack('C*', ...$authorizer->rawSnapshot())),
        ];
        foreach ($restored as $copy) {
            static::assertSame($authorizer->factCount(), $copy->factCount());
            static::assertSame($authorizer->iterations(), $copy->iterations());
            static::assertSame($authorizer->executionTime(), $copy->executionTime());
        }
    }

    public function testSnapshotsPreserveStatisticsAfterEvaluation(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['ample'])->buildUnauthenticated();
        $authorizer->authorize();

        $restored = [
            Authorizer::fromBase64Snapshot($authorizer->base64Snapshot()),
            Authorizer::fromRawSnapshot(pack('C*', ...$authorizer->rawSnapshot())),
        ];
        foreach ($restored as $copy) {
            static::assertSame($authorizer->factCount(), $copy->factCount());
            static::assertSame($authorizer->iterations(), $copy->iterations());
            static::assertSame($authorizer->executionTime(), $copy->executionTime());
        }
    }

    public function testIterationsCannotOverflowPhpIntegers(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = Authorizer::fromBase64Snapshot($snapshots['iterations_overflow']);

        $this->expectException(Exception::class);
        $authorizer->iterations();
    }
}
