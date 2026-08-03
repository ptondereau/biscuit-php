<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Functional;

use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use Biscuit\BiscuitBundle\Tests\TestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Profiler\Profile;

final class ProfilerPanelTest extends WebTestCase
{
    use BiscuitTestTrait;
    use ResetsTestKernel;

    #[Test]
    public function itRendersTheRevocationTabForAValidToken(): void
    {
        $client = $this->createSecuredClient(['static' => ['ids' => ['0000000000000000']]]);

        $html = $this->renderPanel($client, $this->createTestTokenBase64('user("alice")'));

        self::assertStringContainsString('Revocation', $html);
        self::assertStringContainsString('Stores consulted', $html);
        self::assertStringContainsString('No match', $html);
    }

    #[Test]
    public function itRendersTheRevocationTabForARevokedToken(): void
    {
        $token = $this->createTestToken('user("alice")');
        $revokedId = $token->revocationIds()[0];

        $client = $this->createSecuredClient(['static' => ['ids' => [$revokedId]]]);

        $html = $this->renderPanel($client, $token->toBase64());

        self::assertStringContainsString('Revoked', $html);
        self::assertStringContainsString('Matched', $html);
        self::assertStringContainsString($revokedId, $html);
    }

    #[Test]
    public function itRendersTheDisabledStateWhenRevocationIsOff(): void
    {
        TestKernel::configure(
            ['keys' => ['public_key' => self::getTestPublicKey()->toHex()]],
            withFirewall: true,
        );
        $client = self::createClient();

        $html = $this->renderPanel($client, $this->createTestTokenBase64('user("alice")'));

        self::assertStringContainsString('Revocation checking is disabled', $html);
    }

    private function renderPanel(KernelBrowser $client, string $token): string
    {
        $client->enableProfiler();
        $client->request('GET', '/protected', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $profile = $client->getProfile();
        self::assertInstanceOf(Profile::class, $profile, 'The profiler must have collected this request.');

        $client->catchExceptions(false);
        $client->request('GET', '/_profiler/' . $profile->getToken() . '?panel=biscuit');

        self::assertResponseIsSuccessful('The Biscuit profiler panel must render without a Twig error.');

        return (string) $client->getResponse()->getContent();
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
