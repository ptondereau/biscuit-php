<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Authorizer;

use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorizerBuilderFactory::class)]
final class AuthorizerBuilderFactoryTest extends TestCase
{
    #[Test]
    public function itAddsTheCurrentTimeToEveryBuilder(): void
    {
        $factory = new AuthorizerBuilderFactory();

        self::assertMatchesRegularExpression(
            '/time\(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\)/',
            (string) $factory->create('credit'),
        );
    }

    #[Test]
    public function itAppliesTheFactTemplateNamedAfterThePolicy(): void
    {
        $factory = new AuthorizerBuilderFactory(
            new Applier(),
            ['credit' => ['facts' => ['amount({amount})']]],
        );

        self::assertStringContainsString('amount(50)', (string) $factory->create('credit', ['amount' => 50]));
    }

    #[Test]
    public function itIgnoresTemplatesNamedAfterAnotherPolicy(): void
    {
        $factory = new AuthorizerBuilderFactory(
            new Applier(),
            ['credit' => ['facts' => ['amount({amount})']]],
        );

        self::assertStringNotContainsString('amount', (string) $factory->create('debit', ['amount' => 50]));
    }

    #[Test]
    public function itThrowsWhenATemplateParameterIsMissing(): void
    {
        $factory = new AuthorizerBuilderFactory(
            new Applier(),
            ['credit' => ['facts' => ['amount({amount})']]],
        );

        $this->expectExceptionMessage('Missing parameters: ["amount"]');

        $factory->create('credit');
    }
}
