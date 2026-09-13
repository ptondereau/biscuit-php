<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Rule;
use Biscuit\Exception\AuthorizationException;
use Biscuit\Exception\BiscuitException;
use Biscuit\Exception\RunLimitException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

class RunLimitExceptionTest extends TestCase
{
    #[Test]
    public function runLimitExceptionExtendsAuthorizationExceptionAndBiscuitException(): void
    {
        static::assertTrue(is_subclass_of(RunLimitException::class, AuthorizationException::class));
        static::assertTrue(is_subclass_of(RunLimitException::class, BiscuitException::class));
    }

    #[Test]
    public function tooManyFactsDuringAuthorizeThrowsRunLimitExceptionWithoutPolicyOrChecks(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['facts'])->buildUnauthenticated();

        try {
            $authorizer->authorize();
            static::fail('Expected the fact limit to interrupt authorization.');
        } catch (RunLimitException $e) {
            static::assertSame(1, $e->getCode());
            static::assertNull($e->getMatchedPolicy());
            static::assertSame([], $e->getFailedChecks());
            static::assertStringContainsString('too many facts', $e->getMessage());
        }
    }

    #[Test]
    #[DataProvider('limitCodes')]
    public function eachLimitKindHasItsOwnCode(string $fixture, int $code, string $detail): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots[$fixture])->buildUnauthenticated();

        try {
            $authorizer->authorize();
            static::fail('Expected the limit to interrupt authorization.');
        } catch (RunLimitException $e) {
            static::assertSame($code, $e->getCode());
            static::assertStringContainsString($detail, $e->getMessage());
        }
    }

    public static function limitCodes(): iterable
    {
        yield 'facts' => ['facts', 1, 'too many facts'];
        yield 'iterations' => ['iterations', 2, 'too many engine iterations'];
        yield 'inference timeout' => ['inference_timeout', 3, 'too much time'];
        yield 'authorization timeout' => ['authorization_timeout', 3, 'too much time'];
    }

    #[Test]
    public function tooManyFactsDuringQueryThrowsRunLimitException(): void
    {
        $snapshots = require __DIR__ . '/fixtures/authorizer-statistics.php';
        $authorizer = AuthorizerBuilder::fromBase64Snapshot($snapshots['facts'])->buildUnauthenticated();

        try {
            $authorizer->query(new Rule('result($x) <- b($x)'));
            static::fail('Expected the fact limit to interrupt the query.');
        } catch (RunLimitException $e) {
            static::assertSame(1, $e->getCode());
        }
    }

    #[Test]
    public function setLimitsMaxFactsInterruptsAnOtherwisePassingAuthorization(): void
    {
        $source = 'a(1); b($x) <- a($x); allow if b(1);';

        $unlimited = new AuthorizerBuilder($source);
        static::assertSame('allow', $unlimited->buildUnauthenticated()->authorize()->getKind());

        $builder = new AuthorizerBuilder($source);
        $builder->setLimits(maxFacts: 1);
        try {
            $builder->buildUnauthenticated()->authorize();
            static::fail('Expected the fact limit to interrupt authorization.');
        } catch (RunLimitException $e) {
            static::assertSame(1, $e->getCode());
        }
    }

    #[Test]
    public function setLimitsZeroMaxTimeInterruptsWithTimeoutCode(): void
    {
        $builder = new AuthorizerBuilder('allow if true;');
        $builder->setLimits(maxTime: 0.0);

        try {
            $builder->buildUnauthenticated()->authorize();
            static::fail('Expected the zero time limit to interrupt authorization.');
        } catch (RunLimitException $e) {
            static::assertSame(3, $e->getCode());
        }
    }

    #[Test]
    public function setLimitsWithoutArgumentsKeepsTheDefaults(): void
    {
        $builder = new AuthorizerBuilder('a(1); b($x) <- a($x); allow if b(1);');
        $builder->setLimits();

        static::assertSame('allow', $builder->buildUnauthenticated()->authorize()->getKind());
    }

    #[Test]
    public function setLimitsRejectsNegativeMaxFacts(): void
    {
        $builder = new AuthorizerBuilder('allow if true;');

        $this->expectException(ValueError::class);
        $builder->setLimits(maxFacts: -1);
    }

    #[Test]
    public function setLimitsRejectsNegativeMaxTime(): void
    {
        $builder = new AuthorizerBuilder('allow if true;');

        $this->expectException(ValueError::class);
        $builder->setLimits(maxTime: -1.0);
    }
}
