<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\DependencyInjection;

use Biscuit\BiscuitBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    private Processor $processor;

    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    #[Test]
    public function itHasDefaultConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertArrayHasKey('keys', $config);
        self::assertArrayHasKey('security', $config);
        self::assertArrayHasKey('cache', $config);
        self::assertArrayHasKey('revocation', $config);
        self::assertArrayHasKey('policies', $config);
        self::assertArrayHasKey('token_templates', $config);
    }

    #[Test]
    public function itHasDefaultKeysConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertNull($config['keys']['public_key']);
        self::assertNull($config['keys']['private_key']);
        self::assertNull($config['keys']['public_key_file']);
        self::assertNull($config['keys']['private_key_file']);
        self::assertSame('ed25519', $config['keys']['algorithm']);
    }

    #[Test]
    public function itAcceptsHexBasedKeys(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'keys' => [
                    'public_key' => 'abc123def456',
                    'private_key' => '789xyz000111',
                ],
            ],
        ]);

        self::assertSame('abc123def456', $config['keys']['public_key']);
        self::assertSame('789xyz000111', $config['keys']['private_key']);
    }

    #[Test]
    public function itAcceptsFileBasedKeys(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'keys' => [
                    'public_key_file' => '/path/to/public.key',
                    'private_key_file' => '/path/to/private.key',
                ],
            ],
        ]);

        self::assertSame('/path/to/public.key', $config['keys']['public_key_file']);
        self::assertSame('/path/to/private.key', $config['keys']['private_key_file']);
    }

    #[Test]
    public function itAcceptsAlgorithmConfiguration(): void
    {
        $configEd25519 = $this->processConfiguration([
            'biscuit' => [
                'keys' => [
                    'algorithm' => 'ed25519',
                ],
            ],
        ]);

        self::assertSame('ed25519', $configEd25519['keys']['algorithm']);

        $configSecp = $this->processConfiguration([
            'biscuit' => [
                'keys' => [
                    'algorithm' => 'secp256r1',
                ],
            ],
        ]);

        self::assertSame('secp256r1', $configSecp['keys']['algorithm']);
    }

    #[Test]
    public function itHasDefaultSecurityConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertArrayHasKey('token_extractor', $config['security']);
        self::assertTrue($config['security']['token_extractor']['header']);
        self::assertFalse($config['security']['token_extractor']['cookie']);
    }

    #[Test]
    public function itAcceptsSecurityTokenExtractorConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'security' => [
                    'token_extractor' => [
                        'header' => false,
                        'cookie' => 'biscuit_token',
                    ],
                ],
            ],
        ]);

        self::assertFalse($config['security']['token_extractor']['header']);
        self::assertSame('biscuit_token', $config['security']['token_extractor']['cookie']);
    }

    #[Test]
    public function itHasDefaultCacheConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertFalse($config['cache']['enabled']);
        self::assertSame('cache.app', $config['cache']['pool']);
        self::assertSame(3600, $config['cache']['ttl']);
    }

    #[Test]
    public function itAcceptsCacheConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'cache' => [
                    'enabled' => true,
                    'pool' => 'cache.biscuit',
                    'ttl' => 7200,
                ],
            ],
        ]);

        self::assertTrue($config['cache']['enabled']);
        self::assertSame('cache.biscuit', $config['cache']['pool']);
        self::assertSame(7200, $config['cache']['ttl']);
    }

    #[Test]
    public function itHasDefaultRevocationConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertFalse($config['revocation']['enabled']);
        self::assertNull($config['revocation']['service']);
    }

    #[Test]
    public function itAcceptsRevocationConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'revocation' => [
                    'enabled' => true,
                    'service' => 'App\\Security\\BiscuitRevocationChecker',
                ],
            ],
        ]);

        self::assertTrue($config['revocation']['enabled']);
        self::assertSame('App\\Security\\BiscuitRevocationChecker', $config['revocation']['service']);
    }

    #[Test]
    public function itHasEmptyPoliciesByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame([], $config['policies']);
    }

    #[Test]
    public function itAcceptsPoliciesConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'policies' => [
                    'admin' => 'allow if user($id), role($id, "admin")',
                    'read_only' => 'allow if operation("read")',
                    'deny_all' => 'deny if true',
                ],
            ],
        ]);

        self::assertCount(3, $config['policies']);
        self::assertSame('allow if user($id), role($id, "admin")', $config['policies']['admin']);
        self::assertSame('allow if operation("read")', $config['policies']['read_only']);
        self::assertSame('deny if true', $config['policies']['deny_all']);
    }

    #[Test]
    public function itHasEmptyTokenTemplatesByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame([], $config['token_templates']);
    }

    #[Test]
    public function itAcceptsTokenTemplatesConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'token_templates' => [
                    'user_token' => [
                        'facts' => [
                            'user({user_id})',
                            'email({email})',
                        ],
                        'checks' => [
                            'check if time($time), $time < {expiry}',
                        ],
                        'rules' => [
                            'is_admin($id) <- user($id), role($id, "admin")',
                        ],
                    ],
                    'api_token' => [
                        'facts' => [
                            'api_client({client_id})',
                        ],
                        'checks' => [],
                        'rules' => [],
                    ],
                ],
            ],
        ]);

        self::assertCount(2, $config['token_templates']);

        self::assertArrayHasKey('user_token', $config['token_templates']);
        self::assertSame(['user({user_id})', 'email({email})'], $config['token_templates']['user_token']['facts']);
        self::assertSame(['check if time($time), $time < {expiry}'], $config['token_templates']['user_token']['checks']);
        self::assertSame(['is_admin($id) <- user($id), role($id, "admin")'], $config['token_templates']['user_token']['rules']);

        self::assertArrayHasKey('api_token', $config['token_templates']);
        self::assertSame(['api_client({client_id})'], $config['token_templates']['api_token']['facts']);
        self::assertSame([], $config['token_templates']['api_token']['checks']);
        self::assertSame([], $config['token_templates']['api_token']['rules']);
    }

    #[Test]
    public function itAcceptsTokenTemplatesWithEmptyArrays(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'token_templates' => [
                    'minimal' => [
                        'facts' => ['user({id})'],
                    ],
                ],
            ],
        ]);

        self::assertArrayHasKey('minimal', $config['token_templates']);
        self::assertSame(['user({id})'], $config['token_templates']['minimal']['facts']);
        self::assertSame([], $config['token_templates']['minimal']['checks']);
        self::assertSame([], $config['token_templates']['minimal']['rules']);
    }

    /**
     * @param array<string, mixed> $configs
     *
     * @return array<string, mixed>
     */
    private function processConfiguration(array $configs): array
    {
        return $this->processor->processConfiguration(
            $this->configuration,
            $configs,
        );
    }
}
