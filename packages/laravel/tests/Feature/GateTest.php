<?php

declare(strict_types=1);

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Gate;

it('denies a request without token', function (): void {
    testCase()->getJson('/posts')->assertForbidden();
});

it('denies a token signed by another key', function (): void {
    $token = (new BiscuitBuilder('right("posts", "read");'))
        ->build((new KeyPair())->getPrivateKey())
        ->toBase64();

    testCase()->getJson('/posts', ['Authorization' => "Bearer {$token}"])->assertForbidden();
});

it('denies a malformed token', function (): void {
    testCase()->getJson('/posts', ['Authorization' => 'Bearer not-a-biscuit'])->assertForbidden();
});

it('reads the token from the cookie', function (): void {
    testCase()
        ->withCredentials()
        ->withUnencryptedCookie('biscuit', testCase()->mint('right("posts", "read");')->toBase64())
        ->getJson('/posts')
        ->assertOk();
});

it('authorizes alongside the user of any guard', function (): void {
    testCase()
        ->actingAs(new GenericUser(['id' => 1]))
        ->getJson('/posts', testCase()->bearer('right("posts", "read");'))
        ->assertOk();
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
    request()->headers->add(testCase()->bearer('right("posts", "read");'));
    Gate::define('app.ability', static fn(?GenericUser $_user): bool => true);

    expect(Gate::allows('allow if right("posts", "read")'))
        ->toBeTrue()
        ->and(Gate::allows('allow if right("posts", "write")'))
        ->toBeFalse()
        ->and(Gate::allows('app.ability'))
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

it('denies a Biscuit policy without token even when the app defines it', function (): void {
    $user = new GenericUser(['id' => 1]);
    Gate::define('posts.edit', static fn(): bool => true);

    expect(Gate::forUser($user)->allows('posts.edit'))->toBeFalse();
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
