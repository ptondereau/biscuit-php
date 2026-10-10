<?php

declare(strict_types=1);

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\Laravel\BiscuitUser;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Gate;

it('rejects a request without token', function (): void {
    testCase()->getJson('/me')->assertUnauthorized();
});

it('rejects a token signed by another key', function (): void {
    $token = (new BiscuitBuilder('user("alice");'))
        ->build((new KeyPair())->getPrivateKey())
        ->toBase64();

    testCase()->getJson('/me', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
});

it('rejects a malformed token', function (): void {
    testCase()->getJson('/me', ['Authorization' => 'Bearer not-a-biscuit'])->assertUnauthorized();
});

it('authenticates from the bearer header', function (): void {
    testCase()->getJson('/me', testCase()->bearer('user("alice");'))->assertOk()->assertContent('alice');
});

it('authenticates from the cookie', function (): void {
    testCase()
        ->withCredentials()
        ->withUnencryptedCookie('biscuit', testCase()->mint('user(42);')->toBase64())
        ->getJson('/me')
        ->assertOk()
        ->assertContent('42');
});

it('falls back to the revocation id without user fact', function (): void {
    $biscuit = testCase()->mint('right("posts", "read");');

    testCase()
        ->getJson('/me', ['Authorization' => 'Bearer ' . $biscuit->toBase64()])
        ->assertOk()
        ->assertContent($biscuit->revocationIds()[0] ?? '');
});

it('grants a named policy', function (): void {
    testCase()->getJson('/posts', testCase()->bearer('right("posts", "read");'))->assertOk();
});

it('denies a named policy', function (): void {
    testCase()->getJson('/posts', testCase()->bearer('right("posts", "write");'))->assertForbidden();
});

it('fills the policy placeholder from the route parameter', function (): void {
    $headers = testCase()->bearer('right("42", "edit");');

    testCase()->getJson('/posts/42', $headers)->assertOk();
    testCase()->getJson('/posts/43', $headers)->assertForbidden();
});

it('denies an expired token', function (): void {
    testCase()
        ->getJson('/posts', testCase()->bearer('right("posts", "read"); check if time($t), $t < 2020-01-01T00:00:00Z;'))
        ->assertForbidden();
});

it('evaluates inline policies and leaves other abilities to the app', function (): void {
    $user = BiscuitUser::fromBiscuit(testCase()->mint('right("posts", "read");'), 'user');
    Gate::define('app.ability', static fn(): bool => true);

    expect(Gate::forUser($user)->allows('allow if right("posts", "read")'))
        ->toBeTrue()
        ->and(Gate::forUser($user)->allows('allow if right("posts", "write")'))
        ->toBeFalse()
        ->and(Gate::forUser($user)->allows('app.ability'))
        ->toBeTrue();
});

it('resolves the private key for minting', function (): void {
    expect(app(PrivateKey::class)->toHex())->toBe(testCase()->keys()->getPrivateKey()->toHex());
});

it('fails loudly without public key', function (): void {
    config()->set('biscuit.public_key', null);
    app()->forgetInstance(PublicKey::class);

    expect(static fn(): PublicKey => app(PublicKey::class))
        ->toThrow(RuntimeException::class, 'No Biscuit public key configured');
});

it('fails loudly when the key file is missing', function (): void {
    config()->set('biscuit.public_key', null);
    config()->set('biscuit.public_key_file', '/nonexistent/biscuit.pem');
    app()->forgetInstance(PublicKey::class);

    expect(static fn(): PublicKey => app(PublicKey::class))->toThrow(FileNotFoundException::class);
});

it('leaves users of other guards to the app gates', function (): void {
    $user = new GenericUser(['id' => 1]);
    Gate::define('posts.edit', static fn(): bool => true);

    expect(Gate::forUser($user)->allows('posts.read'))
        ->toBeFalse()
        ->and(Gate::forUser($user)->allows('posts.edit'))
        ->toBeTrue();
});

it('loads the public key from a PEM file', function (): void {
    $key = testCase()->keys()->getPublicKey();
    $der = hex2bin('302a300506032b6570032100' . substr($key->toHex(), \strlen('ed25519/')));
    $file = sys_get_temp_dir() . '/' . uniqid('biscuit-', more_entropy: true) . '.pem';
    file_put_contents(
        $file,
        "-----BEGIN PUBLIC KEY-----\n" . base64_encode((string) $der) . "\n-----END PUBLIC KEY-----\n",
    );

    config()->set('biscuit.public_key', null);
    config()->set('biscuit.public_key_file', $file);
    app()->forgetInstance(PublicKey::class);

    expect(app(PublicKey::class)->toHex())->toBe($key->toHex());
    unlink($file);
});
