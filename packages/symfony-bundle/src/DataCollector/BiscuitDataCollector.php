<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\DataCollector;

use Biscuit\Auth\Biscuit;
use Biscuit\BiscuitBundle\Event\BiscuitTokenAttenuatedEvent;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class BiscuitDataCollector extends AbstractDataCollector implements EventSubscriberInterface
{
    private ?Biscuit $currentBiscuit = null;

    private ?string $serializedToken = null;

    private ?string $publicKey = null;

    /** @var array<int, array{policy: string, result: bool, params: array<string, mixed>}> */
    private array $policyChecks = [];

    /** @var array<string, string> */
    private array $policies = [];

    /** @var list<array{parentRevocationIds: list<string>, childRevocationIds: list<string>, blockSource: string, sizeDelta: int}> */
    private array $attenuations = [];

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data = [
            'has_token' => null !== $this->currentBiscuit,
            'block_count' => $this->currentBiscuit?->blockCount() ?? 0,
            'blocks' => $this->collectBlocks(),
            'revocation_ids' => $this->currentBiscuit?->revocationIds() ?? [],
            'serialized_token' => $this->serializedToken,
            'public_key' => $this->publicKey,
            'policies' => $this->policies,
            'policy_checks' => $this->policyChecks,
            'policy_check_count' => \count($this->policyChecks),
            'passed_checks' => $this->countPassedChecks(),
            'failed_checks' => $this->countFailedChecks(),
            'attenuations' => $this->attenuations,
            'attenuation_count' => \count($this->attenuations),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BiscuitTokenAttenuatedEvent::class => 'onAttenuated',
        ];
    }

    public function onAttenuated(BiscuitTokenAttenuatedEvent $event): void
    {
        $this->attenuations[] = [
            'parentRevocationIds' => $event->parent->revocationIds(),
            'childRevocationIds' => $event->child->revocationIds(),
            'blockSource' => $event->blockSource,
            'sizeDelta' => \strlen($event->child->toBase64()) - \strlen($event->parent->toBase64()),
        ];
    }

    public function setBiscuit(Biscuit $biscuit): void
    {
        $this->currentBiscuit = $biscuit;
    }

    public function setSerializedToken(string $token): void
    {
        $this->serializedToken = $token;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param array<string, string> $policies
     */
    public function setPolicies(array $policies): void
    {
        $this->policies = $policies;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function recordPolicyCheck(string $policy, bool $result, array $params = []): void
    {
        $this->policyChecks[] = [
            'policy' => $policy,
            'result' => $result,
            'params' => $params,
        ];
    }

    public static function getTemplate(): ?string
    {
        return '@Biscuit/data_collector/biscuit.html.twig';
    }

    public function getName(): string
    {
        return 'biscuit';
    }

    public function hasToken(): bool
    {
        return $this->data['has_token'] ?? false;
    }

    public function getBlockCount(): int
    {
        return $this->data['block_count'] ?? 0;
    }

    /**
     * @return array<int, string>
     */
    public function getBlocks(): array
    {
        return $this->data['blocks'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function getRevocationIds(): array
    {
        return $this->data['revocation_ids'] ?? [];
    }

    /**
     * @return array<int, array{policy: string, result: bool, params: array<string, mixed>}>
     */
    public function getPolicyChecks(): array
    {
        return $this->data['policy_checks'] ?? [];
    }

    public function getPolicyCheckCount(): int
    {
        return $this->data['policy_check_count'] ?? 0;
    }

    public function getPassedChecks(): int
    {
        return $this->data['passed_checks'] ?? 0;
    }

    public function getFailedChecks(): int
    {
        return $this->data['failed_checks'] ?? 0;
    }

    public function getSerializedToken(): ?string
    {
        return $this->data['serialized_token'] ?? null;
    }

    public function getPublicKey(): ?string
    {
        return $this->data['public_key'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function getPolicies(): array
    {
        return $this->data['policies'] ?? [];
    }

    public function reset(): void
    {
        parent::reset();
        $this->currentBiscuit = null;
        $this->serializedToken = null;
        $this->publicKey = null;
        $this->policyChecks = [];
        $this->policies = [];
        $this->attenuations = [];
    }

    /**
     * @return list<array{parentRevocationIds: list<string>, childRevocationIds: list<string>, blockSource: string, sizeDelta: int}>
     */
    public function getAttenuations(): array
    {
        return $this->data['attenuations'] ?? [];
    }

    public function getAttenuationCount(): int
    {
        return $this->data['attenuation_count'] ?? 0;
    }

    /**
     * @return array<int, string>
     */
    private function collectBlocks(): array
    {
        if (null === $this->currentBiscuit) {
            return [];
        }

        $blocks = [];
        $blockCount = $this->currentBiscuit->blockCount();

        for ($i = 0; $i < $blockCount; ++$i) {
            $blocks[] = $this->currentBiscuit->blockSource($i);
        }

        return $blocks;
    }

    private function countPassedChecks(): int
    {
        return \count(array_filter($this->policyChecks, static fn (array $check): bool => $check['result']));
    }

    private function countFailedChecks(): int
    {
        return \count(array_filter($this->policyChecks, static fn (array $check): bool => !$check['result']));
    }
}
