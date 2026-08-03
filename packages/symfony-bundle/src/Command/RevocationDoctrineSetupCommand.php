<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\BiscuitBundle\Revocation\Exception\RevocationStoreUnavailableException;
use Biscuit\BiscuitBundle\Revocation\Store\DoctrineRevocationStore;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'biscuit:revocation:doctrine:setup',
    description: 'Create the table the Doctrine revocation store reads and writes',
)]
final class RevocationDoctrineSetupCommand extends Command
{
    public function __construct(
        private readonly DoctrineRevocationStore $store,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Print the statements instead of running them')
            ->setHelp(<<<'HELP'
                Applications that use Doctrine migrations do not need this command:
                <info>doctrine:migrations:diff</info> already picks the table up, and the migration
                it generates is reviewable. Use this when you have no migrations.
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dumpSql = (bool) $input->getOption('dump-sql');

        try {
            $statements = $this->store->schemaSql();

            if ($dumpSql) {
                foreach ($statements as $statement) {
                    $output->writeln($statement . ';', OutputInterface::OUTPUT_RAW);
                }

                return Command::SUCCESS;
            }

            if ($this->store->tableExists()) {
                $io->success(sprintf('Table "%s" already exists. Nothing to do.', $this->store->table()));

                return Command::SUCCESS;
            }

            foreach ($statements as $statement) {
                $this->connection->executeStatement($statement);
            }
        } catch (RevocationStoreUnavailableException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Created table "%s".', $this->store->table()));

        return Command::SUCCESS;
    }
}
