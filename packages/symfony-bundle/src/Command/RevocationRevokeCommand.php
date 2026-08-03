<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\RevocationEntryFactory;
use Biscuit\BiscuitBundle\Revocation\RevocationWriterInterface;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:revocation:revoke',
    description: 'Revoke a Biscuit token or a raw revocation identifier',
)]
final class RevocationRevokeCommand extends Command
{
    public function __construct(
        private readonly ?RevocationWriterInterface $writer = null,
        private readonly ?RevocationEntryFactory $entryFactory = null,
        private readonly ?BiscuitTokenManagerInterface $tokenManager = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'token',
                InputArgument::OPTIONAL,
                'The base64-encoded token to revoke. Only its deepest identifier is revoked, so ancestors keep working',
            )
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Revoke a raw revocation identifier instead of a token',
            )
            ->addOption(
                'all-ids',
                null,
                InputOption::VALUE_NONE,
                'Revoke every identifier in the chain, which also invalidates tokens derived from any shared ancestor',
            )
            ->addOption(
                'verify',
                null,
                InputOption::VALUE_NONE,
                'Verify the token signature before extracting identifiers',
            )
            ->addOption('expires-at', null, InputOption::VALUE_REQUIRED, 'RFC 3339 date after which the entry may be purged')
            ->addOption('ttl', null, InputOption::VALUE_REQUIRED, 'Seconds until the entry may be purged')
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Subject recorded alongside the entry')
            ->addOption('reason', null, InputOption::VALUE_REQUIRED, 'Reason recorded alongside the entry')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be revoked without writing anything');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $this->writer || null === $this->entryFactory) {
            $io->error('Revocation is not enabled. Set biscuit.revocation.enabled and biscuit.revocation.on_unavailable.');

            return Command::FAILURE;
        }

        /** @var string|null $tokenString */
        $tokenString = $input->getArgument('token');
        /** @var list<string> $rawIds */
        $rawIds = $input->getOption('id');
        $dryRun = (bool) $input->getOption('dry-run');

        if (null === $tokenString && [] === $rawIds) {
            $io->error('Provide a token argument or at least one --id option.');

            return Command::FAILURE;
        }

        if (null !== $tokenString && [] !== $rawIds) {
            $io->error('Provide either a token argument or --id options, not both.');

            return Command::FAILURE;
        }

        $expiresAt = $this->resolveExpiration($io, $input);

        if (false === $expiresAt) {
            return Command::FAILURE;
        }

        /** @var string|null $subject */
        $subject = $input->getOption('subject');
        /** @var string|null $reason */
        $reason = $input->getOption('reason');

        $entries = null !== $tokenString
            ? $this->entriesFromToken($io, $input, $tokenString, $reason)
            : array_map(
                static fn (string $id): RevocationEntry => new RevocationEntry(
                    revocationId: '' !== $id ? $id : 'invalid',
                    expiresAt: null,
                    revokedAt: new DateTimeImmutable(),
                    subject: null,
                    reason: $reason,
                ),
                $rawIds,
            );

        if (null === $entries) {
            return Command::FAILURE;
        }

        $entries = array_map(
            static fn (RevocationEntry $entry): RevocationEntry => new RevocationEntry(
                revocationId: $entry->revocationId,
                expiresAt: $expiresAt ?? $entry->expiresAt,
                revokedAt: $entry->revokedAt,
                subject: $subject ?? $entry->subject,
                reason: $entry->reason,
            ),
            $entries,
        );

        $io->section(sprintf('%s %d identifier(s)', $dryRun ? 'Would revoke' : 'Revoking', \count($entries)));

        foreach ($entries as $entry) {
            $io->writeln(sprintf('- %s', $entry->revocationId));
        }

        if ($dryRun) {
            $io->note('Nothing was written.');

            return Command::SUCCESS;
        }

        if (\count($entries) > 1 && !$io->confirm('Revoking every identifier also invalidates sibling tokens. Continue?', false)) {
            $io->warning('Aborted.');

            return Command::FAILURE;
        }

        foreach ($entries as $entry) {
            $this->writer->revoke($entry);
        }

        $io->success(sprintf('Revoked %d identifier(s).', \count($entries)));

        return Command::SUCCESS;
    }

    /**
     * @return list<RevocationEntry>|null
     */
    private function entriesFromToken(
        SymfonyStyle $io,
        InputInterface $input,
        string $tokenString,
        ?string $reason,
    ): ?array {
        $verify = (bool) $input->getOption('verify');

        try {
            $token = $verify && null !== $this->tokenManager
                ? $this->tokenManager->parse($tokenString)
                : UnverifiedBiscuit::fromBase64($tokenString);
        } catch (Throwable $e) {
            $io->error('Failed to read token: ' . $e->getMessage());

            return null;
        }

        if (!$verify) {
            $io->warning('Token read without signature verification. Use --verify to check the signature.');
        }

        $chain = $token->revocationIds();

        if ([] === $chain) {
            $io->error('This token carries no revocation identifiers.');

            return null;
        }

        $io->section('Revocation chain');
        $target = (bool) $input->getOption('all-ids') ? null : $chain[\count($chain) - 1];

        foreach ($chain as $index => $id) {
            $io->writeln(sprintf(
                '%s %d: %s%s',
                null === $target || $id === $target ? '>' : ' ',
                $index,
                $id,
                0 === $index ? ' (authority)' : '',
            ));
        }

        assert(null !== $this->entryFactory);

        return (bool) $input->getOption('all-ids')
            ? $this->entryFactory->allFromToken($token, $reason)
            : [$this->entryFactory->fromToken($token, $reason)];
    }

    private function resolveExpiration(SymfonyStyle $io, InputInterface $input): DateTimeImmutable|false|null
    {
        /** @var string|null $expiresAt */
        $expiresAt = $input->getOption('expires-at');
        /** @var string|null $ttl */
        $ttl = $input->getOption('ttl');

        if (null !== $expiresAt && null !== $ttl) {
            $io->error('Use either --expires-at or --ttl, not both.');

            return false;
        }

        if (null !== $ttl) {
            if (!ctype_digit($ttl) || 0 === (int) $ttl) {
                $io->error('--ttl must be a positive number of seconds.');

                return false;
            }

            return new DateTimeImmutable('@' . (time() + (int) $ttl));
        }

        if (null === $expiresAt) {
            return null;
        }

        try {
            return new DateTimeImmutable($expiresAt);
        } catch (Throwable) {
            $io->error(sprintf('--expires-at is not a valid date: %s', $expiresAt));

            return false;
        }
    }
}
