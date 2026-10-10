<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\BiscuitBundle\Revocation\EnumerableRevocationStoreInterface;
use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'biscuit:revocation:list',
    description: 'List the entries of every revocation store that can be enumerated',
)]
final class RevocationListCommand extends Command
{
    /**
     * @param iterable<array-key, EnumerableRevocationStoreInterface> $stores
     */
    public function __construct(private readonly iterable $stores = [])
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of entries to show', '50')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Number of entries to skip', '0')
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Only show entries recorded for this subject')
            ->addOption('expired', null, InputOption::VALUE_NONE, 'Only show entries whose expiration has passed')
            ->addOption('active', null, InputOption::VALUE_NONE, 'Only show entries that have not expired')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'One of table, json, txt', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $format */
        $format = $input->getOption('format');

        if (!\in_array($format, ['table', 'json', 'txt'], true)) {
            $io->error(sprintf('Unknown format "%s". Use table, json or txt.', $format));

            return Command::FAILURE;
        }

        if ((bool) $input->getOption('expired') && (bool) $input->getOption('active')) {
            $io->error('Use either --expired or --active, not both.');

            return Command::FAILURE;
        }

        try {
            $entries = $this->collect($input);
        } catch (RevocationStoreUnavailableException $e) {
            $io->error('A revocation store could not be read: ' . $e->getMessage());

            return Command::FAILURE;
        }

        if ([] === $entries) {
            if ('table' === $format) {
                $io->warning('No enumerable revocation store holds any matching entry.');
            }

            return Command::SUCCESS;
        }

        match ($format) {
            'txt' => $this->renderText($output, $entries),
            'json' => $this->renderJson($output, $entries),
            default => $this->renderTable($io, $entries),
        };

        return Command::SUCCESS;
    }

    /**
     * @return list<RevocationEntry>
     */
    private function collect(InputInterface $input): array
    {
        $now = new DateTimeImmutable();
        /** @var string|null $subject */
        $subject = $input->getOption('subject');
        $onlyExpired = (bool) $input->getOption('expired');
        $onlyActive = (bool) $input->getOption('active');

        $offset = max(0, (int) $input->getOption('offset'));
        $limit = max(1, (int) $input->getOption('limit'));
        $wanted = $offset + $limit;

        $entries = [];

        foreach ($this->stores as $store) {
            foreach ($store->all() as $entry) {
                if (null !== $subject && $entry->subject !== $subject) {
                    continue;
                }

                $expired = null !== $entry->expiresAt && $entry->expiresAt < $now;

                if ($onlyExpired && !$expired) {
                    continue;
                }

                if ($onlyActive && $expired) {
                    continue;
                }

                $entries[] = $entry;

                if (\count($entries) >= $wanted) {
                    return \array_slice($entries, $offset, $limit);
                }
            }
        }

        return \array_slice($entries, $offset, $limit);
    }

    /**
     * @param list<RevocationEntry> $entries
     */
    private function renderText(OutputInterface $output, array $entries): void
    {
        foreach ($entries as $entry) {
            $output->writeln($entry->revocationId, OutputInterface::OUTPUT_RAW);
        }
    }

    /**
     * @param list<RevocationEntry> $entries
     */
    private function renderJson(OutputInterface $output, array $entries): void
    {
        $rows = array_map(
            static fn (RevocationEntry $entry): array => [
                'revocation_id' => $entry->revocationId,
                'expires_at' => $entry->expiresAt?->format(DateTimeImmutable::ATOM),
                'revoked_at' => $entry->revokedAt?->format(DateTimeImmutable::ATOM),
                'subject' => $entry->subject,
                'reason' => $entry->reason,
            ],
            $entries,
        );

        $output->writeln((string) json_encode($rows, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param list<RevocationEntry> $entries
     */
    private function renderTable(SymfonyStyle $io, array $entries): void
    {
        $rows = array_map(
            static fn (RevocationEntry $entry): array => [
                $entry->revocationId,
                $entry->expiresAt?->format(DateTimeImmutable::ATOM) ?? 'never',
                $entry->subject ?? '-',
                $entry->reason ?? '-',
            ],
            $entries,
        );

        $io->table(['Identifier', 'Expires at', 'Subject', 'Reason'], $rows);

        $neverExpires = \count(array_filter($entries, static fn (RevocationEntry $e): bool => null === $e->expiresAt));

        if ($neverExpires > 0) {
            $io->warning(sprintf(
                '%d entry(ies) have no expiration and will never be purged. Give your tokens an expiration date so the list stays bounded.',
                $neverExpires,
            ));
        }
    }
}
