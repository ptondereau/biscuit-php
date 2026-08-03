# Upgrading to 0.4

The revocation subsystem was rebuilt. If you never enabled `biscuit.revocation`, only the
authentication failure response changes.

## Revocation

The extension point moved and split in two. You now implement the read side and let
autoconfiguration wire it up:

| 0.3 | 0.4 |
| --- | --- |
| implement `Cache\Revocation\RevocationCheckerInterface` | implement `Revocation\RevocationStoreInterface` |
| `Cache\Revocation\CacheRevocationChecker` | `Revocation\Store\CacheRevocationStore` |
| `$checker->isRevoked($biscuit): bool` | `$checker->check($biscuit): RevocationResult` |
| `$checker->revoke($id, $ttl)` | `$writer->revoke(new RevocationEntry($id, expiresAt: ...))` |
| `revocation.service: App\MyChecker` | removed; tag or autoconfigure a store instead |

There is no compatibility shim. Widening `isRevoked(Biscuit)` to accept `UnverifiedBiscuit` is a
fatal contravariance error for an existing implementation, so a `class_alias` would break harder
than a removal.

Before:

```yaml
biscuit:
    revocation:
        enabled: true
        service: App\Security\MyRevocationChecker
```

```php
final class MyRevocationChecker implements RevocationCheckerInterface
{
    public function isRevoked(Biscuit $biscuit): bool
    {
        return $this->repository->anyRevoked($biscuit->revocationIds());
    }
}
```

After:

```yaml
biscuit:
    revocation:
        enabled: true
        on_unavailable: deny
```

```php
final class MyRevocationStore implements RevocationStoreInterface
{
    public function findRevoked(array $revocationIds): ?string
    {
        return $this->repository->firstRevoked($revocationIds);
    }
}
```

`on_unavailable` is required whenever revocation is enabled, and the container refuses to build
without it. Pick `deny` to reject requests when a store cannot answer, or `allow` to accept them
and log an error. There is no default because the right answer depends on whether an unreachable
list should take your API down.

Enabling revocation with no store now fails the container build instead of passing every token.

## Token caching removed

`biscuit.cache.*` and `Cache\TokenCache` are gone. The configuration never had any effect:
`TokenCache` was never registered as a service. It also could not have worked, because it stored
a `Biscuit` instance in the pool and the extension object has a private constructor and no
serialization support, so it only survived `ArrayAdapter` inside a single request.

Delete the `cache` key from your configuration. Signature verification is local and runs in
microseconds; caching it was never the bottleneck.

## Authentication failure responses

Failures still return 401, and the body shape changed to match RFC 6750.

Before:

```json
{ "error": "Token has been revoked.", "message": "" }
```

After:

```json
{ "error": "invalid_token", "error_description": "Token has been revoked." }
```

Responses now carry a `WWW-Authenticate` header. Requests that sent no credentials get
`Bearer realm="api"` with no error code, as RFC 6750 section 3.1 requires; everything else gets
`error="invalid_token"`.

Set `biscuit.security.www_authenticate: false` to keep the header off, and
`biscuit.security.realm` to change the realm. Clients that read the old `error` field as a human
message should read `error_description` instead.

`BiscuitAuthenticator` also implements `AuthenticationEntryPointInterface`. Add
`entry_point: biscuit.authenticator` to your firewall so anonymous requests get the same response
shape as rejected tokens.

## Exceptions

Two exceptions replace `CustomUserMessageAuthenticationException`:

- `Security\Exception\MissingTokenException` when no token was extracted
- `Security\Exception\InvalidTokenException` when parsing or verification failed

Both extend `AuthenticationException`, so `catch (AuthenticationException)` keeps working.

## Configuration added

- `biscuit.security.user_identifier_fact` (default `user`) replaces the hardcoded fact name.
- `biscuit.security.www_authenticate` and `biscuit.security.realm` control the challenge.
