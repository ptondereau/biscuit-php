<?php

declare(strict_types=1);

namespace Biscuit\Laravel\Tests;

use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\Laravel\BiscuitServiceProvider;
use Biscuit\Laravel\BiscuitUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Testbench;

/**
 * @internal
 */
abstract class TestCase extends Testbench
{
    private KeyPair $keys;

    protected function getPackageProviders($app): array
    {
        return [BiscuitServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $this->keys = new KeyPair();

        Config::set('auth.guards.biscuit', ['driver' => 'biscuit']);
        Config::set('biscuit.public_key', $this->keys->getPublicKey()->toHex());
        Config::set('biscuit.private_key', $this->keys->getPrivateKey()->toHex());
        Config::set('biscuit.cookie', 'biscuit');
        Config::set('biscuit.policies', [
            'posts.read' => 'allow if right("posts", "read")',
            'posts.edit' => 'allow if right({resource}, "edit")',
        ]);
    }

    /**
     * @param Router $router
     */
    protected function defineRoutes($router): void
    {
        $router
            ->middleware('auth:biscuit')
            ->group(static function (Router $router): void {
                $router->get('/me', static fn(): string => self::identifier(Auth::guard('biscuit')->user()));
                $router->get('/posts', static fn(): string => 'posts')->middleware('can:posts.read');
                $router->get('/posts/{post}', static fn(string $post): string => $post)->middleware(
                    'can:posts.edit,post',
                );
            });
    }

    private static function identifier(?Authenticatable $user): string
    {
        return $user instanceof BiscuitUser ? $user->identifier : '';
    }

    public function keys(): KeyPair
    {
        return $this->keys;
    }

    public function mint(string $code): Biscuit
    {
        return (new BiscuitBuilder($code))->build($this->keys->getPrivateKey());
    }

    /**
     * @return array{Authorization: string}
     */
    public function bearer(string $code): array
    {
        return ['Authorization' => 'Bearer ' . $this->mint($code)->toBase64()];
    }
}
