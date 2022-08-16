# PHP extension for Biscuit

## This is a work-in-progress for now

[![CI](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml/badge.svg)](https://github.com/ptondereau/biscuit-php/actions/workflows/tests.yml)

## Documentation and specifications

- [biscuit website](https://www.biscuitsec.org) for documentation and examples
- [biscuit specification](https://github.com/biscuit-auth/biscuit)
- [biscuit-rust](https://github.com/biscuit-auth/biscuit-rust) for some more technical details.

## Requirements

- [`cargo-php`](https://crates.io/crates/cargo-php)
- PHP with `php-dev` installed >= 8.1
- Rust >= 1.61
- CLang 5

## Generating PHP stubs

[`cargo-php`](https://crates.io/crates/cargo-php) have a builtin feature to generate stubs but it's not finalized and stable enough. We use for the moment https://github.com/sasezaki/php-extension-stub-generator to generate with this current usage:

```bash
$ cargo build
$ php \
    -dextension=target/debug/libext_biscuit_php.so \
    php-extension-stub-generator.phar dump-files ext-biscuit-php stubs
```
## License

Licensed under [Apache License, Version 2.0](./LICENSE).
