<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Key;

use Biscuit\Auth\Algorithm;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use InvalidArgumentException;
use RuntimeException;

final class KeyManager
{
    private ?PublicKey $cachedPublicKey = null;

    private ?PrivateKey $cachedPrivateKey = null;

    public function __construct(
        private readonly ?string $publicKey,
        private readonly ?string $privateKey,
        private readonly ?string $publicKeyFile,
        private readonly ?string $privateKeyFile,
        private readonly string $algorithm,
    ) {
    }

    public function getPublicKey(): PublicKey
    {
        if (null !== $this->cachedPublicKey) {
            return $this->cachedPublicKey;
        }

        if (null !== $this->publicKey) {
            $this->cachedPublicKey = new PublicKey($this->publicKey);

            return $this->cachedPublicKey;
        }

        if (null !== $this->publicKeyFile) {
            $pemContent = $this->readKeyFile($this->publicKeyFile);
            $publicKey = PublicKey::fromPem($pemContent);
            $this->cachedPublicKey = $publicKey;

            return $publicKey;
        }

        throw new RuntimeException('No public key configured. Set either "biscuit.keys.public_key" or "biscuit.keys.public_key_file".');
    }

    public function getPrivateKey(): PrivateKey
    {
        if (null !== $this->cachedPrivateKey) {
            return $this->cachedPrivateKey;
        }

        if (null !== $this->privateKey) {
            $this->cachedPrivateKey = new PrivateKey($this->privateKey);

            return $this->cachedPrivateKey;
        }

        if (null !== $this->privateKeyFile) {
            $pemContent = $this->readKeyFile($this->privateKeyFile);
            $privateKey = PrivateKey::fromPem($pemContent);
            $this->cachedPrivateKey = $privateKey;

            return $privateKey;
        }

        throw new RuntimeException('No private key configured. Set either "biscuit.keys.private_key" or "biscuit.keys.private_key_file".');
    }

    public function getAlgorithm(): Algorithm
    {
        return match ($this->algorithm) {
            'ed25519' => Algorithm::Ed25519,
            'secp256r1' => Algorithm::Secp256r1,
            default => throw new InvalidArgumentException(sprintf('Unknown algorithm: %s', $this->algorithm)),
        };
    }

    public function hasPublicKey(): bool
    {
        return null !== $this->publicKey || null !== $this->publicKeyFile;
    }

    public function hasPrivateKey(): bool
    {
        return null !== $this->privateKey || null !== $this->privateKeyFile;
    }

    private function readKeyFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('Key file not found: %s', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Key file is not readable: %s', $filePath));
        }

        $content = file_get_contents($filePath);

        if (false === $content) {
            throw new RuntimeException(sprintf('Failed to read key file: %s', $filePath));
        }

        return $content;
    }
}
