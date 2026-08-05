# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

See [UPGRADE-0.4.md](UPGRADE-0.4.md) for migration steps.

### Added

- Revocation is now composable. Implement `Revocation\RevocationStoreInterface` and autoconfiguration wires it into the pipeline with no tag and no service configuration. Stores are consulted in priority order and the first match wins, so you can put a break-glass list in front of a database.
- Three stores ship with the bundle: `static` reads a list from config, an env variable or a file with no I/O on the request path; `cache` is PSR-6 backed and converges every instance when pointed at Redis; `in_memory` holds a per-process list for tests and worker runtimes.
- Four commands: `biscuit:revocation:revoke`, `biscuit:revocation:check`, `biscuit:revocation:list` and `biscuit:revocation:purge`. `revoke` prints the chain with its target marked before writing, `check` exits 0/1/2 so it drops into a health check, and `list --format=txt` emits the exact format the static store reads.
- `RevocationEntryFactory` targets the deepest identifier of a token and reads the expiration and subject from the authority block, so revoking one device's token leaves every other token minted from the same root working.
- The profiler gained a Revocation tab showing the verdict, which store answered, per-store timings and whether the check ran degraded. It is populated for rejected requests too, so a revoked token still shows its blocks.
- `BiscuitRevocationCheckedEvent`, `BiscuitTokenRevokedEvent` and `BiscuitRevocationDegradedEvent`. The degraded event fires under either failure policy, so alerting works whether the request was rejected or let through.
- `biscuit.security.user_identifier_fact` replaces the previously hardcoded `user` fact name.
- `X-Test-Biscuit-Revoked` header on `TestBiscuitAuthenticator`, so a functional test can assert how an endpoint answers a revoked token.
- `Test\BiscuitRevocationTestTrait` for testing revocation from the consuming side. `assertTokenRevoked()` and `assertTokenNotRevoked()` work against whichever stores you configured. `receiveRevocation()` applies a revocation the way a node consuming a pushed message does, writing locally and publishing nothing, so you can test how your endpoints answer a token another instance revoked without standing up a broker. `RevocationPushHandler` is now a public alias, so reaching for it needs no private service id.
- A `doctrine` revocation store, so revocations survive a restart. It reads, writes, enumerates and purges, so `biscuit:revocation:purge` deletes rows and reports a real count instead of the zero a PSR-6 pool has to return. Supports MySQL, MariaDB, PostgreSQL and SQLite, each with its own upsert; unsupported platforms fail with a named error rather than a syntax error. Enable it behind `biscuit.revocation.stores.doctrine` and it is consulted last, after every cheaper store.
- The database store declares its table through `configureSchema()`, so `doctrine:migrations:diff` generates a reviewable migration and your migrations own the DDL. Keep `doctrine/doctrine-bundle` installed if you use the ORM: the `postGenerateSchema` hook is what stops a diff from emitting `DROP TABLE` for the revocation table, and running that migration would turn revocation off silently. Applications without migrations get `biscuit:revocation:doctrine:setup`, with `--dump-sql`.
  This reverses a position we recorded when designing the store abstraction: that a Doctrine store belonged in the docs rather than the bundle, because schemas vary too much. That objection was about owning the DDL. `configureSchema()` lets the bundle declare the table without ever creating it, so it no longer applies.
- `biscuit.revocation.push` broadcasts every revoke, unrevoke and purge over Symfony Messenger, so instances holding a per-process `in_memory` list learn about a revocation immediately instead of never. Turn it on and `RevocationWriterInterface` starts publishing; your `logout()` and the console commands propagate with no code change. Route the messages to a fanout transport, because a work-queue transport delivers each one to a single instance and leaves every other node accepting the token. Aimed at worker runtimes such as FrankenPHP and RoadRunner; a shared Redis pool is still the simpler answer when you have one. A consumed message cannot be rebroadcast: the handler holds a writer with no bus, so there is no loop to break. Consuming nodes fire the new `BiscuitRevocationReceivedEvent` rather than `BiscuitTokenRevokedEvent`, so a listener that mails the user sends one mail and not one per node.

### Changed

- Authentication failures return an RFC 6750 body and a `WWW-Authenticate` challenge. The body is now `{"error": "invalid_token", "error_description": "..."}`; it used to be `{"error": "<human message>", "message": ""}`. Requests that sent no credentials get `Bearer realm="api"` with no error code, as the spec requires. Turn the header off with `biscuit.security.www_authenticate: false`.
- `BiscuitAuthenticator` implements `AuthenticationEntryPointInterface`, so anonymous requests get the same response shape as rejected tokens once you set `entry_point: biscuit.authenticator`.
- `biscuit.revocation.on_unavailable` must be set explicitly whenever revocation is enabled. There is no default because the right answer depends on whether an unreachable revocation list should take your API down.
- Enabling revocation with no store configured fails the container build. It used to be possible to have revocation reported as active while every token passed.
- Tagging a service `biscuit.revocation_enumerable_store` when it cannot enumerate its entries fails the container build, matching the checks already done on the store and writer tags. It used to compile and then fail at runtime inside `biscuit:revocation:list`.

### Removed

- `biscuit.cache.*` and `Cache\TokenCache`. The configuration never had any effect because the class was never registered as a service, and it could not have worked: it stored a `Biscuit` instance in a cache pool, and the extension object has a private constructor and no serialization support.
- `biscuit.revocation.service`. Tag or autoconfigure a store instead.
- `Cache\Revocation\RevocationCheckerInterface` and `Cache\Revocation\CacheRevocationChecker`, replaced by `Revocation\RevocationStoreInterface` and `Revocation\Store\CacheRevocationStore`.

### Fixed

- The profiler panel no longer crashes for applications without `symfony/asset`. It called the Twig `asset()` function, which is unavailable unless that package is installed, and the panel raised a Twig `SyntaxError` instead of rendering.
- `psr/cache`, `psr/log` and `symfony/event-dispatcher-contracts` are declared in `require`. They were used by shipped classes but only reachable through a dev dependency, so a `--no-dev` install carried classes referencing missing interfaces.
- The cache-backed store gets its own `cache.biscuit.revocation` pool rather than sharing `cache.app`, where a routine `cache:pool:clear cache.app` would have un-revoked every token.
- The cache store resolves every identifier in one `getItems()` call instead of one `getItem()` per identifier.
- `biscuit:revocation:list` reports an unreadable store through the usual styled error and exits `1`, rather than printing a stack trace. An enumerable store backed by I/O throws while being iterated, which the command did not account for.
- `biscuit:revocation:purge` no longer aborts when it cannot count entries that never expire. That count is advisory and ran before the purge, so an unreachable store took out the operation it was only advising on.
- `biscuit:revocation:list` stops reading once it has enough entries for `--limit` and `--offset`, instead of loading every entry from every store first.

### For contributors

- `tests/Functional` and `tests/Integration` suites now compile and boot a real container, which is what unit tests calling `Extension::load()` cannot reach. `TestKernel` finally exists at the path `phpunit.xml.dist` has been pointing at.
- A `no-optional-deps` CI job removes every suggested package and reruns the unit suite, so the `suggest` block is a contract rather than documentation.
- `BiscuitExtension` no longer patches `biscuit.authenticator` with a positional `replaceArgument()`. Arguments are named throughout, and the checker is resolved with `nullOnInvalid()`.
- `RevocationSchemaListener` only calls `GenerateSchemaEventArgs::setSchema()` when `Schema::edit()` exists. doctrine/orm 3.6.8 added the setter but it throws below doctrine/dbal 4.5.

## [0.3.0] - 2026-07-07

### Changed

- The vendored `stubs/biscuit-php.stubs.php` file is gone; the bundle now uses the [`ptondereau/biscuit-php-stubs`](https://github.com/ptondereau/biscuit-php-stubs) package (dev dependency) for IDE autocompletion and static analysis.
- Datalog template sources (`facts`, `checks`, `rules`) and policy strings are typed `non-empty-string`; an empty policy string now throws an `InvalidArgumentException` instead of a parse error from the extension, and an empty configured key is treated as unset.
- CI installs the `biscuit_php` extension 0.5.1.

## [0.2.2] - 2026-05-27

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

[Unreleased]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.3.0...HEAD
[0.3.0]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.2.2...v0.3.0
[0.2.2]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/ptondereau/biscuit-sf-bundle/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/ptondereau/biscuit-sf-bundle/releases/tag/v0.1.0
