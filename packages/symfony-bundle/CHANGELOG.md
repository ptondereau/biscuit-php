# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-05-06

### Added

- `KeyManager` for loading ed25519 or secp256r1 key pairs from hex, files, or generated on demand.
- `BiscuitTokenManager` and `BiscuitTokenFactory` for creating, signing, and parsing tokens, with support for named token templates declared in configuration.
- `HeaderTokenExtractor`, `CookieTokenExtractor`, and `ChainTokenExtractor` for pulling tokens from incoming requests.
- `BiscuitAuthenticator` for stateless firewalls, producing a `BiscuitUser` with the verified token attached.
- `BiscuitVoter` integrated with Symfony's `#[IsGranted]` attribute, executing named or inline Datalog policies against the verified token.
- `PolicyRegistry` resolving policy names to Datalog strings, with parameter binding for runtime values.
- Optional token verification caching backed by any Symfony cache pool.
- `RevocationCheckerInterface` and a default cache-backed implementation for enforcing revocation lists.
- Web profiler data collector showing token presence, blocks, revocation IDs, and per-request policy decisions.
- Console commands: `biscuit:keys:generate`, `biscuit:token:create`, `biscuit:token:inspect`, `biscuit:policy:test`.
- `make:biscuit-policy` maker for scaffolding policy classes.
- Test helpers: `BiscuitTestTrait`, `TestBiscuitAuthenticator`, `BiscuitFixtures`, `BiscuitFixtureLoader`.

[Unreleased]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/ptondereau/biscuit-sf-bundle/releases/tag/v0.1.0
