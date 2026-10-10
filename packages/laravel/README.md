# Biscuit for Laravel

Laravel integration for [Biscuit](https://www.biscuitsec.org/) authorization tokens.

> **This repository is a read-only mirror.** The package is maintained in the
> [`packages/laravel/` directory of ptondereau/biscuit-php](https://github.com/ptondereau/biscuit-php/tree/main/packages/laravel)
> and synced here automatically. Please open issues and pull requests against the main repository.

## Requirements

- PHP 8.2 or later
- Laravel 12 or 13
- The `biscuit_php` extension: `pie install ptondereau/biscuit-php`

## Installation

```bash
composer require ptondereau/biscuit-laravel
php artisan vendor:publish --tag=biscuit-config
```

Set the root key in `.env`, as a hex string or a PEM file:

```dotenv
BISCUIT_PUBLIC_KEY=ed25519/...
# BISCUIT_PUBLIC_KEY_FILE=/path/to/public.pem
```

The private key (`BISCUIT_PRIVATE_KEY` or `BISCUIT_PRIVATE_KEY_FILE`) is only needed to mint tokens.

## Authentication

Add a guard in `config/auth.php`:

```php
'guards' => [
    'biscuit' => ['driver' => 'biscuit'],
],
```

The guard reads the token from the `Authorization: Bearer` header, or from the cookie named in `BISCUIT_COOKIE`. It checks the signature against the public key. An invalid token is rejected with 401.

```php
Route::middleware('auth:biscuit')->get('/me', fn (Request $request) => $request->user()->identifier);
```

The user identifier comes from the `user` fact of the authority block (`user("alice")` or `user(42)`). Change the fact name with `biscuit.user_fact`. Without that fact, the identifier is the token's first revocation ID.

## Authorization

Declare Datalog policies in `config/biscuit.php`:

```php
'policies' => [
    'posts.read' => 'allow if right("posts", "read")',
    'posts.edit' => 'allow if right({resource}, "edit")',
],
```

Each policy is a Gate ability. It works with `can` middleware, `Gate::allows()`, `@can` and `$this->authorize()`:

```php
Route::middleware(['auth:biscuit', 'can:posts.edit,post'])->put('/posts/{post}', UpdatePost::class);
```

The Gate argument fills the policy placeholders:

| Argument | Parameters |
|---|---|
| Eloquent model | `{resource}` = primary key |
| string or int | `{resource}` = the value |
| associative array | each key is a placeholder |

Placeholders the policy does not use are ignored. Values must be strings, integers or booleans. The authorizer adds the current time, so checks such as `check if time($t), $t < 2030-01-01T00:00:00Z` expire tokens.

An ability that starts with `allow ` or `deny ` is used as an inline policy: `Gate::allows('allow if right("posts", "read")')`.

Abilities that are not Biscuit policies, and users from other guards, go to the app's own gates and policies.

## Minting tokens

```php
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\PrivateKey;

$token = (new BiscuitBuilder('user({id}); right("posts", "read");', ['id' => 'alice']))
    ->build(app(PrivateKey::class))
    ->toBase64();
```

## License

Apache-2.0
