<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Token\Template;

use Biscuit\BiscuitBundle\Token\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Template::class)]
final class TemplateTest extends TestCase
{
    #[Test]
    public function fromArrayNormalisesMissingKeysToEmptyLists(): void
    {
        $template = Template::fromArray(['checks' => ['check if true']]);

        self::assertSame([], $template->facts);
        self::assertSame(['check if true'], $template->checks);
        self::assertSame([], $template->rules);
    }

    #[Test]
    public function constructorAcceptsAllThreeListsExplicitly(): void
    {
        $template = new Template(
            facts: ['user("alice")'],
            checks: ['check if operation("read")'],
            rules: ['allowed_for($r) <- resource($r)'],
        );

        self::assertSame(['user("alice")'], $template->facts);
        self::assertSame(['check if operation("read")'], $template->checks);
        self::assertSame(['allowed_for($r) <- resource($r)'], $template->rules);
    }

    #[Test]
    public function fromArrayCarriesAllProvidedTermTypes(): void
    {
        $template = Template::fromArray([
            'facts' => ['scope("read")'],
            'checks' => ['check if operation("read")'],
            'rules' => ['allowed_for($r) <- resource($r), scope("read")'],
        ]);

        self::assertSame(['scope("read")'], $template->facts);
        self::assertSame(['check if operation("read")'], $template->checks);
        self::assertSame(['allowed_for($r) <- resource($r), scope("read")'], $template->rules);
    }
}
