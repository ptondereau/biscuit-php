<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Template;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Biscuit\BiscuitBundle\Token\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Applier::class)]
final class ApplierTest extends TestCase
{
    #[Test]
    public function populatesBlockBuilderWithChecksFromTemplate(): void
    {
        $builder = new BlockBuilder();
        $template = new Template(checks: ['check if operation("read")']);

        (new Applier())->populate($builder, $template);

        self::assertStringContainsString('operation("read")', (string) $builder);
    }

    #[Test]
    public function populatesBlockBuilderWithFactsFromTemplate(): void
    {
        $builder = new BlockBuilder();
        $template = new Template(facts: ['scope("read")']);

        (new Applier())->populate($builder, $template);

        self::assertStringContainsString('scope("read")', (string) $builder);
    }

    #[Test]
    public function populatesBlockBuilderWithRulesFromTemplate(): void
    {
        $builder = new BlockBuilder();
        $template = new Template(rules: ['allowed_for($r) <- resource($r), scope("read")']);

        (new Applier())->populate($builder, $template);

        self::assertStringContainsString('allowed_for($r)', (string) $builder);
    }

    #[Test]
    public function bindsMatchingPlaceholderParams(): void
    {
        $builder = new BlockBuilder();
        $template = new Template(checks: ['check if resource({res})']);

        (new Applier())->populate($builder, $template, ['res' => 'doc-1']);

        self::assertStringContainsString('"doc-1"', (string) $builder);
    }

    #[Test]
    public function populatesBiscuitBuilderWithAllTermTypes(): void
    {
        $builder = new BiscuitBuilder();
        $template = new Template(
            facts: ['user("alice")'],
            checks: ['check if operation("read")'],
            rules: ['allowed_for($r) <- resource($r)'],
        );

        (new Applier())->populate($builder, $template);

        $source = (string) $builder;
        self::assertStringContainsString('user("alice")', $source);
        self::assertStringContainsString('operation("read")', $source);
        self::assertStringContainsString('allowed_for($r)', $source);
    }

    #[Test]
    public function populatesAuthorizerBuilderWithAllTermTypes(): void
    {
        $builder = new AuthorizerBuilder();
        $template = new Template(
            facts: ['operation("credit_wallet")', 'amount({amount})'],
            checks: ['check if time($t), $t <= {now}'],
            rules: ['allowed($r) <- resource($r)'],
        );

        (new Applier())->populate($builder, $template, ['amount' => 50000, 'now' => 1000]);

        $source = (string) $builder;
        self::assertStringContainsString('operation("credit_wallet")', $source);
        self::assertStringContainsString('50000', $source);
        self::assertStringContainsString('time($t)', $source);
        self::assertStringContainsString('allowed($r)', $source);
    }

    #[Test]
    public function ignoresUnusedParams(): void
    {
        $builder = new BlockBuilder();
        $template = new Template(checks: ['check if now($t), $t <= {exp}']);

        (new Applier())->populate($builder, $template, [
            'exp' => 9_999_999_999,
            'irrelevant' => 'whatever',
        ]);

        self::assertStringContainsString('9999999999', (string) $builder);
    }
}
