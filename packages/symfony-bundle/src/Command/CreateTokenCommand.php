<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\BiscuitBundle\Token\BiscuitTokenFactory;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:token:create',
    description: 'Create a Biscuit token from a template',
)]
final class CreateTokenCommand extends Command
{
    public function __construct(
        private readonly BiscuitTokenFactory $tokenFactory,
        private readonly BiscuitTokenManagerInterface $tokenManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'template',
                InputArgument::OPTIONAL,
                'The token template name to use',
            )
            ->addOption(
                'param',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Parameters in key=value format (e.g., -p user_id=123 -p role=admin)',
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'List available templates instead of creating a token',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listTemplates($io);
        }

        /** @var string|null $template */
        $template = $input->getArgument('template');

        if (null === $template) {
            $io->error('Template argument is required when not using --list');

            return Command::FAILURE;
        }

        /** @var array<int, string> $paramStrings */
        $paramStrings = $input->getOption('param');
        $params = $this->parseParams($paramStrings);

        if (!$this->tokenFactory->hasTemplate($template)) {
            $io->error(sprintf('Unknown template: %s', $template));
            $io->writeln('');
            $io->writeln('Available templates:');
            foreach ($this->tokenFactory->getTemplateNames() as $name) {
                $io->writeln(sprintf('  - %s', $name));
            }

            return Command::FAILURE;
        }

        try {
            $biscuit = $this->tokenFactory->create($template, $params);
            $serialized = $this->tokenManager->serialize($biscuit);
        } catch (Throwable $e) {
            $io->error('Failed to create token: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->section('Token Created');
        $io->definitionList(
            ['Template' => $template],
            ['Parameters' => [] === $params ? '(none)' : implode(', ', array_map(
                static fn (string $key, mixed $value): string => sprintf('%s=%s', $key, var_export($value, true)),
                array_keys($params),
                array_values($params),
            ))],
        );

        $io->section('Base64 Token');
        $io->writeln($serialized);

        $io->section('Token Contents');
        for ($i = 0; $i < $biscuit->blockCount(); ++$i) {
            $io->writeln(sprintf('<info>Block %d:</info>', $i));
            $io->writeln($biscuit->blockSource($i));
        }

        $io->success('Token created successfully.');

        return Command::SUCCESS;
    }

    private function listTemplates(SymfonyStyle $io): int
    {
        $templates = $this->tokenFactory->getTemplateNames();

        if ([] === $templates) {
            $io->warning('No token templates configured.');
            $io->writeln('');
            $io->writeln('Add templates to your configuration:');
            $io->writeln([
                '',
                '# config/packages/biscuit.yaml',
                'biscuit:',
                '    token_templates:',
                '        user_token:',
                '            facts:',
                '                - \'user({user_id})\'',
                '            checks:',
                '                - \'check if time($time), $time < {expiration}\'',
            ]);

            return Command::SUCCESS;
        }

        $io->section('Available Token Templates');
        foreach ($templates as $name) {
            $io->writeln(sprintf('  - %s', $name));
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<int, string> $paramStrings
     *
     * @return array<string, mixed>
     */
    private function parseParams(array $paramStrings): array
    {
        $params = [];

        foreach ($paramStrings as $paramString) {
            if (!str_contains($paramString, '=')) {
                throw new InvalidArgumentException(sprintf('Invalid parameter format: "%s". Expected "key=value".', $paramString));
            }

            [$key, $value] = explode('=', $paramString, 2);

            $params[$key] = $this->parseValue($value);
        }

        return $params;
    }

    private function parseValue(string $value): mixed
    {
        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        if ('null' === $value) {
            return null;
        }

        if (is_numeric($value) && !str_contains($value, '.')) {
            return (int) $value;
        }

        return $value;
    }
}
