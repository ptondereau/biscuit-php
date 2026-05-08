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
- The `biscuit-php` PHP extension (version 0.4.0)

## Installation

Install the PHP extension via [pie](https://github.com/php/pie):

```bash
pie install ptondereau/biscuit-php:0.4.0
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
        token_extractor:
            header: true     # Extract token from Authorization header
            cookie: false    # Cookie name to read from, or false to disable

    cache:
        enabled: false       # Enable token verification caching
        pool:    cache.app   # Cache pool service ID
        ttl:     3600        # Cache TTL in seconds

    revocation:
        enabled: false       # Enable token revocation checking
        service: ~           # Revocation checker service ID

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

A successful authentication produces a `BiscuitUser` whose `getBiscuit()` method returns the verified token, which is then available throughout the request. Failed authentication throws `RevokedTokenException` (when revocation is enabled and the token is on the revocation list) or returns a generic 401 for invalid signatures, malformed tokens, and missing extractors.

The `BiscuitBadge` is attached to the security passport so downstream voters and listeners can detect Biscuit-authenticated requests.

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

Each command exposes `--help` for the full option list.

## Maker

If you also have `symfony/maker-bundle` installed:

```bash
bin/console make:biscuit-policy ArticleViewerPolicy
```

This generates `src/Security/Policy/ArticleViewerPolicy.php` with a documented skeleton including a `NAME` constant, a `POLICY` Datalog string, and a usage example with `#[IsGranted]`.

## Token Caching

For high-throughput APIs you can cache successful token verifications to avoid re-running signature checks on every request:

```yaml
biscuit:
    cache:
        enabled: true
        pool: cache.app
        ttl: 600
```

Cache keys are derived from the serialized token. Failed verifications are never cached.

## Token Revocation

To enforce a revocation list, implement `Biscuit\BiscuitBundle\Cache\Revocation\RevocationCheckerInterface` and wire it in:

```yaml
biscuit:
    revocation:
        enabled: true
        service: App\Security\MyRevocationChecker
```

A reference implementation backed by a Symfony cache pool is provided as `Biscuit\BiscuitBundle\Cache\Revocation\CacheRevocationChecker`.

When revocation is enabled, the authenticator throws `RevokedTokenException` for any token whose revocation IDs intersect the revoked set. Revocation checks happen after signature verification but before policy evaluation.

## Web Profiler Integration

When `symfony/web-profiler-bundle` is installed in the dev environment, the bundle adds a Biscuit panel showing:

- Whether a token was attached to the request, with block count and revocation IDs
- All blocks in the token (Datalog source)
- Every policy check performed during the request, with parameters and pass/fail outcome
- Every attenuation performed during the request, with parent and child revocation IDs and the appended block source

The toolbar shows a green/red indicator and the count of policy checks.

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
