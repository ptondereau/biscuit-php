<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\DependencyInjection;

use Biscuit\BiscuitBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
    public function itHasDefaultRevocationConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertFalse($config['revocation']['enabled']);
        self::assertNull($config['revocation']['on_unavailable']);
        self::assertSame('on_revoke', $config['revocation']['dispatch_check_events']);
        self::assertNull($config['revocation']['default_expiry']);
    }

    #[Test]
    public function itLeavesTheStoresEmptyByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame([], $config['revocation']['stores']['static']['ids']);
        self::assertNull($config['revocation']['stores']['static']['file']);
        self::assertFalse($config['revocation']['stores']['cache']['enabled']);
        self::assertFalse($config['revocation']['stores']['in_memory']['enabled']);
    }

    #[Test]
    public function itLeavesPushOffByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertFalse($config['revocation']['push']['enabled']);
        self::assertSame('messenger.default_bus', $config['revocation']['push']['bus']);
    }

    #[Test]
    public function itAcceptsPushEnabledAsAShorthand(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => ['revocation' => ['push' => true]],
        ]);

        self::assertTrue($config['revocation']['push']['enabled']);
        self::assertSame('messenger.default_bus', $config['revocation']['push']['bus']);
    }

    #[Test]
    public function itAcceptsACustomPushBus(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => ['revocation' => ['push' => ['enabled' => true, 'bus' => 'app.audit_bus']]],
        ]);

        self::assertSame('app.audit_bus', $config['revocation']['push']['bus']);
    }

    #[Test]
    public function itRejectsAnEmptyPushBus(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([
            'biscuit' => ['revocation' => ['push' => ['enabled' => true, 'bus' => '']]],
        ]);
    }

    #[Test]
    public function itAcceptsRevocationConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'dispatch_check_events' => 'always',
                    'default_expiry' => 86400,
                ],
            ],
        ]);

        self::assertTrue($config['revocation']['enabled']);
        self::assertSame('deny', $config['revocation']['on_unavailable']);
        self::assertSame('always', $config['revocation']['dispatch_check_events']);
        self::assertSame(86400, $config['revocation']['default_expiry']);
    }

    #[Test]
    public function itRejectsAnUnknownUnavailablePolicy(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([
            'biscuit' => [
                'revocation' => ['enabled' => true, 'on_unavailable' => 'maybe'],
            ],
        ]);
    }

    #[Test]
    public function itAcceptsAnEnvPlaceholderForTheStaticIdList(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['static' => ['ids' => '%env(csv:BISCUIT_REVOKED_IDS)%']],
                ],
            ],
        ]);

        self::assertSame('%env(csv:BISCUIT_REVOKED_IDS)%', $config['revocation']['stores']['static']['ids']);
    }

    #[Test]
    public function itAcceptsAListOfStaticIds(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => ['static' => ['ids' => ['abc', 'def']]],
                ],
            ],
        ]);

        self::assertSame(['abc', 'def'], $config['revocation']['stores']['static']['ids']);
    }

    #[Test]
    public function itDefaultsTheUserIdentifierFactToUser(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame('user', $config['security']['user_identifier_fact']);
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
    public function itHasEmptyBlockTemplatesByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame([], $config['block_templates']);
    }

    #[Test]
    public function itAcceptsBlockTemplatesConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'block_templates' => [
                    'read_only' => [
                        'checks' => ['check if operation("read")'],
                    ],
                    'expires' => [
                        'checks' => ['check if now($t), $t <= {exp}'],
                    ],
                    'mixed' => [
                        'facts' => ['scope("read")'],
                        'checks' => ['check if operation("read")'],
                        'rules' => ['allowed_for($r) <- resource($r), scope("read")'],
                    ],
                ],
            ],
        ]);

        self::assertCount(3, $config['block_templates']);

        self::assertSame(['check if operation("read")'], $config['block_templates']['read_only']['checks']);
        self::assertSame([], $config['block_templates']['read_only']['facts']);
        self::assertSame([], $config['block_templates']['read_only']['rules']);

        self::assertSame(['scope("read")'], $config['block_templates']['mixed']['facts']);
        self::assertSame(['check if operation("read")'], $config['block_templates']['mixed']['checks']);
        self::assertSame(['allowed_for($r) <- resource($r), scope("read")'], $config['block_templates']['mixed']['rules']);
    }

    #[Test]
    public function itHasEmptyAuthorizerFactTemplatesByDefault(): void
    {
        $config = $this->processConfiguration([]);

        self::assertSame([], $config['authorizer_fact_templates']);
    }

    #[Test]
    public function itAcceptsAuthorizerFactTemplatesConfiguration(): void
    {
        $config = $this->processConfiguration([
            'biscuit' => [
                'authorizer_fact_templates' => [
                    'credit_authorized' => [
                        'facts' => [
                            'operation("credit_wallet")',
                            'amount({amount})',
                            'wallet_tier({tier})',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertArrayHasKey('credit_authorized', $config['authorizer_fact_templates']);
        self::assertSame(
            ['operation("credit_wallet")', 'amount({amount})', 'wallet_tier({tier})'],
            $config['authorizer_fact_templates']['credit_authorized']['facts'],
        );
        self::assertSame([], $config['authorizer_fact_templates']['credit_authorized']['checks']);
        self::assertSame([], $config['authorizer_fact_templates']['credit_authorized']['rules']);
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
