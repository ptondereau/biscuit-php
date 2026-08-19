<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationResult;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:revocation:check',
    description: 'Check whether a token or identifier is revoked (exit 0 valid, 1 revoked, 2 error)',
)]
final class RevocationCheckCommand extends Command
{
    public function __construct(
        private readonly ?RevocationChecker $checker = null,
        private readonly ?BiscuitTokenManager $tokenManager = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::OPTIONAL, 'The base64-encoded token to check')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Check raw revocation identifiers instead of a token',
            )
            ->addOption('verify', null, InputOption::VALUE_NONE, 'Verify the token signature before checking')
            ->addOption('explain', null, InputOption::VALUE_NONE, 'Show the verdict of every store consulted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $this->checker) {
            $io->error('Revocation is not enabled. Set biscuit.revocation.enabled and biscuit.revocation.on_unavailable.');

            return Command::INVALID;
        }

        /** @var string|null $tokenString */
        $tokenString = $input->getArgument('token');
        /** @var list<string> $rawIds */
        $rawIds = $input->getOption('id');

        if (null === $tokenString && [] === $rawIds) {
            $io->error('Provide a token argument or at least one --id option.');

            return Command::INVALID;
        }

        try {
            $result = null !== $tokenString
                ? $this->checkToken($input, $tokenString)
                : $this->checker->checkIds(array_values(array_filter($rawIds, static fn (string $id): bool => '' !== $id)));
        } catch (RevocationStoreUnavailableException $e) {
            $io->error('A revocation store is unavailable: ' . $e->getMessage());

            return Command::INVALID;
        } catch (Throwable $e) {
            $io->error('Failed to check token: ' . $e->getMessage());

            return Command::INVALID;
        }

        if ((bool) $input->getOption('explain')) {
            $this->explain($io, $result);
        }

        if ($result->degraded) {
            $io->warning('At least one store could not answer, so this verdict is incomplete.');
        }

        if ($result->isRevoked()) {
            $io->error(sprintf('Revoked by "%s": %s', (string) $result->store, (string) $result->revokedId));

            return Command::FAILURE;
        }

        $io->success('Not revoked.');

        return Command::SUCCESS;
    }

    private function checkToken(InputInterface $input, string $tokenString): RevocationResult
    {
        assert(null !== $this->checker);

        $verify = (bool) $input->getOption('verify');

        $token = $verify && null !== $this->tokenManager
            ? $this->tokenManager->parse($tokenString)
            : UnverifiedBiscuit::fromBase64($tokenString);

        return $this->checker->check($token);
    }

    private function explain(SymfonyStyle $io, RevocationResult $result): void
    {
        $io->section('Identifiers checked');

        foreach ($result->checkedIds as $index => $id) {
            $io->writeln(sprintf('  %d: %s', $index, $id));
        }

        $io->section('Stores consulted');

        $rows = [];

        foreach ($result->outcomes as $outcome) {
            $rows[] = [
                $outcome->store,
                null !== $outcome->error ? 'unavailable' : (null !== $outcome->revokedId ? 'matched' : 'no match'),
                $outcome->error ?? ($outcome->revokedId ?? '-'),
                sprintf('%.3f ms', $outcome->durationMs),
            ];
        }

        if ([] === $rows) {
            $io->writeln('<comment>No store was consulted.</comment>');

            return;
        }

        $io->table(['Store', 'Result', 'Detail', 'Time'], $rows);
    }
}
