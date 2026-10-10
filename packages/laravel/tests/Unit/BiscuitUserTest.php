<?php

declare(strict_types=1);

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Laravel\BiscuitUser;

function biscuit(string $code): Biscuit
{
    return (new BiscuitBuilder($code))->build((new KeyPair())->getPrivateKey());
}

$identifiers = [
    'a string user fact' => ['user("alice");', 'alice'],
    'an integer user fact' => ['user(42);', '42'],
    'a user fact among other facts' => ['right("posts", "read"); user("bob");', 'bob'],
];

foreach ($identifiers as $case => [$code, $expected]) {
    it("reads the identifier from {$case}", function () use ($code, $expected): void {
        expect(BiscuitUser::fromBiscuit(biscuit($code), 'user')->getAuthIdentifier())->toBe($expected);
    });
}

it('reads a custom user fact', function (): void {
    expect(BiscuitUser::fromBiscuit(biscuit('user("alice"); account("acme");'), 'account')->getAuthIdentifier())
        ->toBe('acme');
});

it('ignores a fact whose name only ends with the user fact', function (): void {
    $biscuit = biscuit('superuser("root");');

    expect(BiscuitUser::fromBiscuit($biscuit, 'user')->getAuthIdentifier())->toBe($biscuit->revocationIds()[0] ?? '');
});

it('ignores the user fact in attenuation blocks', function (): void {
    $biscuit = biscuit('right("posts", "read");');
    $attenuated = $biscuit->append(new BlockBuilder('user("mallory");'));

    expect(BiscuitUser::fromBiscuit($attenuated, 'user')->getAuthIdentifier())
        ->toBe($attenuated->revocationIds()[0] ?? '');
});

it('never exposes a password or a remember token', function (): void {
    $user = BiscuitUser::fromBiscuit(biscuit('user("alice");'), 'user');
    $user->setRememberToken('stolen');

    expect($user->getAuthIdentifierName())
        ->toBe('identifier')
        ->and($user->getAuthPasswordName())
        ->toBeEmpty()
        ->and($user->getAuthPassword())
        ->toBeEmpty()
        ->and($user->getRememberToken())
        ->toBeEmpty()
        ->and($user->getRememberTokenName())
        ->toBeEmpty();
});
