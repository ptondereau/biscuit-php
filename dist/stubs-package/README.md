# biscuit-php-stubs

IDE and static-analysis stubs for [`biscuit_php`](https://github.com/ptondereau/biscuit-php), the PHP extension for [Biscuit](https://www.biscuitsec.org/) authorization tokens.

> **This repository is a read-only mirror.** The stubs are maintained in the
> [`stubs/` directory of ptondereau/biscuit-php](https://github.com/ptondereau/biscuit-php/tree/main/stubs)
> and synced here automatically. Please open issues and pull requests against the main repository.

## Installation

```bash
composer require --dev ptondereau/biscuit-php-stubs
```

## What you get

- One stub file per class under the `Biscuit\Auth` and `Biscuit\Exception` namespaces.
- Rich docblocks: usage examples, precise PHPStan/Psalm-friendly types, and `@throws` annotations matching the extension's exception hierarchy.

The stubs are for IDE autocompletion and static analysis only. Every stub method throws an `Error` when called, so a missing extension fails loudly instead of silently. At runtime, install the extension itself, for example with [PIE](https://github.com/php/pie):

```bash
pie install ptondereau/biscuit-php
```

## Versioning

Releases of this package track the releases of the `biscuit_php` extension: the same tag is published on both repositories.

## License

Apache-2.0, same as the main repository.
