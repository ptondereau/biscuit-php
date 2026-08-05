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
            doctrine:
                enabled:    false
                connection: doctrine.dbal.default_connection
                table:      biscuit_revoked_tokens
        push:
            enabled: false                  # Broadcast writes over Symfony Messenger
            bus:     messenger.default_bus  # Bus used to dispatch and to register handlers

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

A *missing* key denies the check. If the policy or the fact template references a parameter the `subject:` array does not provide, the voter votes deny, records a failed check in the profiler and logs a warning naming the policy and the error behind it. A malformed Datalog snippet in the template behaves the same way.

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
| `doctrine` | 64 | yes | Database table. Survives a restart, and does the slowest I/O, so it answers last. |
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
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationStoreInterface;

final class ApiRevocationStore implements RevocationStoreInterface
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function findRevoked(array $revocationIds): ?string
    {
        try {
            $revoked = $this->client
                ->request('POST', '/revocations/check', ['json' => ['ids' => $revocationIds]])
                ->toArray();
        } catch (ExceptionInterface $e) {
            throw new RevocationStoreUnavailableException($e->getMessage(), 0, $e);
        }

        foreach ($revocationIds as $revocationId) {
            if (\in_array($revocationId, $revoked['revoked'], true)) {
                return $revocationId;
            }
        }

        return null;
    }
}
```

Resolve the whole batch in one round trip, and return the identifier as you received it so
callers can correlate it with the token. Throw `RevocationStoreUnavailableException` when the
backend cannot answer; that is what `on_unavailable` reacts to. Nothing else may escape.
`RevocationChecker` catches only that exception, so any other one becomes a 500 on every
authenticated request no matter what `on_unavailable` says. Add `RevocationWriterInterface` if
the store can be written to, and `EnumerableRevocationStoreInterface` if it can list its
entries.

### Storing Revocations in a Database

Revocations kept in a database survive a restart. The cache store expires its entries and
`in_memory` dies with the process, so a node that comes back up starts accepting tokens that
were revoked while it was down. It needs `doctrine/dbal`.

```yaml
biscuit:
    revocation:
        enabled: true
        on_unavailable: deny
        stores:
            doctrine:
                enabled: true
                connection: doctrine.dbal.default_connection
                table: biscuit_revoked_tokens
```

It reads, writes, enumerates and purges. `biscuit:revocation:purge` deletes rows and reports a
real count, which the cache store cannot do because a PSR-6 pool expires entries on its own.

MySQL, MariaDB, PostgreSQL and SQLite are supported. Each needs a different upsert, and the
bundle picks it from the platform. Oracle, SQL Server and DB2 fail with a named error rather
than a syntax error, so implement `RevocationStoreInterface` yourself on those.

#### The table

The bundle declares the table but never creates it. Your migrations own the DDL:

```bash
bin/console doctrine:migrations:diff   # picks the table up, produces a reviewable migration
bin/console doctrine:migrations:migrate
```

Without migrations:

```bash
bin/console biscuit:revocation:doctrine:setup            # create it
bin/console biscuit:revocation:doctrine:setup --dump-sql # print the DDL instead
```

If you use the ORM, keep `doctrine/doctrine-bundle` installed. The bundle hooks
`postGenerateSchema` to declare the table, and that hook is what stops
`doctrine:migrations:diff` from generating `DROP TABLE biscuit_revoked_tokens`. Run that
migration and revocation is silently off: every token passes and nothing is logged.

`revocation_id` is the primary key, and `expires_at` is indexed so purging is not a full scan.
On MySQL the identifier column is pinned to `utf8mb4_bin`, because a default `_ci` collation
matches case-, accent- and trailing-space-insensitively and would revoke a token that was
never revoked.

#### Revoke outside your own transaction

`ChainRevocationWriter` writes every store in turn, and the volatile ones come first. If you
call `revoke()` inside a transaction that later rolls back, the cache says revoked and the
database does not. The token keeps failing until the cache entry lapses, then quietly starts
working again. Revoke after you commit, or outside the transaction.

Dates are stored in UTC regardless of your `date.timezone`, and sub-second precision is not
portable across drivers, so timestamps round to the second. Note that
`biscuit:revocation:purge --before=2026-01-01` parses that date in PHP's default timezone, so
pass an explicit offset when it matters.

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

### Pushing Revocations to Other Instances

Push gives you the shortest gap between "revoked" and "rejected everywhere", which is why the
[Biscuit revocation guide](https://www.biscuitsec.org/docs/guides/revocation/) rates a
subscribe-on-startup queue the safest of the four distribution strategies. It also asks the
most of your infrastructure.

If you can point `stores.cache.pool` at Redis, do that first. Every instance reads the same
list, nothing new to operate, and the gap is one cache lookup. Push is what you reach for when
that shortcut does not apply: worker runtimes such as FrankenPHP or RoadRunner where each
instance holds its own `in_memory` list and there is no shared store to converge on.

```yaml
biscuit:
    revocation:
        enabled: true
        on_unavailable: deny
        stores:
            in_memory:
                enabled: true
        push:
            enabled: true
```

Push needs `symfony/messenger`. The container refuses to build without it rather than
silently doing nothing.

That config alone is not enough to propagate anything. Four more steps follow, and skipping
any of them fails quietly.

#### Step 1: pick a transport that can fan out

Messenger hides which broker you run, but it does not hide delivery topology, and there is no
`framework.messenger` setting that means "broadcast". You express it in transport-specific
`options`, and most transports cannot express it at all:

| Transport | Can deliver to every instance |
| --- | --- |
| AMQP | Yes. Fanout exchange, one queue per instance |
| Redis | Yes. One consumer group per instance |
| Doctrine | No. A row is claimed by a single consumer |
| Beanstalkd | No. Work queue only |
| Amazon SQS | Not on its own. Needs SNS in front of one queue per instance, which the bridge does not manage |
| Kafka | No official bridge. Symfony points at [Enqueue](https://github.com/php-enqueue/enqueue-dev) |
| `sync://`, `in-memory://` | No. One process, nothing leaves it |

Routing these messages to Doctrine or Beanstalkd is the failure this feature exists to prevent:
each message goes to exactly one consumer, so one instance stops accepting the token and every
other node keeps going. Nothing errors. `doctrine://default` is the tempting default when you
have no broker, and it is the wrong answer here. Without AMQP or Redis, use a shared
`stores.cache` pool instead of push.

#### Step 2: give every instance its own queue

The guide's model is one queue per subscriber. A fanout exchange with a queue per instance, or
Redis with a consumer group per instance.

`INSTANCE_ID` has to differ per running instance and stay stable across a restart, or the
instance subscribes to a fresh empty queue every boot. On Kubernetes use the pod name
(`valueFrom.fieldRef.fieldPath: metadata.name`); with Docker Compose replicas use the
container hostname; on bare metal use the hostname.

With AMQP:

```yaml
framework:
    messenger:
        transports:
            biscuit_revocation:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    exchange: { name: biscuit_revocation, type: fanout }
                    queues:   { 'biscuit_revocation_%env(INSTANCE_ID)%': ~ }
```

With Redis, the consumer group is what separates the subscribers, and it needs
`symfony/redis-messenger`. `delete_after_ack: false` is required, not a tuning choice: the
default deletes each message once one group acks it, which drops it before the other
instances have read it. Cap the stream with `stream_max_entries` instead:

```yaml
framework:
    messenger:
        transports:
            biscuit_revocation:
                dsn: '%env(REDIS_TRANSPORT_DSN)%'
                options:
                    stream:             biscuit_revocation
                    group:              '%env(INSTANCE_ID)%'
                    consumer:           '%env(INSTANCE_ID)%'
                    delete_after_ack:   false
                    stream_max_entries: 10000
```

Redis behaves better than AMQP on restart. Symfony creates the consumer group at offset `0`,
so a group reads the stream from the beginning, and a group that already exists resumes from
what it last acked. Either way a returning instance replays everything still in the stream, so
`stream_max_entries` is what bounds the catch-up window rather than the message being gone the
moment it was delivered. Size it for your worst expected downtime.

The cost is bookkeeping. One group per instance means retired instances leave their groups
behind, holding pending entries in Redis metadata. Prefer a stable identity that gets reused
(a StatefulSet ordinal, not a random pod name) and drop groups for instances you have retired
with `XGROUP DESTROY`.

#### Step 3: route the three messages

```yaml
framework:
    messenger:
        routing:
            'Biscuit\BiscuitBundle\Revocation\Message\RevokeToken':             biscuit_revocation
            'Biscuit\BiscuitBundle\Revocation\Message\UnrevokeToken':           biscuit_revocation
            'Biscuit\BiscuitBundle\Revocation\Message\PurgeExpiredRevocations': biscuit_revocation
```

Leave a message out of `routing` and Messenger handles it in-process the moment it is
dispatched. The revoking node applies it twice, harmlessly, and no other node ever hears
about it. Local testing looks fine and production propagates nothing, so route all three or
none.

#### Step 4: run a consumer on every instance

Nothing arrives until something reads the queue:

```bash
bin/console messenger:consume biscuit_revocation
```

Run it as a supervised process alongside each instance, not as one shared worker. A single
worker for the whole cluster puts the revocation list on one machine, which is the problem
push exists to solve. Give it `--time-limit` and let your supervisor restart it, as with any
Messenger worker.

Nothing else changes in your code. `RevocationWriterInterface` now resolves to a publisher
that writes locally and then broadcasts, so the commands and the `logout()` above start
propagating without an edit. The node doing the revoking never waits for its own message.

#### Checking that it works

Revoke on one instance and check from another. `biscuit:revocation:check` exits `0` for a
valid token and `1` for a revoked one, so this works as a shell test:

```bash
# on instance A
bin/console biscuit:revocation:revoke "$TOKEN" --ttl=3600

# on instance B, a moment later
bin/console biscuit:revocation:check "$TOKEN" && echo 'PUSH IS NOT WORKING'
```

If instance B still exits `0`, the queue is the place to look. One queue shared by every
consumer is the usual cause, and `messenger:stats` will show a single queue draining instead
of one per instance.

A malformed message is rejected rather than written, so it follows your retry strategy and
lands in `failure_transport` if you configured one. Set one up: a revocation stuck in a retry
loop is a revocation that is not being enforced.

Two services keep a consumed message from being broadcast again. The publisher writes and
dispatches; the handler holds a writer that only writes. A handler can't reach the bus, so
there is no loop to break and no origin identifier to track. A local write that fails stops
the broadcast, because telling the cluster about a revocation this node could not apply
would leave it inconsistent.

The three messages carry strings only, and dates travel as RFC 3339, so they survive the
JSON and AMQP serializers unchanged.

Events split along the same line. The origin node fires `BiscuitTokenRevokedEvent` as it
always did. Consuming nodes fire `BiscuitRevocationReceivedEvent` instead, so a listener
that emails "your session ended" sends one mail rather than one per node:

```php
use Biscuit\BiscuitBundle\Event\BiscuitRevocationReceivedEvent;
use Biscuit\BiscuitBundle\Revocation\RevocationPushOperation;

#[AsEventListener]
public function onReceived(BiscuitRevocationReceivedEvent $event): void
{
    match ($event->operation) {
        RevocationPushOperation::Revoke => $this->warmCaches($event->entry),
        RevocationPushOperation::Unrevoke => $this->clearCaches($event->revocationId),
        RevocationPushOperation::Purge => $this->recordPurge($event->purged),
    };
}
```

`purgeExpired()` returns the count this node dropped. Counts from the other instances cannot
be collected back across a fanout. The cutoff is resolved before dispatch, so every node
purges to the same instant instead of to its own clock, which is also why
`biscuit:revocation:purge` reports one node's number in a cluster.

#### What push does not give you

With AMQP, an instance that boots after a revocation was broadcast never sees that message,
so a restart brings a node back accepting the token when `in_memory` is the only store. Redis
replays what is still in the stream, which narrows the gap to entries trimmed past
`stream_max_entries` but does not close it. Two ways to cover it, and you want one of them:

- Keep a second store behind the in-memory one. The `doctrine` store holds entries until you
  purge them; a `cache` store on a shared pool answers until they expire.
- Promote long-lived revocations to the static list. `biscuit:revocation:list --format=txt`
  writes the exact format `stores.static.file` reads, so a node has them at startup.

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
- `BiscuitTokenRevokedEvent` fires when an entry is written locally, on the node doing the
  writing.
- `BiscuitRevocationDegradedEvent` fires when a store fails, under either failure policy, so
  alerting works whether the request was rejected or let through.
- `BiscuitRevocationReceivedEvent` fires on a node that applied a change pushed from another
  instance. See [Pushing Revocations to Other Instances](#pushing-revocations-to-other-instances).

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

### Testing Revocation

`BiscuitRevocationTestTrait` answers the question a revoked token actually raises: how does my
endpoint behave? `assertTokenRevoked()` and `assertTokenNotRevoked()` work against whichever
stores you configured, with no knowledge of which one answered.

`receiveRevocation()` applies a revocation the way a consuming node does, so you can test the
case that is otherwise hard to reach: another instance revoked this token and yours has not
handled the message through a broker. It writes locally and publishes nothing, which is exactly
what the push handler does. It takes a `Biscuit`, an `UnverifiedBiscuit`, a `RevocationEntry` or
a raw identifier, and a token is revoked by its deepest identifier, matching
`RevocationEntryFactory::fromToken()`.

```php
use Biscuit\BiscuitBundle\Test\BiscuitRevocationTestTrait;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LogoutTest extends WebTestCase
{
    use BiscuitRevocationTestTrait;
    use BiscuitTestTrait;

    public function testAnotherInstanceRevokedTheToken(): void
    {
        $client = static::createClient();
        $token = $this->createTestToken('user("alice")');

        $this->receiveRevocation($token, reason: 'logout');

        $this->assertTokenRevoked($token);

        $client->request('GET', '/api/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }
}
```

The assertions need `biscuit.revocation.enabled`. The `receive` helpers also need
`biscuit.revocation.push.enabled`, and say so if it is off rather than failing obscurely.

To assert the other direction, that your code published a revocation, route the messages to
`in-memory://` in the test environment and read the transport. No bundle helper is involved:

```yaml
# config/packages/test/messenger.yaml
framework:
    messenger:
        transports:
            biscuit_revocation: 'in-memory://'
```

```php
$this->writer->revoke($this->entryFactory->fromToken($token, reason: 'logout'));

$transport = static::getContainer()->get('messenger.transport.biscuit_revocation');
self::assertInstanceOf(RevokeToken::class, $transport->getSent()[0]->getMessage());
```

One thing no test can tell you: whether your transport really fans out to every instance. That
is a property of your broker topology, not of your code, so verify it against a running cluster
with the `biscuit:revocation:check` recipe above.

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
