<?php

declare(strict_types=1);

use Biscuit\Laravel\Policies;
use Illuminate\Database\Eloquent\Model;

function policies(): Policies
{
    return new Policies([
        'posts.read' => 'allow if right("posts", "read")',
        'posts.edit' => 'allow if right({resource}, "edit")',
        'transfer' => 'allow if amount({amount}), owner({owner})',
    ]);
}

it('ignores an unknown ability', function (): void {
    expect(policies()->resolve('app.ability', []))->toBeNull();
});

it('resolves a named policy', function (): void {
    expect((string) policies()->resolve('posts.read', []))->toBe('allow if right("posts", "read")');
});

it('resolves an inline policy', function (): void {
    expect((string) policies()->resolve('deny if banned(true)', []))->toBe('deny if banned(true)');
});

foreach (['string' => '42', 'int' => 42] as $type => $subject) {
    it("turns a {$type} subject into the resource", function () use ($subject): void {
        expect((string) policies()->resolve('posts.edit', [$subject]))->toBe('allow if right("42", "edit")');
    });
}

it('uses the key of a model subject', function (): void {
    $model = (new class extends Model {})->forceFill(['id' => 7]);

    expect((string) policies()->resolve('posts.edit', [$model]))->toBe('allow if right("7", "edit")');
});

foreach (['empty' => '', 'array' => ['allow if true'], 'null' => null] as $type => $policy) {
    it("rejects a {$type} policy", function () use ($policy): void {
        expect(static fn(): Policies => new Policies(['posts.read' => $policy]))
            ->toThrow(
                InvalidArgumentException::class,
                'Biscuit policy "posts.read" must be a non-empty Datalog string.',
            );
    });
}

it('rejects an argument the policy uses that is not a Datalog term', function (): void {
    expect(static fn(): mixed => policies()->resolve('transfer', ['amount' => 1.5, 'owner' => 'bob']))
        ->toThrow(
            InvalidArgumentException::class,
            'Biscuit policy parameter "amount" must be a string, an integer or a boolean.',
        );
});

it('drops named arguments the policy does not use', function (): void {
    expect((string) policies()->resolve('transfer', ['amount' => 10, 'owner' => 'bob', 'geo' => 'fr']))
        ->toBe('allow if amount(10), owner("bob")');
});
