<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:token:inspect',
    description: 'Inspect a Biscuit token',
)]
final class InspectTokenCommand extends Command
{
    public function __construct(
        private readonly BiscuitTokenManagerInterface $tokenManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'The base64-encoded token to inspect',
            )
            ->addOption(
                'verify',
                null,
                InputOption::VALUE_NONE,
                'Verify the token signature using the configured public key',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $tokenString */
        $tokenString = $input->getArgument('token');
        $verify = $input->getOption('verify');

        if ($verify) {
            return $this->inspectVerified($io, $tokenString);
        }

        return $this->inspectUnverified($io, $tokenString);
    }

    private function inspectVerified(SymfonyStyle $io, string $tokenString): int
    {
        try {
            $biscuit = $this->tokenManager->parse($tokenString);
        } catch (Throwable $e) {
            $io->error('Failed to parse/verify token: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Token signature is valid.');

        $io->section('Token Information');
        $io->definitionList(
            ['Block count' => (string) $biscuit->blockCount()],
        );

        for ($i = 0; $i < $biscuit->blockCount(); ++$i) {
            $io->section(sprintf('Block %d%s', $i, 0 === $i ? ' (Authority)' : ''));
            $io->writeln($biscuit->blockSource($i));
        }

        $io->section('Revocation IDs');
        $revocationIds = $biscuit->revocationIds();
        if ([] === $revocationIds) {
            $io->writeln('<comment>No revocation IDs</comment>');
        } else {
            foreach ($revocationIds as $id) {
                $io->writeln(sprintf('- %s', $id));
            }
        }

        return Command::SUCCESS;
    }

    private function inspectUnverified(SymfonyStyle $io, string $tokenString): int
    {
        try {
            $unverified = UnverifiedBiscuit::fromBase64($tokenString);
        } catch (Throwable $e) {
            $io->error('Failed to parse token: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->warning('Token shown without signature verification. Use --verify to check signature.');

        $io->section('Token Information');
        $rootKeyId = $unverified->rootKeyId();
        $io->definitionList(
            ['Block count' => (string) $unverified->blockCount()],
            ['Root key ID' => null !== $rootKeyId ? (string) $rootKeyId : '<not set>'],
        );

        for ($i = 0; $i < $unverified->blockCount(); ++$i) {
            $io->section(sprintf('Block %d%s', $i, 0 === $i ? ' (Authority)' : ''));
            $io->writeln($unverified->blockSource($i));
        }

        $io->section('Revocation IDs');
        $revocationIds = $unverified->revocationIds();
        if ([] === $revocationIds) {
            $io->writeln('<comment>No revocation IDs</comment>');
        } else {
            foreach ($revocationIds as $id) {
                $io->writeln(sprintf('- %s', $id));
            }
        }

        return Command::SUCCESS;
    }
}
