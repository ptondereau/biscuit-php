# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- `biscuit.revocation.enabled` / `biscuit.revocation.service` now actually wire the configured `RevocationCheckerInterface` into the authenticator. Previously both keys were read into container parameters but never applied, so enabling revocation had no effect and presented tokens were never checked against the revocation list. Enabling revocation without a `service` now fails fast with a clear configuration error.

## [0.2.1] - 2026-05-27

### Added

- `biscuit.authorizer_fact_templates` configuration: named fact templates that `BiscuitVoter` injects into the authorizer (keyed by policy name) before `authorize()`. This lets `#[IsGranted]` policies and token checks reason about request-context facts (amount, geo, wallet tier, time, ...) that the verifier supplies server-side, with the Datalog kept in configuration rather than inline in PHP. Backed by a new `Token\Template\AuthorizerBuilderAdapter` reusing the existing `Applier`.

### Fixed

- `PolicyRegistry::get()` now binds only the parameters the policy string actually references, so a caller may pass a wider parameter set (for example one shared with an authorizer fact template) without Biscuit rejecting the policy for unused parameters.

## [0.2.0] - 2026-05-26

### Changed

- Compatibility with `ptondereau/biscuit-php` 0.5.0:
  - `BiscuitVoter` adapted to the new `Authorizer::authorize()` shape (returns `MatchedPolicy`, throws `AuthorizationException` on deny).
  - `biscuit:policy:test` now reports the matched policy and failed-check details produced by `AuthorizationException`.
  - Stubs refreshed to the v0.5.0 exception hierarchy and value objects.
- `composer.json` now requires `ext-biscuit_php: >=0.5`.

## [0.1.1] - 2026-05-08

### Added

- `biscuit.block_templates` configuration and `BiscuitBlockFactory` for deriving scoped tokens from reusable, parameterised block templates.
- `BiscuitTokenAttenuatedEvent` dispatched on every `attenuate()` call; the data collector subscribes to surface the derivation chain in the profiler.
- `biscuit:token:attenuate` console command, accepting either a registered template name or inline Datalog via `--code`, with optional `--unverified` for cross-key inspection.
- Shared `Token\Template\Applier` module reused by `BiscuitTokenFactory` and `BiscuitBlockFactory` so populate-from-template and parameter binding live in one place.

### Changed

- **BREAKING**: `BiscuitTokenFactory` constructor now takes a `Token\Template\Applier` as its second argument; the templates array moves to third position. Bundle DI users are unaffected (the container wires both factories automatically); only direct constructor callers need to add the new argument.

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

[Unreleased]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/ptondereau/biscuit-sf-bundle/releases/tag/v0.1.0
