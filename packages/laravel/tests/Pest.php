<?php

declare(strict_types=1);

use Biscuit\Laravel\Tests\TestCase;
use Pest\TestSuite;

pest()->extend(TestCase::class)->in('Feature');

function testCase(): TestCase
{
    $test = TestSuite::getInstance()->test;

    if (!$test instanceof TestCase) {
        throw new LogicException('testCase() only works inside a Feature test.');
    }

    return $test;
}
