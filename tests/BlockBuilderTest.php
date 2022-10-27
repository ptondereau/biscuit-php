<?php

namespace Biscuit\Tests;

use Biscuit\Auth\BlockBuilder;
use Biscuit\Auth\Fact;
use Biscuit\Auth\Check;
use PHPUnit\Framework\TestCase;

class BlockBuilderTest extends TestCase
{
    public function testBlockBuilder(): void
    {
        $blockBuilder = new BlockBuilder();

        $fact = new Fact('user({id})');
        $fact->set('id', 15);

        $check = new Check('check if resource({id}), operation("read") or admin("authority")');
        $check->set('id', 'uuid');

        $blockBuilder->addFact($fact);
        $blockBuilder->addCheck($check);

        $expected = <<<'BLOCK'
        user(15);
        check if resource("uuid"), operation("read") or admin("authority");

        BLOCK;

        self::assertEquals(
            $expected,
            (string) $blockBuilder,
        );
    }
}
