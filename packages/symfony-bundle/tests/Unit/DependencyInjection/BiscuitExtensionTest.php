<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\DependencyInjection;

use Biscuit\BiscuitBundle\DependencyInjection\BiscuitExtension;
use Biscuit\BiscuitBundle\Revocation\RevocationCheckerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

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
    public function itSetsDefaultRevocationParameters(): void
    {
        $this->extension->load([], $this->container);

        self::assertFalse($this->container->getParameter('biscuit.revocation.enabled'));
        self::assertNull($this->container->getParameter('biscuit.revocation.on_unavailable'));
        self::assertSame('on_revoke', $this->container->getParameter('biscuit.revocation.dispatch_check_events'));
        self::assertNull($this->container->getParameter('biscuit.revocation.default_expiry'));
    }

    #[Test]
    public function itSetsCustomRevocationParameters(): void
    {
        $this->loadWithRevocation([
            'on_unavailable' => 'allow',
            'dispatch_check_events' => 'always',
            'default_expiry' => 86400,
        ]);

        self::assertTrue($this->container->getParameter('biscuit.revocation.enabled'));
        self::assertSame('allow', $this->container->getParameter('biscuit.revocation.on_unavailable'));
        self::assertSame('always', $this->container->getParameter('biscuit.revocation.dispatch_check_events'));
        self::assertSame(86400, $this->container->getParameter('biscuit.revocation.default_expiry'));
    }

    #[Test]
    public function itAlwaysResolvesTheAuthenticatorCheckerByNameAndNeverByPosition(): void
    {
        $this->extension->load([], $this->container);

        $checker = $this->container->getDefinition('biscuit.authenticator')->getArgument('$revocationChecker');

        self::assertInstanceOf(Reference::class, $checker);
        self::assertSame(RevocationCheckerInterface::class, (string) $checker);
        self::assertSame(
            ContainerInterface::NULL_ON_INVALID_REFERENCE,
            $checker->getInvalidBehavior(),
            'A disabled revocation setup must inject null, not drop the argument.',
        );
    }

    #[Test]
    public function itDefinesNoRevocationServicesWhenDisabled(): void
    {
        $this->extension->load([], $this->container);

        self::assertFalse($this->container->hasDefinition('biscuit.revocation.checker'));
        self::assertFalse($this->container->hasDefinition('biscuit.revocation.writer'));
        self::assertFalse($this->container->hasAlias(RevocationCheckerInterface::class));
    }

    #[Test]
    public function itDefinesTheCheckerWhenEnabled(): void
    {
        $this->loadWithRevocation();

        self::assertTrue($this->container->hasDefinition('biscuit.revocation.checker'));
        self::assertTrue($this->container->hasAlias(RevocationCheckerInterface::class));
    }

    #[Test]
    public function itThrowsWhenRevocationIsEnabledWithoutAnUnavailablePolicy(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/on_unavailable must be set explicitly/');

        $this->extension->load([
            'biscuit' => [
                'revocation' => ['enabled' => true],
            ],
        ], $this->container);
    }

    #[Test]
    public function itRegistersTheStaticStoreOnlyWhenItHasSomethingToCheck(): void
    {
        $this->loadWithRevocation();

        self::assertFalse($this->container->hasDefinition('biscuit.revocation.store.static'));
    }

    #[Test]
    public function itRegistersTheStaticStoreWhenIdsAreConfigured(): void
    {
        $this->loadWithRevocation(['stores' => ['static' => ['ids' => ['abc']]]]);

        self::assertTrue($this->container->hasDefinition('biscuit.revocation.store.static'));
        self::assertSame(['abc'], $this->container->getParameter('biscuit.revocation.stores.static.ids'));
    }

    #[Test]
    public function itRegistersTheStaticStoreWhenOnlyAFileIsConfigured(): void
    {
        $this->loadWithRevocation(['stores' => ['static' => ['file' => '/tmp/revoked.txt']]]);

        self::assertTrue($this->container->hasDefinition('biscuit.revocation.store.static'));
    }

    #[Test]
    public function itDoesNotRegisterTheCacheStoreByDefault(): void
    {
        $this->loadWithRevocation();

        self::assertFalse($this->container->hasDefinition('biscuit.revocation.store.cache'));
    }

    #[Test]
    public function itCreatesADedicatedCachePoolRatherThanUsingCacheApp(): void
    {
        $this->loadWithRevocation(['stores' => ['cache' => ['enabled' => true]]]);

        self::assertTrue($this->container->hasDefinition('cache.biscuit.revocation'));
        self::assertTrue($this->container->getDefinition('cache.biscuit.revocation')->hasTag('cache.pool'));

        $pool = $this->container->getDefinition('biscuit.revocation.store.cache')->getArgument('$cachePool');
        self::assertInstanceOf(Reference::class, $pool);
        self::assertSame('cache.biscuit.revocation', (string) $pool);
    }

    #[Test]
    public function itUsesAnExistingPoolWhenOneIsConfigured(): void
    {
        $this->loadWithRevocation(['stores' => ['cache' => ['enabled' => true, 'pool' => 'cache.redis']]]);

        self::assertFalse($this->container->hasDefinition('cache.biscuit.revocation'));

        $pool = $this->container->getDefinition('biscuit.revocation.store.cache')->getArgument('$cachePool');
        self::assertInstanceOf(Reference::class, $pool);
        self::assertSame('cache.redis', (string) $pool);
    }

    #[Test]
    public function itRegistersTheInMemoryStoreOnlyWhenEnabled(): void
    {
        $this->loadWithRevocation();
        self::assertFalse($this->container->hasDefinition('biscuit.revocation.store.in_memory'));

        $this->setUp();
        $this->loadWithRevocation(['stores' => ['in_memory' => ['enabled' => true]]]);
        self::assertTrue($this->container->hasDefinition('biscuit.revocation.store.in_memory'));
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
    public function itSetsDefaultAuthorizerFactTemplatesParameter(): void
    {
        $this->extension->load([], $this->container);

        self::assertSame([], $this->container->getParameter('biscuit.authorizer_fact_templates'));
    }

    #[Test]
    public function itSetsCustomAuthorizerFactTemplatesParameter(): void
    {
        $templates = [
            'credit_authorized' => [
                'facts' => ['operation("credit_wallet")', 'amount({amount})'],
                'checks' => [],
                'rules' => [],
            ],
        ];

        $this->extension->load([
            'biscuit' => [
                'authorizer_fact_templates' => $templates,
            ],
        ], $this->container);

        self::assertSame($templates, $this->container->getParameter('biscuit.authorizer_fact_templates'));
    }

    #[Test]
    public function itWiresApplierAndAuthorizerFactTemplatesIntoVoter(): void
    {
        $this->extension->load([], $this->container);

        $voter = $this->container->getDefinition('biscuit.voter');

        self::assertCount(4, $voter->getArguments());
        self::assertSame('%biscuit.authorizer_fact_templates%', $voter->getArgument(3));
    }

    #[Test]
    public function itRegistersClassAliases(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Key\KeyManager'));
        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Token\BiscuitTokenManager'));
        self::assertTrue($this->container->hasAlias('Biscuit\BiscuitBundle\Token\BiscuitTokenFactory'));
    }

    /**
     * @param array<string, mixed> $revocation
     */
    private function loadWithRevocation(array $revocation = []): void
    {
        $this->extension->load([
            'biscuit' => [
                'revocation' => $revocation + ['enabled' => true, 'on_unavailable' => 'deny'],
            ],
        ], $this->container);
    }
}
