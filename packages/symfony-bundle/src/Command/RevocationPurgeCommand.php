<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:revocation:purge',
    description: 'Drop revocation entries whose expiration has passed',
)]
final class RevocationPurgeCommand extends Command
{
    /**
     * @param iterable<array-key, EnumerableRevocationStoreInterface> $stores
     */
    public function __construct(
        private readonly ?RevocationWriterInterface $writer = null,
        private readonly iterable $stores = [],
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('before', null, InputOption::VALUE_REQUIRED, 'Purge entries that expired before this RFC 3339 date')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report what would be purged without writing anything')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Skip the confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $this->writer) {
            $io->error('Revocation is not enabled. Set biscuit.revocation.enabled and biscuit.revocation.on_unavailable.');

            return Command::FAILURE;
        }

        /** @var string|null $before */
        $before = $input->getOption('before');

        try {
            $now = null !== $before ? new DateTimeImmutable($before) : new DateTimeImmutable();
        } catch (Throwable) {
            $io->error(sprintf('--before is not a valid date: %s', (string) $before));

            return Command::FAILURE;
        }

        $this->warnAboutEntriesThatNeverExpire($io);

        if ((bool) $input->getOption('dry-run')) {
            $io->note(sprintf('Would purge entries expiring before %s.', $now->format(DateTimeImmutable::ATOM)));

            return Command::SUCCESS;
        }

        if (!(bool) $input->getOption('force')
            && !$io->confirm(sprintf('Purge entries expiring before %s?', $now->format(DateTimeImmutable::ATOM)), false)) {
            $io->warning('Aborted.');

            return Command::FAILURE;
        }

        $purged = $this->writer->purgeExpired($now);

        $io->success(sprintf('Purged %d entry(ies).', $purged));

        return Command::SUCCESS;
    }

    private function warnAboutEntriesThatNeverExpire(SymfonyStyle $io): void
    {
        $neverExpires = 0;

        foreach ($this->stores as $store) {
            foreach ($store->all() as $entry) {
                if (null === $entry->expiresAt) {
                    ++$neverExpires;
                }
            }
        }

        if ($neverExpires > 0) {
            $io->warning(sprintf(
                '%d entry(ies) have no expiration and cannot be purged. Give your tokens an expiration date so the list stays bounded.',
                $neverExpires,
            ));
        }
    }
}
