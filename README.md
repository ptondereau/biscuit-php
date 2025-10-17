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

### Build from Source

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
composer install
composer test

# With coverage
composer test:coverage
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
