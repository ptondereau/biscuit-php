<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Template;

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Biscuit\BiscuitBundle\Token\Template\BiscuitBuilderAdapter;
use Biscuit\BiscuitBundle\Token\Template\BlockBuilderAdapter;
use Biscuit\BiscuitBundle\Token\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Applier::class)]
#[CoversClass(BiscuitBuilderAdapter::class)]
#[CoversClass(BlockBuilderAdapter::class)]
final class ApplierTest extends TestCase
{
    #[Test]
    public function populatesBlockBuilderWithChecksFromTemplate(): void
    {
        $blockBuilder = new BlockBuilder();
        $adapter = new BlockBuilderAdapter($blockBuilder);
        $template = new Template(checks: ['check if operation("read")']);

        (new Applier())->populate($adapter, $template);

        self::assertStringContainsString('operation("read")', (string) $blockBuilder);
    }

    #[Test]
    public function populatesBlockBuilderWithFactsFromTemplate(): void
    {
        $blockBuilder = new BlockBuilder();
        $adapter = new BlockBuilderAdapter($blockBuilder);
        $template = new Template(facts: ['scope("read")']);

        (new Applier())->populate($adapter, $template);

        self::assertStringContainsString('scope("read")', (string) $blockBuilder);
    }

    #[Test]
    public function populatesBlockBuilderWithRulesFromTemplate(): void
    {
        $blockBuilder = new BlockBuilder();
        $adapter = new BlockBuilderAdapter($blockBuilder);
        $template = new Template(rules: ['allowed_for($r) <- resource($r), scope("read")']);

        (new Applier())->populate($adapter, $template);

        self::assertStringContainsString('allowed_for($r)', (string) $blockBuilder);
    }

    #[Test]
    public function bindsMatchingPlaceholderParams(): void
    {
        $blockBuilder = new BlockBuilder();
        $adapter = new BlockBuilderAdapter($blockBuilder);
        $template = new Template(checks: ['check if resource({res})']);

        (new Applier())->populate($adapter, $template, ['res' => 'doc-1']);

        self::assertStringContainsString('"doc-1"', (string) $blockBuilder);
    }

    #[Test]
    public function populatesBiscuitBuilderWithAllTermTypes(): void
    {
        $biscuitBuilder = new BiscuitBuilder();
        $adapter = new BiscuitBuilderAdapter($biscuitBuilder);
        $template = new Template(
            facts: ['user("alice")'],
            checks: ['check if operation("read")'],
            rules: ['allowed_for($r) <- resource($r)'],
        );

        (new Applier())->populate($adapter, $template);

        $source = (string) $biscuitBuilder;
        self::assertStringContainsString('user("alice")', $source);
        self::assertStringContainsString('operation("read")', $source);
        self::assertStringContainsString('allowed_for($r)', $source);
    }

    #[Test]
    public function ignoresUnusedParams(): void
    {
        $blockBuilder = new BlockBuilder();
        $adapter = new BlockBuilderAdapter($blockBuilder);
        $template = new Template(checks: ['check if now($t), $t <= {exp}']);

        (new Applier())->populate($adapter, $template, [
            'exp' => 9_999_999_999,
            'irrelevant' => 'whatever',
        ]);

        self::assertStringContainsString('9999999999', (string) $blockBuilder);
    }
}
