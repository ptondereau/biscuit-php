<?php

declare(strict_types=1);

namespace Biscuit\Laravel;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\Policy;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\Exception\AuthorizationException;
use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\BuilderStateException;
use Biscuit\Exception\FormatException;
use Biscuit\Exception\PolicyException;
use Biscuit\Exception\PrivateKeyException;
use Biscuit\Exception\PublicKeyException;
use Biscuit\Exception\ScopeException;
use Biscuit\Exception\TermException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Override;
use RuntimeException;

final class BiscuitServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/biscuit.php', 'biscuit');

        $this->app->singleton(
            PublicKey::class,
            /**
             * @throws FileNotFoundException
             * @throws PublicKeyException
             * @throws RuntimeException
             */
            static function (): PublicKey {
                $hex = self::option('biscuit.public_key');

                return null !== $hex ? new PublicKey($hex) : PublicKey::fromPem(self::pem('public_key'));
            },
        );
        $this->app->singleton(
            PrivateKey::class,
            /**
             * @throws FileNotFoundException
             * @throws PrivateKeyException
             * @throws RuntimeException
             */
            static function (): PrivateKey {
                $hex = self::option('biscuit.private_key');

                return null !== $hex ? new PrivateKey($hex) : PrivateKey::fromPem(self::pem('private_key'));
            },
        );
        $this->app->singleton(
            Policies::class,
            /**
             * @throws InvalidArgumentException
             */
            static fn(): Policies => new Policies(Config::array('biscuit.policies')),
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/biscuit.php' => $this->app->configPath('biscuit.php'),
        ], 'biscuit-config');

        Gate::before(
            /**
             * @param array<array-key, mixed> $arguments
             *
             * @throws AuthorizerBuildException
             * @throws BuilderStateException
             * @throws InvalidArgumentException
             * @throws PolicyException
             * @throws ScopeException
             * @throws TermException
             */
            function (mixed $_user, string $ability, array $arguments): ?bool {
                $policy = $this->app->make(Policies::class)->resolve($ability, $arguments);

                if (null === $policy) {
                    return null;
                }

                $biscuit = $this->biscuit($this->app->make(Request::class));

                return null !== $biscuit && self::authorize($biscuit, $policy);
            },
        );
    }

    private function biscuit(Request $request): ?Biscuit
    {
        $cookie = self::option('biscuit.cookie');
        $token = $request->bearerToken() ?? (null !== $cookie ? $request->cookie($cookie) : null);

        if (!\is_string($token) || '' === $token) {
            return null;
        }

        try {
            return Biscuit::fromBase64($token, $this->app->make(PublicKey::class));
        } catch (FormatException) {
            return null;
        }
    }

    /**
     * @throws AuthorizerBuildException
     * @throws BuilderStateException
     * @throws PolicyException
     * @throws TermException
     */
    private static function authorize(Biscuit $biscuit, Policy $policy): bool
    {
        $builder = new AuthorizerBuilder();
        $builder->setTime();
        $builder->addPolicy($policy);

        try {
            $builder->build($biscuit)->authorize();
        } catch (AuthorizationException) {
            return false;
        }

        return true;
    }

    /**
     * @return non-empty-string|null
     */
    private static function option(string $key): ?string
    {
        return self::nonEmpty(Config::get($key));
    }

    /**
     * @return non-empty-string|null
     */
    private static function nonEmpty(mixed $value): ?string
    {
        return \is_string($value) && '' !== $value ? $value : null;
    }

    /**
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    private static function pem(string $name): string
    {
        $file = self::option("biscuit.{$name}_file");

        if (null === $file) {
            throw new RuntimeException(sprintf(
                'No Biscuit %s configured. Set "biscuit.%s" or "biscuit.%s_file".',
                str_replace(search: '_', replace: ' ', subject: $name),
                $name,
                $name,
            ));
        }

        return File::get($file);
    }
}
