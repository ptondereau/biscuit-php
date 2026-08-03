# Biscuit Symfony Bundle

Symfony bundle for [Biscuit](https://www.biscuitsec.org/) authorization tokens.

[![CI](https://github.com/ptondereau/biscuit-sf-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/ptondereau/biscuit-sf-bundle/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/ptondereau/biscuit-sf-bundle/badge.svg?branch=main)](https://coveralls.io/github/ptondereau/biscuit-sf-bundle?branch=main)
[![Latest Version](https://img.shields.io/packagist/v/ptondereau/biscuit-symfony-bundle.svg)](https://packagist.org/packages/ptondereau/biscuit-symfony-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/ptondereau/biscuit-symfony-bundle.svg)](https://packagist.org/packages/ptondereau/biscuit-symfony-bundle)
[![License](https://img.shields.io/badge/license-Apache%202.0-blue.svg)](LICENSE)

## About

Biscuit is a bearer token format with offline attenuation, third-party blocks, and a Datalog-based authorization language. This bundle integrates Biscuit into Symfony's Security component so you can authenticate requests carrying Biscuit tokens and enforce policies through the standard `#[IsGranted]` attribute.

What you get:

- Token extraction from `Authorization` header and/or cookies
- Symfony authenticator that validates the token's signature against your public key
- A `BiscuitVoter` that runs your Datalog policies against the request, fully driven by `#[IsGranted]`
- Token attenuation through reusable block templates, with an event for audit and a console command for debugging
- Configurable token caching and revocation checking
- A web profiler panel showing the current token, its blocks, every policy decision, and every attenuation performed during the request
- Console commands to generate keys, mint tokens from templates, attenuate tokens, and inspect tokens
- A `make:biscuit-policy` maker
- Test helpers to mint tokens and authenticate functional tests

Read the Datalog reference at [biscuitsec.org/docs/reference/datalog](https://www.biscuitsec.org/docs/reference/datalog/).

## Requirements

- PHP 8.1 or higher
- Symfony 6.4, 7.4, or 8.0
- The `biscuit-php` PHP extension (version 0.5.0)

## Installation

Install the PHP extension via [pie](https://github.com/php/pie):

```bash
pie install ptondereau/biscuit-php:0.5.0
```

Install the bundle via Composer:

```bash
composer require ptondereau/biscuit-symfony-bundle
```

If you are not using Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Biscuit\BiscuitBundle\BiscuitBundle::class => ['all' => true],
];
```

## Quick Start

Generate a key pair:

```bash
bin/console biscuit:keys:generate
```

Configure the bundle (`config/packages/biscuit.yaml`):

```yaml
biscuit:
    keys:
        public_key: '%env(BISCUIT_PUBLIC_KEY)%'
        private_key: '%env(BISCUIT_PRIVATE_KEY)%'
    policies:
        admin_only: 'allow if role("admin")'
```

Wire the authenticator into your firewall (`config/packages/security.yaml`):

```yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            custom_authenticators:
                - biscuit.authenticator
```

Protect a controller with `#[IsGranted]`:

```php
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController
{
    #[Route('/api/admin', methods: ['GET'])]
    #[IsGranted('admin_only')]
    public function index(): Response
    {
        return new JsonResponse(['ok' => true]);
    }
}
```

That's it. Requests without a valid token get `401`, requests whose token does not satisfy the policy get `403`.

## Configuration Reference

```yaml
biscuit:
    keys:
        public_key:        ~       # Public key in hex
        private_key:       ~       # Private key in hex
        public_key_file:   ~       # Path to public key file (alternative to public_key)
        private_key_file:  ~       # Path to private key file (alternative to private_key)
        algorithm:         ed25519 # ed25519 or secp256r1

    security:
        user_identifier_fact: user  # Authority block fact holding the user identifier
        www_authenticate:     true  # Send an RFC 6750 challenge on failure
        realm:                api   # Realm advertised in that challenge
        token_extractor:
            header: true     # Extract token from Authorization header
            cookie: false    # Cookie name to read from, or false to disable

    revocation:
        enabled:               false     # Enable revocation checking
        on_unavailable:        ~         # allow or deny; REQUIRED when enabled
        dispatch_check_events: on_revoke # never, on_revoke or always
        default_expiry:        ~         # Seconds to keep an entry of unknown expiry
        stores:
            static:
                ids:  []     # Revoked ids, or '%env(csv:BISCUIT_REVOKED_IDS)%'
                file: ~      # Newline-delimited or JSON list of revoked ids
            cache:
                enabled:    false
                pool:       ~                 # Null creates cache.biscuit.revocation
                adapter:    cache.app         # Parent adapter for that pool
                key_prefix: biscuit_revoked_
            in_memory:
                enabled: false

    policies:                # Named policies referenced by #[IsGranted]
        admin_only:    'allow if role("admin")'
        scope_read:    'allow if scope({resource}, "read")'

    token_templates:         # Templates used by BiscuitTokenFactory and biscuit:token:create
        admin_token:
            facts:
                - 'user({id})'
                - 'role("admin")'
            checks:
                - 'check if time($t), $t < {expiry}'
            rules: []

    block_templates:         # Templates used by BiscuitBlockFactory and biscuit:token:attenuate
        read_only:
            checks:
                - 'check if operation("read")'
        expires:
            checks:
                - 'check if now($t), $t <= {exp}'

    authorizer_fact_templates:   # Facts injected into the authorizer, keyed by #[IsGranted] policy name
        credit_authorized:
            facts:
                - 'operation("credit_wallet")'
                - 'amount({amount})'
```

## Key Management

The `KeyManager` service exposes the configured key pair. Three input forms are supported:

- Inline hex via `keys.public_key` and `keys.private_key` (recommended via env vars).
- Files via `keys.public_key_file` and `keys.private_key_file`.
- A new pair generated on demand if none is configured (only useful in tests).

To generate a fresh pair from the CLI:

```bash
bin/console biscuit:keys:generate --algorithm=ed25519
```

The command prints the hex-encoded keys to stdout. Store them in your secrets manager and inject them via environment variables.

## Token Extraction

Tokens are extracted by the `ChainTokenExtractor`, which delegates to one or more named extractors in order:

- `HeaderTokenExtractor` reads `Authorization: Bearer <token>` from the request.
- `CookieTokenExtractor` reads a configurable cookie name.

Enable both:

```yaml
biscuit:
    security:
        token_extractor:
            header: true
            cookie: biscuit_token
```

The chain stops at the first extractor that returns a non-null value. To add a custom extractor, implement `Biscuit\BiscuitBundle\Token\Extractor\TokenExtractorInterface` and register it as a service tagged into the chain.

## Authentication

The bundle ships a single authenticator: `BiscuitAuthenticator`. Add it as a `custom_authenticators` entry on any stateless firewall that should accept Biscuit tokens.

A successful authentication produces a `BiscuitUser` whose `getBiscuit()` method returns the verified token, which is then available throughout the request.

The `BiscuitBadge` is attached to the security passport so downstream voters and listeners can detect Biscuit-authenticated requests.

**The authenticator does not deny anonymous requests.** `supports()` returns false when no token
is present, so a request without one never reaches the authenticator. Deny it yourself with
`access_control` or `#[IsGranted]`, or the endpoint you believe is protected is public:

```yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            provider: your_provider
            custom_authenticators:
                - biscuit.authenticator
            entry_point: biscuit.authenticator

    access_control:
        - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
```

Setting `entry_point` lets the bundle answer anonymous requests too, so they get the same JSON
body and `WWW-Authenticate` challenge as a rejected token.

### Failure Responses

Failures return 401 with an RFC 6750 body and challenge, so clients can tell "get a new token"
apart from "you sent nothing":

| Cause | `WWW-Authenticate` | Body `error` |
| --- | --- | --- |
| No token sent | `Bearer realm="api"` | `invalid_request` |
| Malformed, forged or revoked token | `Bearer realm="api", error="invalid_token", ...` | `invalid_token` |

Turn the header off with `biscuit.security.www_authenticate: false`, and change the realm with
`biscuit.security.realm`.

## Authorization

Policies are referenced by name from `#[IsGranted]`:

```yaml
biscuit:
    policies:
        admin_only:   'allow if role("admin")'
        owner_only:   'allow if user({user_id})'
        scope_read:   'allow if scope({resource}, "read")'
```

```php
#[IsGranted('admin_only')]
public function dashboard(): Response { /* ... */ }

#[IsGranted('owner_only', subject: ['user_id' => $userId])]
public function profile(int $userId): Response { /* ... */ }

#[IsGranted('scope_read', subject: $resource)]
public function show(string $resource): Response { /* ... */ }
```

The voter resolves the policy and runs an authorizer over the verified token. The `subject:` argument is bound into the policy as parameters:

- A string subject becomes `{resource}` in the policy.
- An object with `getId()` becomes `{resource}` as the string-cast id.
- An associative array is bound key-by-key (use this for multi-parameter policies).

You can also pass a Datalog string directly to `#[IsGranted]` for ad-hoc policies that aren't worth naming:

```php
#[IsGranted('allow if scope({resource}, "read")', subject: $resource)]
```

If the configured policies do not match, the voter abstains and falls back to other voters.

## Authorizer Fact Templates

Policies and token checks can only reason about facts that are present in the authorizer. Token-side facts come from the token itself; **request-context** facts (the amount being moved, the current time, the target resource's tier, ...) must be supplied by the verifier at authorization time. `authorizer_fact_templates` lets you declare those facts in configuration and have `BiscuitVoter` inject them automatically, keyed by policy name:

```yaml
biscuit:
    policies:
        credit_authorized: 'allow if role("agent"), operation("credit_wallet"), amount($a), $a <= 100000, geo({req_zone})'

    authorizer_fact_templates:
        credit_authorized:                 # same name as the policy
            facts:
                - 'operation("credit_wallet")'
                - 'amount({amount})'
                - 'geo({req_zone})'
                - 'time({now})'
```

```php
#[IsGranted('credit_authorized', subject: [
    'req_zone' => $wilaya,
    'amount'   => $amountInDinars,
    'now'      => time(),
])]
public function credit(): Response { /* ... */ }
```

When `credit_authorized` is evaluated, the voter applies the same-named fact template against the `subject:` parameters and adds the resulting facts to the authorizer before `authorize()` runs; the token's own checks are evaluated against those facts too. The `subject:` array may carry more keys than the policy references: extra keys feed the fact template and are ignored by the policy.

## Token Templates

Define reusable token shapes in configuration:

```yaml
biscuit:
    token_templates:
        admin_token:
            facts:
                - 'user({id})'
                - 'role("admin")'
        scoped_reader:
            facts:
                - 'user({id})'
                - 'scope({resource}, "read")'
```

Mint tokens from templates with `BiscuitTokenFactory`:

```php
use Biscuit\BiscuitBundle\Token\BiscuitTokenFactory;

final class IssueTokenAction
{
    public function __construct(private readonly BiscuitTokenFactory $factory) {}

    public function __invoke(int $userId, string $dog): string
    {
        $token = $this->factory->create('scoped_reader', [
            'id' => $userId,
            'resource' => $dog,
        ]);

        return $token->toBase64();
    }
}
```

## Block Templates and Attenuation

A holder of a Biscuit can derive a more restricted token by appending a block. Attenuation can only narrow authority; it can never widen it. The bundle exposes this through `BiscuitBlockFactory`, fed by reusable block templates declared in configuration:

```yaml
biscuit:
    block_templates:
        read_only:
            checks:
                - 'check if operation("read")'
        expires:
            checks:
                - 'check if now($t), $t <= {exp}'
        single_resource:
            checks:
                - 'check if resource({res})'
```

Apply a template to an existing token:

```php
use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Token\BiscuitBlockFactory;

final class ShareLinkAction
{
    public function __construct(private readonly BiscuitBlockFactory $blockFactory) {}

    public function __invoke(Biscuit $parent, string $resource): Biscuit
    {
        $derived = $this->blockFactory->attenuate($parent, 'single_resource', [
            'res' => $resource,
        ]);

        return $this->blockFactory->attenuate($derived, 'expires', [
            'exp' => time() + 3600,
        ]);
    }
}
```

For composing several templates into a single block (one extra block instead of N), use `buildBlock()` plus the underlying `BlockBuilder::merge()` before passing the result to `BiscuitTokenManager::attenuate()`.

Every successful attenuation dispatches `Biscuit\BiscuitBundle\Event\BiscuitTokenAttenuatedEvent` from `BiscuitTokenManager`, with `parent`, `blockSource`, and `child` readonly properties. The bundle's data collector subscribes to it so the profiler shows the full derivation chain; you can subscribe additional listeners for audit logging or metrics.

## Console Commands

| Command | Purpose |
|---|---|
| `biscuit:keys:generate`   | Generate an ed25519 or secp256r1 key pair |
| `biscuit:token:create`    | Mint a token from a configured template |
| `biscuit:token:attenuate` | Append a block to an existing token, from a template or inline Datalog |
| `biscuit:token:inspect`   | Decode and pretty-print a Biscuit token |
| `biscuit:policy:test`     | Run a configured policy against a token |
| `biscuit:revocation:revoke` | Revoke a token or a raw revocation identifier |
| `biscuit:revocation:check`  | Check a token, exiting 0 valid, 1 revoked, 2 error |
| `biscuit:revocation:list`   | List entries of every enumerable store |
| `biscuit:revocation:purge`  | Drop entries whose expiration has passed |

Each command exposes `--help` for the full option list.

## Maker

If you also have `symfony/maker-bundle` installed:

```bash
bin/console make:biscuit-policy ArticleViewerPolicy
```

This generates `src/Security/Policy/ArticleViewerPolicy.php` with a documented skeleton including a `NAME` constant, a `POLICY` Datalog string, and a usage example with `#[IsGranted]`.

## Token Revocation

A token is revoked when **any** identifier in its revocation chain is listed. Revoking a parent
identifier therefore kills every token attenuated from it, which is what keeps offline
attenuation from defeating revocation. Revoking the deepest identifier does the opposite: that
token stops working and its ancestors keep working, so you can log one device out without
invalidating every share link the user ever minted.

Identifiers come from the token structure, so you never add a `jti` yourself, and the list holds
no secrets. You can hand it to every service that verifies tokens.

### Turning It On

Two settings are required. There is no default for `on_unavailable` because the right answer
depends on whether an unreachable revocation list should take your API down:

```yaml
biscuit:
    revocation:
        enabled: true
        on_unavailable: deny
        stores:
            cache:
                enabled: true
```

- `deny` rejects the request with a 500 when a store cannot answer. The control never silently
  stops working, at the cost of turning a Redis blip into an outage.
- `allow` accepts the request, logs an error, dispatches `BiscuitRevocationDegradedEvent` and
  marks the check degraded in the profiler. Revocation stops being enforced in full while the
  store is down.

Enabling revocation with no store fails the container build. "Revocation is on" while every
token passes would be worse than a hard failure.

### Stores

Stores are consulted in priority order and the first match wins.

| Store | Priority | Writable | Notes |
| --- | --- | --- | --- |
| `static` | 256 | no | In-memory list from config, an env var or a file. No I/O on the request path. |
| `in_memory` | 192 | yes | Per-process list. For tests and worker runtimes. |
| `cache` | 128 | yes | PSR-6 pool. Point it at Redis and every instance converges. |
| yours | 0 | optional | Any service implementing `RevocationStoreInterface`. |

The static store is the break-glass option. It needs nothing provisioned:

```yaml
biscuit:
    revocation:
        enabled: true
        on_unavailable: deny
        stores:
            static:
                ids: '%env(csv:BISCUIT_REVOKED_IDS)%'
                file: '%kernel.project_dir%/config/revoked_ids.txt'
```

The cache store gets its own pool, `cache.biscuit.revocation`, rather than sharing `cache.app`.
A routine `cache:pool:clear cache.app` would otherwise un-revoke every token. Set
`stores.cache.pool` to reuse a pool you already run.

### Writing Your Own Store

Implement one method. Autoconfiguration wires it up, so the class needs no tag and no service
config:

```php
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;

final class DoctrineRevocationStore implements RevocationStoreInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findRevoked(array $revocationIds): ?string
    {
        $found = $this->connection->fetchOne(
            'SELECT revocation_id FROM revoked_tokens WHERE revocation_id IN (?)',
            [$revocationIds],
            [ArrayParameterType::STRING],
        );

        return false === $found ? null : $found;
    }
}
```

Resolve the whole batch in one round trip, and return the identifier as you received it so
callers can correlate it with the token. Throw `RevocationStoreUnavailableException` when the
backend cannot answer; that is what `on_unavailable` reacts to. Add
`RevocationWriterInterface` if the store can be written to, and
`EnumerableRevocationStoreInterface` if it can list its entries.

### Revoking a Token

Inject `RevocationWriterInterface` and hand it an entry:

```php
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;

public function logout(RevocationEntryFactory $factory, RevocationWriterInterface $writer): void
{
    $writer->revoke($factory->fromToken($this->currentToken, reason: 'logout'));
}
```

`fromToken()` targets the deepest identifier and reads the expiration and subject from the
authority block. Use `allFromToken()` only when you mean to invalidate sibling tokens too.

Give every token an expiration date. Entries with no expiration can never be purged, so the
list grows forever, and `biscuit:revocation:list` will nag you about them.

### Revocation Commands

```bash
bin/console biscuit:revocation:revoke <token> [--all-ids] [--ttl=86400] [--dry-run]
bin/console biscuit:revocation:revoke --id=<hex> --id=<hex>
bin/console biscuit:revocation:check <token> [--explain]
bin/console biscuit:revocation:list [--format=table|json|txt] [--subject=alice] [--expired]
bin/console biscuit:revocation:purge [--before=2026-01-01T00:00:00Z] [--force]
```

`revoke` prints the whole chain with the target marked before it writes anything, and reads the
token without its signature unless you pass `--verify`.

`check` exits `0` when the token is valid, `1` when it is revoked and `2` on error, so it drops
straight into a health check.

`list --format=txt` emits bare newline-delimited identifiers, which is the format the static
store reads. Promoting a dynamic list to a static one is one pipe:

```bash
bin/console biscuit:revocation:list --format=txt > config/revoked_ids.txt
```

### Events

- `BiscuitRevocationCheckedEvent` carries the full `RevocationResult`. Checks run on every
  authenticated request, so it only fires for revoked tokens by default. Set
  `dispatch_check_events` to `always` or `never` to change that.
- `BiscuitTokenRevokedEvent` fires when an entry is written.
- `BiscuitRevocationDegradedEvent` fires when a store fails, under either failure policy, so
  alerting works whether the request was rejected or let through.

### Where the Check Sits

The authenticator checks revocation after signature verification, never before. Revocation
identifiers are derived from the block signatures, so until `parse()` succeeds an attacker
controls them. Checking earlier would let anonymous requests drive lookups with arbitrary keys
into Redis or SQL, and would report a forged token as revoked rather than invalid.

## Web Profiler Integration

When `symfony/web-profiler-bundle` is installed in the dev environment, the bundle adds a Biscuit panel showing:

- Whether a token was attached to the request, with block count and revocation IDs
- All blocks in the token (Datalog source)
- Every policy check performed during the request, with parameters and pass/fail outcome
- Every attenuation performed during the request, with parent and child revocation IDs and the appended block source
- The revocation verdict: which store answered, how long each store took, and whether the check ran degraded

The panel is populated for rejected requests too, so a revoked token still shows its blocks and
the store that matched. The toolbar turns red when a token is revoked and yellow when a store
could not answer.

## Testing Helpers

The `Biscuit\BiscuitBundle\Test` namespace provides utilities for functional tests.

`BiscuitTestTrait` mints tokens against a per-test-class key pair without needing to mock anything:

```php
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProtectedEndpointTest extends WebTestCase
{
    use BiscuitTestTrait;

    public function testAdminCanAccess(): void
    {
        $token = $this->createTestTokenBase64('user(1); role("admin")');

        $client = static::createClient();
        $client->request('GET', '/api/admin', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseIsSuccessful();
    }
}
```

`TestBiscuitAuthenticator` is a drop-in replacement for the production authenticator that trusts tokens signed by the test key pair.

`BiscuitFixtures` and `BiscuitFixtureLoader` load Datalog scenarios from YAML files for repeatable fixture data.

## Development

Run the full quality gate locally:

```bash
composer check
```

This runs `php-cs-fixer` (dry run), `phpstan` at level 8, and the PHPUnit suite.

To auto-fix style issues:

```bash
composer cs-fix
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). All contributors are expected to follow the [Code of Conduct](CODE_OF_CONDUCT.md).

## Security

Vulnerabilities should be reported to `security@biscuitsec.org`. See [SECURITY.md](SECURITY.md) for details.

## License

Apache License 2.0. See [LICENSE](LICENSE).
