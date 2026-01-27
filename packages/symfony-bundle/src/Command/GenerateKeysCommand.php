<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\Algorithm;
use Biscuit\Auth\KeyPair;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'biscuit:keys:generate',
    description: 'Generate a new Biscuit key pair',
)]
final class GenerateKeysCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'algorithm',
            'a',
            InputOption::VALUE_REQUIRED,
            'Algorithm to use (ed25519 or secp256r1)',
            'ed25519',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $algorithm */
        $algorithm = $input->getOption('algorithm');

        $algo = match ($algorithm) {
            'ed25519' => Algorithm::Ed25519,
            'secp256r1' => Algorithm::Secp256r1,
            default => throw new InvalidArgumentException(sprintf('Unknown algorithm: %s. Use "ed25519" or "secp256r1".', $algorithm)),
        };

        $keyPair = new KeyPair($algo);

        $io->section('Generated Key Pair');
        $io->definitionList(
            ['Algorithm' => $algorithm],
            ['Public Key' => $keyPair->getPublicKey()->toHex()],
            ['Private Key' => $keyPair->getPrivateKey()->toHex()],
        );

        $io->warning('Store the private key securely! It will not be shown again.');

        $io->section('Configuration Example');
        $io->writeln([
            '# Add to your config/packages/biscuit.yaml:',
            'biscuit:',
            '    keys:',
            sprintf('        algorithm: %s', $algorithm),
            sprintf("        public_key: '%%env(BISCUIT_PUBLIC_KEY)%%'"),
            sprintf("        private_key: '%%env(BISCUIT_PRIVATE_KEY)%%'"),
            '',
            '# Add to your .env.local:',
            sprintf('BISCUIT_PUBLIC_KEY=%s', $keyPair->getPublicKey()->toHex()),
            sprintf('BISCUIT_PRIVATE_KEY=%s', $keyPair->getPrivateKey()->toHex()),
        ]);

        $io->success('Key pair generated successfully.');

        return Command::SUCCESS;
    }
}
