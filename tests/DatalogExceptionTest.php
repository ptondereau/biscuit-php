<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\Fact;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\ParseError;
use Biscuit\Auth\Rule;
use Biscuit\Exception\DatalogException;
use Biscuit\Exception\FactException;
use Biscuit\Exception\RuleException;
use Biscuit\Exception\ScopeException;
use Biscuit\Exception\TermException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DatalogExceptionTest extends TestCase
{
    #[Test]
    public function parseFailurePopulatesParseErrorsAndLeavesParameterAccessorsNull(): void
    {
        try {
            new Fact('wrong');
            static::fail('expected FactException');
        } catch (FactException $e) {
            static::assertInstanceOf(DatalogException::class, $e);

            $parseErrors = $e->getParseErrors();
            static::assertIsArray($parseErrors);
            static::assertNotEmpty($parseErrors);
            static::assertContainsOnlyInstancesOf(ParseError::class, $parseErrors);

            static::assertNull($e->getMissingParameters());
            static::assertNull($e->getUnusedParameters());
        }
    }

    #[Test]
    public function unusedParameterPopulatesUnusedParametersAndLeavesOthersEmpty(): void
    {
        try {
            new Rule('foo({x}) <- bar({y})', ['z' => 1]);
            static::fail('expected TermException');
        } catch (TermException $e) {
            static::assertInstanceOf(DatalogException::class, $e);

            static::assertNull($e->getParseErrors());

            $missing = $e->getMissingParameters();
            $unused = $e->getUnusedParameters();
            static::assertIsArray($missing);
            static::assertIsArray($unused);
            static::assertSame([], $missing);
            static::assertSame(['z'], $unused);
        }
    }

    #[Test]
    public function missingParameterPopulatesMissingParametersAtBuilderTime(): void
    {
        $rule = new Rule('foo({x}) <- bar({y})');

        try {
            $builder = new BiscuitBuilder();
            $builder->addRule($rule);
            static::fail('expected RuleException');
        } catch (RuleException $e) {
            static::assertInstanceOf(DatalogException::class, $e);

            static::assertNull($e->getParseErrors());

            $missing = $e->getMissingParameters();
            $unused = $e->getUnusedParameters();
            static::assertIsArray($missing);
            static::assertIsArray($unused);
            static::assertContains('x', $missing);
            static::assertContains('y', $missing);
            static::assertSame([], $unused);
        }
    }

    #[Test]
    public function termFailureLeavesAllAccessorsNull(): void
    {
        try {
            $fact = new Fact('user({id})');
            $fact->set('id', null);
            static::fail('expected TermException');
        } catch (TermException $e) {
            static::assertInstanceOf(DatalogException::class, $e);

            static::assertNull($e->getParseErrors());
            static::assertNull($e->getMissingParameters());
            static::assertNull($e->getUnusedParameters());
        }
    }

    #[Test]
    public function unusedScopeParameterThrowsScopeException(): void
    {
        $keyPair = new KeyPair();
        $key = $keyPair->getPublicKey();

        try {
            new Rule('right({id}) <- user({id})', null, ['nonexistent' => $key]);
            static::fail('expected ScopeException');
        } catch (ScopeException $e) {
            static::assertInstanceOf(DatalogException::class, $e);
        }
    }

    #[Test]
    public function parseErrorAccessorsExposeInputAndMessage(): void
    {
        try {
            new Fact('wrong');
            static::fail('expected FactException');
        } catch (FactException $e) {
            $parseErrors = $e->getParseErrors();
            static::assertIsArray($parseErrors);
            static::assertNotEmpty($parseErrors);

            $first = $parseErrors[0];
            static::assertInstanceOf(ParseError::class, $first);
            static::assertIsString($first->getInput());

            $message = $first->getMessage();
            static::assertTrue($message === null || is_string($message));
        }
    }
}
