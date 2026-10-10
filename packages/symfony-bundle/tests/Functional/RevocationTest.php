<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\Auth\BlockBuilder;
use Biscuit\BiscuitBundle\DataCollector\BiscuitDataCollector;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Profiler\Profile;

final class RevocationTest extends WebTestCase
{
    use BiscuitTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itRejectsATokenWhoseIdIsInTheStaticList(): void
    {
        $token = $this->createTestToken('user("alice")');
        $revokedId = $token->revocationIds()[0];

        $client = $this->createSecuredClient(['static' => ['ids' => [$revokedId]]]);
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itAcceptsATokenThatIsNotInTheList(): void
    {
        $client = $this->createSecuredClient(['static' => ['ids' => ['0000000000000000']]]);
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createTestTokenBase64('user("alice")'),
        ]);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function itMatchesTheStaticListCaseInsensitively(): void
    {
        $token = $this->createTestToken('user("alice")');
        $revokedId = strtoupper($token->revocationIds()[0]);

        $client = $this->createSecuredClient(['static' => ['ids' => [$revokedId]]]);
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itRejectsAnAttenuatedTokenWhenItsParentIdIsRevoked(): void
    {
        $parent = $this->createTestToken('user("alice")');
        $parentId = $parent->revocationIds()[0];

        $block = new BlockBuilder('check if resource("report")');
        $child = $parent->append($block);

        $client = $this->createSecuredClient(['static' => ['ids' => [$parentId]]]);
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $child->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itLeavesTheParentUsableWhenOnlyTheAttenuatedIdIsRevoked(): void
    {
        $parent = $this->createTestToken('user("alice")');

        $block = new BlockBuilder('check if resource("report")');
        $child = $parent->append($block);
        $childIds = $child->revocationIds();
        $deepestId = $childIds[\count($childIds) - 1];

        $client = $this->createSecuredClient(['static' => ['ids' => [$deepestId]]]);

        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $child->toBase64(),
        ]);
        self::assertResponseStatusCodeSame(401, 'The attenuated token itself must be rejected.');

        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $parent->toBase64(),
        ]);
        self::assertResponseIsSuccessful('Its parent must keep working.');
    }

    #[Test]
    public function itRejectsATokenRevokedThroughTheWriterAtRuntime(): void
    {
        $token = $this->createTestToken('user("alice")');
        $revokedId = $token->revocationIds()[0];

        $client = $this->createSecuredClient(['in_memory' => ['enabled' => true]]);

        $writer = self::getContainer()->get(RevocationWriterInterface::class);
        self::assertInstanceOf(RevocationWriterInterface::class, $writer);
        $writer->revoke(new RevocationEntry($revokedId, subject: 'alice', reason: 'logout'));

        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itPopulatesTheProfilerPanelForARevokedRequest(): void
    {
        $token = $this->createTestToken('user("alice")');
        $revokedId = $token->revocationIds()[0];

        $client = $this->createSecuredClient(['static' => ['ids' => [$revokedId]]]);
        $client->enableProfiler();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token->toBase64(),
        ]);

        $profile = $client->getProfile();
        self::assertInstanceOf(Profile::class, $profile);

        $collector = $profile->getCollector('biscuit');
        self::assertInstanceOf(BiscuitDataCollector::class, $collector);

        self::assertTrue($collector->hasToken(), 'The token must still be collected on a rejected request.');
        self::assertTrue($collector->isRevoked());
        self::assertSame('static', $collector->getRevocation()['store'] ?? null);
    }

    /**
     * @param array<string, mixed> $stores
     */
    private function createSecuredClient(array $stores): KernelBrowser
    {
        TestKernel::configure(
            [
                'keys' => ['public_key' => self::getTestPublicKey()->toHex()],
                'revocation' => [
                    'enabled' => true,
                    'on_unavailable' => 'deny',
                    'stores' => $stores,
                ],
            ],
            withFirewall: true,
        );

        return self::createClient();
    }
}
