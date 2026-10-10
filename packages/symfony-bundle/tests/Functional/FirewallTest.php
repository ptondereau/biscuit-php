<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FirewallTest extends WebTestCase
{
    use BiscuitTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itRejectsARequestWithoutAToken(): void
    {
        $client = $this->createSecuredClient();
        $client->request('GET', '/protected');

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itChallengesWithoutAnErrorCodeWhenNoTokenWasSent(): void
    {
        $client = $this->createSecuredClient();
        $client->request('GET', '/protected');

        self::assertResponseHeaderSame('WWW-Authenticate', 'Bearer realm="api"');
    }

    #[Test]
    public function itChallengesWithAnInvalidTokenErrorForAForgedToken(): void
    {
        $attacker = new KeyPair();
        $forged = (new BiscuitBuilder('user("alice")'))->build($attacker->getPrivateKey())->toBase64();

        $client = $this->createSecuredClient();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $forged,
        ]);

        $challenge = $client->getResponse()->headers->get('WWW-Authenticate');
        self::assertIsString($challenge);
        self::assertStringContainsString('error="invalid_token"', $challenge);
    }

    #[Test]
    public function itReportsFailuresUsingTheOauthBearerErrorShape(): void
    {
        $client = $this->createSecuredClient();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer not-a-biscuit',
        ]);

        $body = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertIsArray($body);
        self::assertSame('invalid_token', $body['error'] ?? null);
        self::assertArrayHasKey('error_description', $body);
    }

    #[Test]
    public function itRejectsATokenSignedByAnotherKey(): void
    {
        $attacker = new KeyPair();
        $forged = (new BiscuitBuilder('user("alice")'))->build($attacker->getPrivateKey())->toBase64();

        $client = $this->createSecuredClient();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $forged,
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itRejectsAMalformedToken(): void
    {
        $client = $this->createSecuredClient();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer not-a-biscuit',
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function itAcceptsATokenSignedByTheConfiguredKey(): void
    {
        $client = $this->createSecuredClient();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createTestTokenBase64('user("alice")'),
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"status":"authenticated"}',
            (string) $client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itLeavesRequestsAloneWhenTheFirewallIsNotSecured(): void
    {
        TestKernel::configure([], withFirewall: false);

        $client = self::createClient();
        $client->request('GET', '/protected');

        self::assertResponseIsSuccessful();
    }

    private function createSecuredClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        TestKernel::configure(
            ['keys' => ['public_key' => self::getTestPublicKey()->toHex()]],
            withFirewall: true,
        );

        return self::createClient();
    }
}
