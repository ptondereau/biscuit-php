# Upgrading Guide

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
