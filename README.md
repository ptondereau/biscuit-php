# PHP Extension for Biscuit

PHP bindings for [Biscuit](https://www.biscuitsec.org), a bearer token supporting offline attenuation, decentralized verification, and powerful authorization policies.

[![CI](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml/badge.svg)](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml)

## Documentation and Specifications

- [Biscuit Website](https://www.biscuitsec.org) - Documentation and examples
- [Biscuit Specification](https://github.com/biscuit-auth/biscuit)
- [Biscuit Rust](https://github.com/biscuit-auth/biscuit-rust) - Technical details

## Requirements

- [`cargo-php`](https://crates.io/crates/cargo-php)
- PHP >= 8.1 with `php-dev` installed
- Rust
- Clang

## Installation

### Pre-built Binaries (Recommended)

Pre-built binaries are available for Linux x86_64 across multiple PHP versions, with both Thread-Safe (TS) and Non-Thread-Safe (NTS) variants. Download the appropriate binary for your PHP version and thread safety from the [latest release](https://github.com/ptondereau/biscuit-php/releases/latest).

#### Quick Installation

```bash
# Download binary for your PHP version and thread safety
# Replace 8.3 with your version and ts/nts based on your thread safety
wget https://github.com/ptondereau/biscuit-php/releases/latest/download/ext_biscuit_php-linux-x86_64-php8.3-nts.so

# Verify checksum
wget https://github.com/ptondereau/biscuit-php/releases/latest/download/ext_biscuit_php-linux-x86_64-php8.3-nts.so.sha256
sha256sum -c ext_biscuit_php-linux-x86_64-php8.3-nts.so.sha256

# Move to PHP extension directory (adjust path for your system)
sudo mv ext_biscuit_php-linux-x86_64-php8.3-nts.so /usr/lib/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/

# Enable the extension
echo "extension=ext_biscuit_php-linux-x86_64-php8.3-nts.so" | sudo tee /etc/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/mods-available/biscuit.ini
sudo phpenmod biscuit

# Verify installation
php -m | grep biscuit
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
    "ext-biscuit": "*",
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
php -dextension=target/release/libext_biscuit_php.so -m | grep biscuit
```

### Using stubs for autocompletion

We're exposing PHP stubs for IDE integration

```bash
composer require ptondereau/ext-biscuit-php
```

## Quick Start

```php
<?php

use Biscuit\Auth\{BiscuitBuilder, KeyPair, AuthorizerBuilder};

// Generate a keypair
$root = new KeyPair();

// Create a biscuit token
$builder = new BiscuitBuilder();
$builder->addCode('user("alice"); resource("file1")');
$biscuit = $builder->build($root->private());

// Serialize to base64
$token = $biscuit->toBase64();

// Parse and authorize
$parsed = Biscuit::fromBase64($token, $root->public());

$authBuilder = new AuthorizerBuilder();
$authBuilder->addCode('allow if user("alice"), resource("file1")');
$authorizer = $authBuilder->build($parsed);

// Check authorization
$policy = $authorizer->authorize();
echo $policy === 0 ? "Authorized!" : "Denied!";
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
$signedBlock = $request->createBlock($thirdPartyKey->private(), $externalBlock);

$biscuitWithAttestation = $biscuit->appendThirdParty(
    $thirdPartyKey->public(),
    $signedBlock
);
```

### Authorizer Queries

```php
$authorizer = $authBuilder->build($biscuit);

$rule = new Rule('users($id) <- user($id);');
$facts = $authorizer->query($rule);

foreach ($facts as $fact) {
    echo "Found: {$fact->name()}\n";
}
```

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

### Algorithm Support

```php
// Ed25519 is the default algorithm (recommended)
$keypair1 = new KeyPair(); // Uses Ed25519
$keypair2 = KeyPair::newWithAlgorithm(); // Uses Ed25519 by default

// Explicitly use Secp256r1
$keypair3 = KeyPair::newWithAlgorithm(1); // ALGORITHM_SECP256R1

// Key import defaults to Ed25519
$publicKey = PublicKey::fromBytes($bytes); // Defaults to Ed25519
$publicKey = PublicKey::fromBytes($bytes, 0); // Explicit Ed25519
$publicKey = PublicKey::fromBytes($bytes, 1); // Explicit Secp256r1
```

## Testing

```bash
cargo build
php \
    -dextension=target/debug/libext_biscuit_php.so \
    vendor/bin/phpunit
```

## Formatting

We're using [Mago](https://mago.carthage.software/) as code-style formatter for PHP code

```bash
composer install
cargo build
php \
    -dextension=target/debug/libext_biscuit_php.so \
    vendor/bin/mago lint // and format
```

## Generating PHP Stubs

```bash
cargo build
php \
    -dextension=target/debug/libext_biscuit_php.so \
    php-extension-stub-generator.phar dump-files ext-biscuit-php stubs
```

## Contributing

Contributions are welcome! Please:

1. Add tests for new features
3. Update documentation
4. Ensure all tests pass

## License

Licensed under [Apache License, Version 2.0](./LICENSE).
