# Changelog

All notable changes to the packages of this repository are documented in this file. Every package shares the same version.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Extension

#### Added

- `Fact::terms()` returns the terms of a fact as PHP values, so `Authorizer::query()` results can be read without parsing `__toString()`.
- Datalog parameters accept a `DateTimeInterface` for dates.
- `Bytes` wraps a binary string so it binds as a Datalog bytes term. `Fact::terms()` returns bytes as `Bytes`.

#### Changed

- **BREAKING**: a list parameter always binds as a set. A list of ints between 0 and 255, or an empty list, used to bind as bytes, so such a set could not be passed. Wrap bytes in `Bytes` instead.

#### Fixed

- `AuthorizationException` and `RunLimitException` messages no longer repeat their cause.
- An argument of the wrong type throws a `TypeError`, with ext-php-rs 0.16.3. A required argument used to throw a plain `Exception`, and an optional one was read as `null`.
- `AuthorizerBuilder::setLimits()` applies a `maxTime` given as an int. It used to be ignored, so the default limit stayed.

### Stubs

#### Added

- `Fact::terms()`, and `DateTimeInterface` in the parameter types.
- `Bytes`, also accepted in the parameter types.

### Symfony bundle

#### Fixed

- Template parameters bind as Datalog dates from a `DateTimeInterface`, or from a `--param` value in RFC 3339 form such as `2026-08-06T12:00:00Z`.
- The README's `expires` block template checks `time($t)`, the fact the voter adds, instead of `now($t)`.

### Laravel package

#### Added

- New `ptondereau/biscuit-laravel` package for Laravel 12 and 13 on PHP 8.2 or later.
- Datalog policies from `config/biscuit.php` work as Gate abilities, with placeholders filled from the Gate argument. The token comes from the bearer header or a cookie, next to the app's own authentication.
- `PublicKey` and `PrivateKey` container bindings, loaded from a hex string or a PEM file.

### Tooling

#### Changed

- Mago 1.57.0.

## [0.6.0] - 2026-10-10

### Symfony bundle

#### Changed

- The bundle is now developed in [ptondereau/biscuit-php](https://github.com/ptondereau/biscuit-php) under `packages/symfony-bundle` and follows the extension version. [ptondereau/biscuit-sf-bundle](https://github.com/ptondereau/biscuit-sf-bundle) is a read-only mirror. Earlier releases are in the [bundle changelog](packages/symfony-bundle/CHANGELOG.md).

### Stubs

#### Changed

- The stubs are maintained under `packages/stubs`. The published package layout is unchanged.

## Earlier releases

Extension releases up to 0.5.6 are described in the [GitHub releases](https://github.com/ptondereau/biscuit-php/releases).

[Unreleased]: https://github.com/ptondereau/biscuit-php/compare/v0.6.0...HEAD
[0.6.0]: https://github.com/ptondereau/biscuit-php/compare/v0.5.6...v0.6.0
