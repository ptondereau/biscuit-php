# PHP Extension for Biscuit

PHP bindings for [Biscuit](https://www.biscuitsec.org), a bearer token supporting offline attenuation, decentralized verification, and powerful authorization policies.

[![CI](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml/badge.svg)](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml)
[![Split](https://github.com/ptondereau/biscuit-php/actions/workflows/split.yml/badge.svg)](https://github.com/ptondereau/biscuit-php/actions/workflows/split.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/ptondereau/biscuit-php.svg)](https://packagist.org/packages/ptondereau/biscuit-php)
[![License](https://img.shields.io/badge/license-Apache%202.0-blue.svg)](LICENSE)

## Packages

This repository holds the extension and the PHP packages built on top of it. They share one version and one [changelog](./CHANGELOG.md).

| Package | Source | Packagist | Version |
| --- | --- | --- | --- |
| Extension | repository root | [`ptondereau/biscuit-php`](https://packagist.org/packages/ptondereau/biscuit-php) | [![Latest Version](https://img.shields.io/packagist/v/ptondereau/biscuit-php.svg)](https://packagist.org/packages/ptondereau/biscuit-php) |
| IDE and static-analysis stubs | [`packages/stubs`](./packages/stubs) | [`ptondereau/biscuit-php-stubs`](https://packagist.org/packages/ptondereau/biscuit-php-stubs) | [![Latest Version](https://img.shields.io/packagist/v/ptondereau/biscuit-php-stubs.svg)](https://packagist.org/packages/ptondereau/biscuit-php-stubs) |
| Symfony bundle | [`packages/symfony-bundle`](./packages/symfony-bundle) | [`ptondereau/biscuit-symfony-bundle`](https://packagist.org/packages/ptondereau/biscuit-symfony-bundle) | [![Latest Version](https://img.shields.io/packagist/v/ptondereau/biscuit-symfony-bundle.svg)](https://packagist.org/packages/ptondereau/biscuit-symfony-bundle) [![Coverage Status](https://coveralls.io/repos/github/ptondereau/biscuit-php/badge.svg?branch=main)](https://coveralls.io/github/ptondereau/biscuit-php?branch=main) |

On every push to `main` and on release tags, the [`split`](./.github/workflows/split.yml) workflow mirrors each directory of `packages/` to its read-only repository ([`biscuit-php-stubs`](https://github.com/ptondereau/biscuit-php-stubs), [`biscuit-sf-bundle`](https://github.com/ptondereau/biscuit-sf-bundle)), which is what Packagist tracks. Issues and pull requests for every package go to this repository.

## Documentation and Specifications

- [Biscuit Website](https://www.biscuitsec.org) - Documentation and examples
- [Biscuit Specification](https://github.com/biscuit-auth/biscuit)
- [Biscuit Rust](https://github.com/biscuit-auth/biscuit-rust) - Technical details
- [Upgrading Guide](./UPGRADING.md) - Migration guide for breaking changes

## Requirements

- [`cargo-php`](https://crates.io/crates/cargo-php)
- PHP >= 8.1 with `php-dev` installed
- Rust
- Clang

## Installation

### Pre-built Binaries (Recommended)

Pre-built binaries are available for Linux (glibc/musl, x86_64/arm64), macOS (x86_64/arm64), and Windows (x86_64) across PHP 8.1 through 8.5, with both Thread-Safe (TS) and Non-Thread-Safe (NTS) variants. Download the appropriate archive for your platform from the [latest release](https://github.com/ptondereau/biscuit-php/releases/latest).

#### Quick Installation (Linux glibc x86_64, PHP 8.3 NTS shown)

```bash
# Pick the archive matching your PHP version, libc, arch, and TS/NTS:
VERSION=v0.4.0
wget https://github.com/ptondereau/biscuit-php/releases/download/${VERSION}/php_biscuit_php-${VERSION}_php8.3-x86_64-linux-glibc-nts.zip
unzip php_biscuit_php-${VERSION}_php8.3-x86_64-linux-glibc-nts.zip

# Move to PHP extension directory (adjust path for your system)
sudo mv biscuit_php.so /usr/lib/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/

# Enable the extension
echo "extension=biscuit_php.so" | sudo tee /etc/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/mods-available/biscuit_php.ini
sudo phpenmod biscuit_php

# Verify installation
php -m | grep biscuit_php
```

### PIE installation

We support [PIE](https://github.com/php/pie/) installation:
```bash
pie install ptondereau/biscuit-php
```

and you can add in your `composer.json`:
```json
{
    // ...
    "ext-biscuit_php": "*",
    // ...
}
```


### Build from Source

If pre-built binaries are not available for your platform:

```bash
# Clone the repository
git clone https://github.com/ptondereau/biscuit-php.git
cd biscuit-php

# Install dependencies
composer install

# Build the extension
cargo build --release

# Load the extension
php -dextension=target/release/libbiscuit_php.so -m | grep biscuit_php
```

### IDE stubs and static analysis

PHP stubs for the whole extension API are published as a dedicated Composer package, [`ptondereau/biscuit-php-stubs`](https://github.com/ptondereau/biscuit-php-stubs):

```bash
composer require --dev ptondereau/biscuit-php-stubs
```

The stubs ship with full docblocks (usage examples, precise types, and `@throws` annotations), so IDEs, PHPStan, and Psalm can understand the API without loading the extension. Every stub method throws an `Error` when called, so a missing extension fails loudly at runtime instead of silently.

## Quick Start

```php
<?php

use Biscuit\Auth\{Biscuit, BiscuitBuilder, KeyPair, AuthorizerBuilder};

// Generate a keypair
$root = new KeyPair();

// Create a biscuit token (can pass code directly to constructor)
$builder = new BiscuitBuilder('user("alice"); resource("file1")');
$biscuit = $builder->build($root->getPrivateKey());

// Serialize to base64
$token = $biscuit->toBase64();

// Parse and authorize
$parsed = Biscuit::fromBase64($token, $root->getPublicKey());

// Authorizer with inline code
$authBuilder = new AuthorizerBuilder('allow if user("alice"), resource("file1")');
$authorizer = $authBuilder->build($parsed);

// Check authorization: returns the matched allow policy,
// or throws Biscuit\Exception\AuthorizationException on failure
$policy = $authorizer->authorize();
echo "Authorized by policy #{$policy->getPolicyId()} ({$policy->getKind()})";
```

## Advanced Examples

### Third-Party Blocks

```php
// Create biscuit
$biscuit = $builder->build($rootKey);

// Third-party attestation
$thirdPartyKey = new KeyPair();
$request = $biscuit->thirdPartyRequest();

$externalBlock = new BlockBuilder();
$externalBlock->addCode('external_fact("verified");');
$signedBlock = $request->createBlock($thirdPartyKey->getPrivateKey(), $externalBlock);

$biscuitWithAttestation = $biscuit->appendThirdParty(
    $thirdPartyKey->getPublicKey(),
    $signedBlock
);
```

### Parameterized Primitives

```php
use Biscuit\Auth\{Fact, Rule, Check, Policy};

// Fact with constructor params
$fact = new Fact('user({id})', ['id' => 'alice']);

// Rule with params
$rule = new Rule('can_read($u, {res}) <- user($u)', ['res' => 'file1']);

// Check with params
$check = new Check('check if user({name})', ['name' => 'alice']);

// Policy with params
$policy = new Policy('allow if user({name})', ['name' => 'alice']);

// Or use set() method for dynamic values
$fact = new Fact('user({id})');
$fact->set('id', $userId);
```

### Authorizer Queries

```php
$authorizer = $authBuilder->build($biscuit);

$rule = new Rule('users($id) <- user($id)');
$facts = $authorizer->query($rule);

foreach ($facts as $fact) {
    echo "Found: {$fact->name()}\n";
}
```

### Datalog Statistics

Read the authorizer's engine statistics without triggering evaluation:

```php
try {
    $policy = $authorizer->authorize();
} finally {
    $statistics = [
        'execution_time_seconds' => $authorizer->executionTime(),
        'iterations' => $authorizer->iterations(),
        'fact_count' => $authorizer->factCount(),
    ];
}
```

`executionTime()` returns the cumulative engine time in seconds as a float, or
`null` when unavailable. It includes inference and subsequent authorization or
query evaluation, but excludes token verification, authorizer construction, and
PHP binding overhead. It is not the duration of the last PHP call.

`iterations()` counts inference passes that generated new facts. `factCount()`
includes initial and derived facts, distinguished by their origin sets. Repeated
calls may reuse completed inference without increasing either counter.

Statistics remain readable after an exception. If inference was interrupted,
the counters can contain partial results while the time is still `null`; a
recorded time does not imply successful authorization. Snapshots preserve these
statistics, so restored values can describe earlier execution.

### Run Limits

The Datalog engine stops after 1000 facts, 100 iterations, or 1 ms of engine
time by default. Hitting a limit is not a policy decision: it throws
`Biscuit\Exception\RunLimitException`, a subclass of `AuthorizationException`
whose `getCode()` is `1` (facts), `2` (iterations), or `3` (time). Raise the
limits on the builder when the defaults are too tight for your host:

```php
$authBuilder->setLimits(maxTime: 0.05);

try {
    $authorizer = $authBuilder->build($token);
    $authorizer->authorize();
} catch (RunLimitException $e) {
    // engine interrupted, not a denial
} catch (AuthorizationException $e) {
    // denied
}
```

Arguments left `null` keep their current value. Limits travel with snapshots.

### Root Key Rotation

Set a root key ID on the token. To verify the token, give a key set. The key set uses the root key ID as the index.

```php
$builder = new BiscuitBuilder('user("alice")');
$builder->setRootKeyId(2);
$token = $builder->build($current->getPrivateKey());

$parsed = Biscuit::fromBase64($token->toBase64(), [
    1 => $previous->getPublicKey(),
    2 => $current->getPublicKey(),
]);
```

The extension rejects a token that has no root key ID.
The extension also rejects a token if the key set does not contain its root key ID.
`Biscuit::fromBytes()` and `UnverifiedBiscuit::verify()` also accept a key set.

### Snapshot Persistence

```php
// Save authorizer state
$snapshot = $authorizer->base64Snapshot();

// Restore later
$restored = Authorizer::fromBase64Snapshot($snapshot);
$policy = $restored->authorize();
```

### PEM Key Import

```php
$pem = "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----";
$privateKey = PrivateKey::fromPem($pem);
$keyPair = KeyPair::fromPrivateKey($privateKey);
```

### Direct Private Key Generation

```php
use Biscuit\Auth\{PrivateKey, Algorithm};

// Generate a random private key (Ed25519 by default)
$privateKey = PrivateKey::generate();

// Generate with specific algorithm
$privateKey = PrivateKey::generate(Algorithm::Secp256r1);

// Get the corresponding public key
$publicKey = $privateKey->getPublicKey();
```

### Algorithm Support

```php
use Biscuit\Auth\Algorithm;

// Ed25519 is the default algorithm (recommended)
$keypair1 = new KeyPair(); // Uses Ed25519

// Explicitly use Secp256r1
$keypair2 = new KeyPair(Algorithm::Secp256r1);

// Key import defaults to Ed25519
$publicKey = PublicKey::fromBytes($bytes); // Defaults to Ed25519
$publicKey = PublicKey::fromBytes($bytes, Algorithm::Ed25519); // Explicit Ed25519
$publicKey = PublicKey::fromBytes($bytes, Algorithm::Secp256r1); // Explicit Secp256r1
```

## Testing

```bash
cargo build
php \
    -dextension=target/debug/libbiscuit_php.so \
    vendor/bin/phpunit
```

## Formatting

We're using [Mago](https://mago.carthage.software/) as code-style formatter for PHP code

```bash
composer install
cargo build
php \
    -dextension=target/debug/libbiscuit_php.so \
    vendor/bin/mago lint // and format
```

## PHP Stubs

The stubs live in [`packages/stubs/`](./packages/stubs) and are maintained by hand, one file per class. When changing the PHP API exposed from Rust, update the matching stub in `packages/stubs/` in the same pull request.

## Contributing

Contributions are welcome! Please:

1. Add tests for new features
2. Update documentation and the PHP stubs in `packages/stubs/` when the API changes
3. Add an entry to [`CHANGELOG.md`](./CHANGELOG.md) under `## [Unreleased]`, in the section of the package you changed
4. Ensure all tests pass

Symfony bundle specifics are in [its contributing guide](./packages/symfony-bundle/CONTRIBUTING.md).

## License

Licensed under [Apache License, Version 2.0](./LICENSE).
