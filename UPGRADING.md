# Upgrading Guide

## Upgrading from v0.4.x to v0.5.0

v0.5.0 replaces the flat list of `Invalid*` exception classes with a deep typed hierarchy under a shared `BiscuitException` base, and attaches structured payloads to `AuthorizationException` (matched policy and failed checks) and to all `DatalogException` subclasses (parse errors and parameter binding info). Every failure shape now has its own concrete exception class so callers can use idiomatic multi-catch instead of branching on a message string.

See [issue #14](https://github.com/ptondereau/biscuit-php/issues/14).

### What changed

#### Exception hierarchy

Every extension-thrown exception now extends `Biscuit\Exception\BiscuitException`, which extends `\Exception`. `catch (BiscuitException $e)` is the new catch-all idiom. Each failure category has a base class (`KeyException`, `DatalogException`, `FormatException`, `BuildException`) with concrete per-kind subclasses you can catch directly.

| Before (v0.4.x) | After (v0.5.0) |
|---|---|
| `InvalidPublicKey` | `PublicKeyException` extends `KeyException` |
| `InvalidPrivateKey` | `PrivateKeyException` extends `KeyException` |
| `InvalidFact` | `FactException` extends `DatalogException` |
| `InvalidRule` | `RuleException` extends `DatalogException` |
| `InvalidCheck` | `CheckException` extends `DatalogException` |
| `InvalidPolicy` | `PolicyException` extends `DatalogException` |
| `InvalidTerm` | `TermException` extends `DatalogException` |
| (Datalog scope failures, previously bundled into `InvalidTerm`) | `ScopeException` extends `DatalogException` |
| (Token base64 errors, previously raw `\Exception`) | `Base64Exception` extends `FormatException` |
| (Token byte errors, previously raw `\Exception`) | `BytesException` extends `FormatException` |
| (Token signature errors, previously raw `\Exception`) | `SignatureException` extends `FormatException` |
| (Snapshot / block-source errors, previously raw `\Exception`) | `SnapshotException` extends `FormatException` |
| (`BiscuitBuilder::build()` failures, previously raw `\Exception`) | `BiscuitBuildException` extends `BuildException` |
| (`Biscuit::append()` failures, previously raw `\Exception`) | `BlockAppendException` extends `BuildException` |
| (`AuthorizerBuilder::build()` / `query()` failures, previously raw `\Exception`) | `AuthorizerBuildException` extends `BuildException` |
| (`Biscuit::appendThirdParty()` failures, previously raw `\Exception`) | `ThirdPartyBlockAppendException` extends `BuildException` |
| `AuthorizerError` | `AuthorizationException` extends `BiscuitException` |
| `ThirdPartyRequestError` | `ThirdPartyException` extends `BiscuitException` |
| `BuilderConsumed` | `BuilderStateException` extends `BiscuitException` |

The class identity replaces the `getCode()` kind discriminant from earlier proposals: `catch (FactException $e)` is the v0.5.0 idiom rather than `catch (DatalogException $e) { if ($e->getCode() === 1) ... }`. `getCode()` returns the standard PHP `Throwable` default (0) on these exceptions; rely on the class hierarchy instead.

You can still catch a category at the base level (for example `catch (DatalogException $e)`) and let inheritance route any of its subclasses to that handler.

#### Structured payloads

| Class | New accessors |
|---|---|
| `AuthorizationException` | `getMatchedPolicy(): ?MatchedPolicy`, `getFailedChecks(): array<FailedCheck>` |
| Every `DatalogException` subclass (`FactException`, `RuleException`, `CheckException`, `PolicyException`, `TermException`, `ScopeException`) | `getParseErrors(): ?array<ParseError>`, `getMissingParameters(): ?array<string>`, `getUnusedParameters(): ?array<string>` |

New value objects:

| Class | Accessors |
|---|---|
| `Biscuit\Auth\MatchedPolicy` | `getKind(): string` (`"allow"` or `"deny"`), `getPolicyId(): int`, `getCode(): ?string` |
| `Biscuit\Auth\FailedCheck` | `getOrigin(): string` (`"block"` or `"authorizer"`), `getBlockId(): ?int`, `getCheckId(): int`, `getRule(): string` |
| `Biscuit\Auth\ParseError` | `getInput(): string`, `getMessage(): ?string` |

#### Other

- `Authorizer::authorize()` returns `Biscuit\Auth\MatchedPolicy` instead of `int`.
- The full upstream `Error::source()` chain is joined into `getMessage()`, so a `DatalogException` message now includes the parser error context.

### Migration

#### Renaming `Invalid*` Datalog catches

Before:
```php
use Biscuit\Exception\InvalidFact;
use Biscuit\Exception\InvalidRule;
use Biscuit\Exception\InvalidCheck;
use Biscuit\Exception\InvalidPolicy;
use Biscuit\Exception\InvalidTerm;

try {
    new Fact('not valid');
} catch (InvalidFact $e) {
    log($e->getMessage());
}
```

After:
```php
use Biscuit\Exception\FactException;

try {
    new Fact('not valid');
} catch (FactException $e) {
    log($e->getMessage());
}
```

Multi-catch lets you react to different Datalog failure shapes in one block:
```php
use Biscuit\Exception\FactException;
use Biscuit\Exception\RuleException;
use Biscuit\Exception\TermException;

try {
    $fact = new Fact('user({id})');
    $fact->set('id', $userId);
} catch (FactException $e) {
    // bad Fact source
} catch (TermException $e) {
    // bad term value passed to set()
} catch (RuleException $e) {
    // bad Rule source or binding
}
```

#### Renaming key catches

Before:
```php
use Biscuit\Exception\InvalidPublicKey;
use Biscuit\Exception\InvalidPrivateKey;

try {
    new PublicKey($maybeBadHex);
} catch (InvalidPublicKey $e) { /* ... */ }

try {
    new PrivateKey($maybeBadHex);
} catch (InvalidPrivateKey $e) { /* ... */ }
```

After:
```php
use Biscuit\Exception\PrivateKeyException;
use Biscuit\Exception\PublicKeyException;

try {
    new PublicKey($maybeBadHex);
} catch (PublicKeyException $e) { /* ... */ }

try {
    new PrivateKey($maybeBadHex);
} catch (PrivateKeyException $e) { /* ... */ }
```

#### Catching builder-state errors

Before:
```php
use Biscuit\Exception\BuilderConsumed;

try {
    $blockBuilder->addCode('...'); // after a merge
} catch (BuilderConsumed $e) { /* ... */ }
```

After:
```php
use Biscuit\Exception\BuilderStateException;

try {
    $blockBuilder->addCode('...');
} catch (BuilderStateException $e) { /* ... */ }
```

#### Catching third-party flow errors

Before:
```php
use Biscuit\Exception\ThirdPartyRequestError;

try {
    $request->createBlock($key, $block);
} catch (ThirdPartyRequestError $e) { /* ... */ }
```

After:
```php
use Biscuit\Exception\ThirdPartyException;

try {
    $request->createBlock($key, $block);
} catch (ThirdPartyException $e) { /* ... */ }
```

#### Authorization

`Authorizer::authorize()` now returns a `Biscuit\Auth\MatchedPolicy` carrying the policy kind, id, and source code. Failures throw `AuthorizationException` with structured matched-policy and failed-checks accessors.

Before:
```php
use Biscuit\Exception\AuthorizerError;

$idx = $authorizer->authorize();
if ($idx === 0) {
    // first allow policy matched
}
```

After:
```php
use Biscuit\Exception\AuthorizationException;

try {
    $policy = $authorizer->authorize();
    log(sprintf('matched %s policy #%d', $policy->getKind(), $policy->getPolicyId()));
} catch (AuthorizationException $e) {
    $matched = $e->getMatchedPolicy();
    if ($matched !== null) {
        log(sprintf(
            'rejected by %s policy #%d: %s',
            $matched->getKind(),
            $matched->getPolicyId(),
            $matched->getCode(),
        ));
    }

    foreach ($e->getFailedChecks() as $check) {
        log(sprintf(
            '%s check #%d failed: %s',
            $check->getOrigin(),
            $check->getCheckId(),
            $check->getRule(),
        ));
    }
}
```

A `null` matched policy means no policy matched at all (the `Logic::NoMatchingPolicy` upstream case); the failed-checks list still tells you which conditions blocked authorization.

#### Inspecting Datalog failures

Every `DatalogException` subclass carries the upstream `LanguageError` payload when there is one. Use it to surface parse errors and parameter-binding issues without parsing the message.

Parse failure:
```php
use Biscuit\Auth\Fact;
use Biscuit\Auth\ParseError;
use Biscuit\Exception\FactException;

try {
    new Fact('not valid datalog');
} catch (FactException $e) {
    $parseErrors = $e->getParseErrors();
    if ($parseErrors !== null) {
        foreach ($parseErrors as $parseError) {
            log(sprintf(
                'parse error at %s: %s',
                $parseError->getInput(),
                $parseError->getMessage() ?? '(no message)',
            ));
        }
    }
}
```

Unused parameter (the parameter map's extra keys surface at term-binding time):
```php
use Biscuit\Auth\Rule;
use Biscuit\Exception\TermException;

try {
    new Rule('foo({x}) <- bar({y})', ['z' => 1]);
} catch (TermException $e) {
    $unused = $e->getUnusedParameters();
    if ($unused !== null) {
        log('unused params: ' . implode(', ', $unused));
    }
}
```

Missing parameter at builder time:
```php
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\Rule;
use Biscuit\Exception\RuleException;

$rule = new Rule('foo({x}) <- bar({y})');

try {
    $builder = new BiscuitBuilder();
    $builder->addRule($rule);
} catch (RuleException $e) {
    $missing = $e->getMissingParameters();
    if ($missing !== null) {
        log('missing params: ' . implode(', ', $missing));
    }
}
```

If you do not care about which Datalog kind failed, catch the `DatalogException` base instead and the three accessors are still available. When the failure is not a Datalog parse or parameter issue (for example, a `null` value passed to `Fact::set()`), all three accessors return `null` and you can fall back to `getMessage()`.

#### Catching everything at once

If you do not need to distinguish failure shapes, the new base class is enough:

```php
use Biscuit\Exception\BiscuitException;

try {
    // anything from this extension
} catch (BiscuitException $e) {
    log($e->getMessage());
}
```

### Quick Migration Checklist

- [ ] Replace every `Invalid{Fact,Rule,Check,Policy,Term}` catch with the matching `{Fact,Rule,Check,Policy,Term}Exception` from `Biscuit\Exception`.
- [ ] Replace every `Invalid{Public,Private}Key` catch with `{Public,Private}KeyException` from `Biscuit\Exception`.
- [ ] Replace every `AuthorizerError` catch with `AuthorizationException`.
- [ ] Replace every `BuilderConsumed` catch with `BuilderStateException`.
- [ ] Replace every `ThirdPartyRequestError` catch with `ThirdPartyException`.
- [ ] Replace every `$result = $authorizer->authorize(); /* use as int */` with `MatchedPolicy` access (`->getPolicyId()`).
- [ ] If you previously wrapped token parsing or building in a bare `\Exception` catch, narrow it to the relevant `Base64Exception` / `BytesException` / `SignatureException` / `SnapshotException` / `BiscuitBuildException` / `BlockAppendException` / `AuthorizerBuildException` / `ThirdPartyBlockAppendException`.
- [ ] Optional: enrich logging with `getMatchedPolicy()` / `getFailedChecks()` (authorization failures) and `getParseErrors()` / `getMissingParameters()` / `getUnusedParameters()` (datalog failures) for actionable diagnostics.

## Upgrading from v0.3.x to v0.4.0

v0.4.0 aligns the extension name across the Rust crate, the `.so` filename, the Composer `extension-name`, and what `php -m` reports. There are no API changes; the migration is purely about how the extension is named, packaged, and loaded.

The aligned name is `biscuit_php` (with an underscore). PIE's `extension-name` validator only accepts letters, digits, and underscores, so a hyphen variant is not an option.

### What changed

| Concern | Before (v0.3.x) | After (v0.4.0) |
|---------|-----------------|----------------|
| Registered PHP module name (`php -m`) | `biscuit-php` | `biscuit_php` |
| Cargo `[package]` name | `biscuit-php` | `biscuit_php` |
| Cargo `[lib]` name | `biscuit` | `biscuit_php` |
| Local cargo build output | `libbiscuit.so` | `libbiscuit_php.so` |
| Composer `php-ext.extension-name` | `biscuit` | `biscuit_php` |
| Composer ext requirement | `ext-biscuit-php` | `ext-biscuit_php` |
| PIE-installed file | `biscuit.so` | `biscuit_php.so` |
| Linux/macOS release archive prefix | `php_biscuit-` | `php_biscuit_php-` |
| `extension=` directive in php.ini | `extension=biscuit.so` | `extension=biscuit_php.so` |

See [issue #30](https://github.com/ptondereau/biscuit-php/issues/30) for context.

### Migration

**1. Composer requires**

```json
"require": {
    "ext-biscuit_php": "*"
}
```

If you were on v0.3.x with `"ext-biscuit-php"` (the only string that worked then) or with the README's old `"ext-biscuit"`, change it to `"ext-biscuit_php"`.

**2. PIE users**

```bash
pie install ptondereau/biscuit-php:^0.4
```

Then enable the new filename:

```bash
docker-php-ext-enable biscuit_php
# or, manually:
# extension=biscuit_php.so
```

If you previously enabled `biscuit`, disable it (`docker-php-ext-disable biscuit` or remove the old `extension=biscuit.so` line) before enabling `biscuit_php` to avoid loading the module twice.

**3. Manual install (zip from GitHub release)**

The archive name now starts with `php_biscuit_php-` and contains `biscuit_php.so` rather than `biscuit.so`. Update your `extension=` line in php.ini accordingly.

**4. Building from source**

`cargo build` now produces `libbiscuit_php.so` (not `libbiscuit.so`). Update any local scripts:

```bash
# Before
php -dextension=./target/release/libbiscuit.so vendor/bin/phpunit

# After
php -dextension=./target/release/libbiscuit_php.so vendor/bin/phpunit
```

---

## Upgrading from v0.2.x to v0.3.0

v0.3.0 introduces API simplification changes that consolidate redundant methods and add optional parameters to constructors. This is a **breaking change** release.

### KeyPair Constructor

**Before (v0.2.x):**
```php
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\Algorithm;

// Default Ed25519
$kp = new KeyPair();

// Explicit algorithm required separate method
$kp = KeyPair::newWithAlgorithm(Algorithm::Secp256r1);
```

**After (v0.3.0):**
```php
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\Algorithm;

// Default Ed25519 (unchanged)
$kp = new KeyPair();

// Explicit algorithm now via constructor
$kp = new KeyPair(Algorithm::Secp256r1);
```

**Migration:** Replace all `KeyPair::newWithAlgorithm($alg)` calls with `new KeyPair($alg)`.

---

### Builder addCode Methods

The `addCodeWithParams()` method has been removed. Use `addCode()` with optional parameters instead.

**Before (v0.2.x):**
```php
$builder = new BiscuitBuilder();
$builder->addCode('user("alice")');
$builder->addCodeWithParams('resource({res})', ['res' => 'file1']);
```

**After (v0.3.0):**
```php
$builder = new BiscuitBuilder();
$builder->addCode('user("alice")');
$builder->addCode('resource({res})', ['res' => 'file1']);
```

**Affected classes:**
- `BiscuitBuilder`
- `BlockBuilder`
- `AuthorizerBuilder`

**Migration:** Replace all `addCodeWithParams($source, $params)` calls with `addCode($source, $params)`.

---

### Builder Constructors with Optional Source

All builder constructors now accept optional source code and parameters.

**Before (v0.2.x):**
```php
$builder = new BiscuitBuilder();
$builder->addCode('user({id})', ['id' => 'alice']);
```

**After (v0.3.0):**
```php
// Can still use the old pattern
$builder = new BiscuitBuilder();
$builder->addCode('user({id})', ['id' => 'alice']);

// Or use constructor directly
$builder = new BiscuitBuilder('user({id})', ['id' => 'alice']);
```

**Affected classes:**
- `BiscuitBuilder::__construct(?string $source = null, ?array $params = null, ?array $scope_params = null)`
- `BlockBuilder::__construct(?string $source = null, ?array $params = null, ?array $scope_params = null)`
- `AuthorizerBuilder::__construct(?string $source = null, ?array $params = null, ?array $scope_params = null)`

**Migration:** No changes required. This is backward compatible.

---

### Primitive Constructors with Optional Params

`Fact`, `Rule`, `Check`, and `Policy` constructors now accept optional parameters.

**Before (v0.2.x):**
```php
$fact = new Fact('user({id})');
$fact->set('id', 'alice');

$rule = new Rule('can_read($u, {res}) <- user($u)');
$rule->set('res', 'file1');

$check = new Check('check if user({name})');
$check->set('name', 'alice');

$policy = new Policy('allow if user({name})');
$policy->set('name', 'alice');
```

**After (v0.3.0):**
```php
// Can still use set() method
$fact = new Fact('user({id})');
$fact->set('id', 'alice');

// Or use constructor params directly
$fact = new Fact('user({id})', ['id' => 'alice']);

$rule = new Rule('can_read($u, {res}) <- user($u)', ['res' => 'file1']);

$check = new Check('check if user({name})', ['name' => 'alice']);

$policy = new Policy('allow if user({name})', ['name' => 'alice']);
```

**Constructor signatures:**
- `Fact::__construct(string $source, ?array $params = null)`
- `Rule::__construct(string $source, ?array $params = null, ?array $scope_params = null)`
- `Check::__construct(string $source, ?array $params = null, ?array $scope_params = null)`
- `Policy::__construct(string $source, ?array $params = null, ?array $scope_params = null)`

**Migration:** No changes required. The `set()` method is still available. This is backward compatible.

---

### Builder Consumption Behavior

Builders now clone internally when calling `build()`, allowing the builder to be reused. This matches the biscuit-python approach. However, `merge()` operations still consume the builders.

**Before (v0.2.x):**
```php
$builder = new BiscuitBuilder('user("alice")');
$biscuit1 = $builder->build($privateKey);
$biscuit2 = $builder->build($privateKey); // Worked - builder was cloned internally
```

**After (v0.3.0):**
```php
$builder = new BiscuitBuilder('user("alice")');
$biscuit1 = $builder->build($privateKey);
$biscuit2 = $builder->build($privateKey); // Still works - builder clones internally on build()

// You can even modify and rebuild
$builder->addCode('resource("file1")');
$biscuit3 = $builder->build($privateKey); // Works with added code
```

**Affected classes:**
- `BiscuitBuilder`
- `BlockBuilder`
- `AuthorizerBuilder`

**Consuming methods (still consume the builder):**
- `merge()` - consumes the "other" builder being merged
- `merge_block()` - consumes the block builder being merged

**Non-consuming methods:**
- `build()` - clones internally, builder remains usable
- `buildUnauthenticated()` - clones internally, builder remains usable

**Migration:** No changes required for `build()` calls. If you were creating new builders for each build, you can now optionally reuse them

---

### New Exception

New exception class added: `Biscuit\Exception\BuilderConsumed`

This exception is thrown when attempting to use a builder that has already been consumed by a previous operation.

---

### Quick Migration Checklist

1. **Search and replace:**
   - `KeyPair::newWithAlgorithm(` → `new KeyPair(`
   - `->addCodeWithParams(` → `->addCode(`

2. **Builder reuse is now supported:**
   - Builders can be reused after `build()` calls (they clone internally)
   - Only `merge()` operations consume builders

3. **Optional improvements (not required):**
   - Use constructor params for builders instead of separate `addCode()` calls
   - Use constructor params for primitives instead of separate `set()` calls

### Removed Methods

| Class | Removed Method | Replacement |
|-------|---------------|-------------|
| `KeyPair` | `newWithAlgorithm(Algorithm $alg)` | `new KeyPair($alg)` |
| `BiscuitBuilder` | `addCodeWithParams()` | `addCode($source, $params)` |
| `BlockBuilder` | `addCodeWithParams()` | `addCode($source, $params)` |
| `AuthorizerBuilder` | `addCodeWithParams()` | `addCode($source, $params)` |
