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

## Authorization

A Biscuit is not an authentication token. Keep your guards (session, Sanctum, Passport) for users. The token only carries rights.

The token comes from the `Authorization: Bearer` header, or from the cookie named in `BISCUIT_COOKIE`. Its signature is checked against the public key.


Declare Datalog policies in `config/biscuit.php`:

```php
'policies' => [
    'posts.read' => 'allow if right("posts", "read")',
    'posts.edit' => 'allow if right({resource}, "edit")',
],
```

Each policy is a Gate ability. It works with `can` middleware, `Gate::allows()`, `@can` and `$this->authorize()`:

```php
Route::middleware(['auth', 'can:posts.edit,post'])->put('/posts/{post}', UpdatePost::class);
```

The Gate argument fills the policy placeholders:

| Argument | Parameters |
|---|---|
| Eloquent model | `{resource}` = primary key |
| string or int | `{resource}` = the value |
| associative array | each key is a placeholder |

Placeholders the policy does not use are ignored. Values must be strings, integers or booleans. The authorizer adds the current time, so checks such as `check if time($t), $t < 2030-01-01T00:00:00Z` expire tokens.

An ability that starts with `allow ` or `deny ` is used as an inline policy: `Gate::allows('allow if right("posts", "read")')`.

A Biscuit policy is denied when the request has no valid token, whoever the user is. Other abilities go to the app's own gates and policies.

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
