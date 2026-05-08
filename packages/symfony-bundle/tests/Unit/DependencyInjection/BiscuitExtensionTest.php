<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\DependencyInjection;

use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(BiscuitExtension::class)]
final class BiscuitExtensionTest extends TestCase
{
    private ContainerBuilder $container;

    private BiscuitExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new BiscuitExtension();
    }

    #[Test]
    public function itRegistersKeyManagerService(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('biscuit.key_manager'));
    }

    #[Test]
    public function itRegistersTokenManagerService(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('biscuit.token_manager'));
    }

    #[Test]
    public function itRegistersTokenFactoryService(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('biscuit.token_factory'));
    }

    #[Test]
    public function itRegistersBlockFactoryService(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('biscuit.block_factory'));
    }

    #[Test]
    public function itSetsDefaultKeysParameters(): void
    {
        $this->extension->load([], $this->container);

        self::assertNull($this->container->getParameter('biscuit.keys.public_key'));
        self::assertNull($this->container->getParameter('biscuit.keys.private_key'));
        self::assertNull($this->container->getParameter('biscuit.keys.public_key_file'));
        self::assertNull($this->container->getParameter('biscuit.keys.private_key_file'));
        self::assertSame('ed25519', $this->container->getParameter('biscuit.keys.algorithm'));
    }

    #[Test]
    public function itSetsCustomKeysParameters(): void
    {
        $this->extension->load([
            'biscuit' => [
                'keys' => [
                    'public_key' => 'abc123',
                    'private_key' => 'def456',
                    'algorithm' => 'secp256r1',
                ],
            ],
        ], $this->container);

        self::assertSame('abc123', $this->container->getParameter('biscuit.keys.public_key'));
        self::assertSame('def456', $this->container->getParameter('biscuit.keys.private_key'));
        self::assertSame('secp256r1', $this->container->getParameter('biscuit.keys.algorithm'));
    }

    #[Test]
    public function itSetsFileBasedKeysParameters(): void
    {
        $this->extension->load([
            'biscuit' => [
                'keys' => [
                    'public_key_file' => '/path/to/public.key',
                    'private_key_file' => '/path/to/private.key',
                ],
            ],
        ], $this->container);

        self::assertSame('/path/to/public.key', $this->container->getParameter('biscuit.keys.public_key_file'));
        self::assertSame('/path/to/private.key', $this->container->getParameter('biscuit.keys.private_key_file'));
    }

    #[Test]
    public function itSetsDefaultSecurityParameters(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->getParameter('biscuit.security.token_extractor.header'));
        self::assertFalse($this->container->getParameter('biscuit.security.token_extractor.cookie'));
    }

    #[Test]
    public function itSetsCustomSecurityParameters(): void
    {
        $this->extension->load([
            'biscuit' => [
                'security' => [
                    'token_extractor' => [
                        'header' => false,
                        'cookie' => 'biscuit_token',
                    ],
                ],
            ],
        ], $this->container);

        self::assertFalse($this->container->getParameter('biscuit.security.token_extractor.header'));
        self::assertSame('biscuit_token', $this->container->getParameter('biscuit.security.token_extractor.cookie'));
    }

    #[Test]
    public function itSetsDefaultCacheParameters(): void
    {
        $this->extension->load([], $this->container);

        self::assertFalse($this->container->getParameter('biscuit.cache.enabled'));
        self::assertSame('cache.app', $this->container->getParameter('biscuit.cache.pool'));
        self::assertSame(3600, $this->container->getParameter('biscuit.cache.ttl'));
    }

    #[Test]
    public function itSetsCustomCacheParameters(): void
    {
        $this->extension->load([
            'biscuit' => [
                'cache' => [
                    'enabled' => true,
                    'pool' => 'cache.biscuit',
                    'ttl' => 7200,
                ],
            ],
        ], $this->container);

        self::assertTrue($this->container->getParameter('biscuit.cache.enabled'));
        self::assertSame('cache.biscuit', $this->container->getParameter('biscuit.cache.pool'));
        self::assertSame(7200, $this->container->getParameter('biscuit.cache.ttl'));
    }

    #[Test]
    public function itSetsDefaultRevocationParameters(): void
    {
        $this->extension->load([], $this->container);

        self::assertFalse($this->container->getParameter('biscuit.revocation.enabled'));
        self::assertNull($this->container->getParameter('biscuit.revocation.service'));
    }

    #[Test]
    public function itSetsCustomRevocationParameters(): void
    {
        $this->extension->load([
            'biscuit' => [
                'revocation' => [
                    'enabled' => true,
                    'service' => 'App\\Security\\RevocationChecker',
                ],
            ],
        ], $this->container);

        self::assertTrue($this->container->getParameter('biscuit.revocation.enabled'));
        self::assertSame('App\\Security\\RevocationChecker', $this->container->getParameter('biscuit.revocation.service'));
    }

    #[Test]
    public function itSetsDefaultPoliciesParameter(): void
    {
        $this->extension->load([], $this->container);

        self::assertSame([], $this->container->getParameter('biscuit.policies'));
    }

    #[Test]
    public function itSetsCustomPoliciesParameter(): void
    {
        $policies = [
            'admin' => 'allow if user($id), role($id, "admin")',
            'read_only' => 'allow if operation("read")',
        ];

        $this->extension->load([
            'biscuit' => [
                'policies' => $policies,
            ],
        ], $this->container);

        self::assertSame($policies, $this->container->getParameter('biscuit.policies'));
    }

    #[Test]
    public function itSetsDefaultTokenTemplatesParameter(): void
    {
        $this->extension->load([], $this->container);

        self::assertSame([], $this->container->getParameter('biscuit.token_templates'));
    }

    #[Test]
    public function itSetsCustomTokenTemplatesParameter(): void
    {
        $templates = [
            'user_token' => [
                'facts' => ['user({user_id})'],
                'checks' => ['check if time($time), $time < {expiry}'],
                'rules' => [],
            ],
        ];

        $this->extension->load([
            'biscuit' => [
                'token_templates' => $templates,
            ],
        ], $this->container);

        self::assertSame($templates, $this->container->getParameter('biscuit.token_templates'));
    }

    #[Test]
    public function itSetsDefaultBlockTemplatesParameter(): void
    {
        $this->extension->load([], $this->container);

        self::assertSame([], $this->container->getParameter('biscuit.block_templates'));
    }

    #[Test]
    public function itSetsCustomBlockTemplatesParameter(): void
    {
        $templates = [
            'read_only' => [
                'facts' => [],
                'checks' => ['check if operation("read")'],
                'rules' => [],
            ],
        ];

        $this->extension->load([
            'biscuit' => [
                'block_templates' => $templates,
            ],
        ], $this->container);

        self::assertSame($templates, $this->container->getParameter('biscuit.block_templates'));
    }

    #[Test]
    public function itRegistersClassAliases(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Key\KeyManager'));
        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Token\BiscuitTokenManager'));
        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Token\BiscuitTokenFactory'));
    }
}
