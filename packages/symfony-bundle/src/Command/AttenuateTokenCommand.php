<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\UnverifiedBiscuit;
use Biscuit\BiscuitBundle\Token\BiscuitBlockFactory;
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
    name: 'biscuit:token:attenuate',
    description: 'Attenuate a Biscuit token by appending a block',
)]
final class AttenuateTokenCommand extends Command
{
    public function __construct(
        private readonly BiscuitBlockFactory $blockFactory,
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
                'The base64-encoded parent token to attenuate',
            )
            ->addOption(
                'template',
                't',
                InputOption::VALUE_REQUIRED,
                'The block template name to apply',
            )
            ->addOption(
                'code',
                'c',
                InputOption::VALUE_REQUIRED,
                'Inline Datalog source for the attenuation block',
            )
            ->addOption(
                'param',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Template parameters in key=value format',
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'List available block templates instead of attenuating',
            )
            ->addOption(
                'unverified',
                null,
                InputOption::VALUE_NONE,
                'Skip signature verification of the parent token',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listTemplates($io);
        }

        /** @var string $tokenString */
        $tokenString = $input->getArgument('token');
        /** @var string|null $templateName */
        $templateName = $input->getOption('template');
        /** @var string|null $codeSource */
        $codeSource = $input->getOption('code');

        if (null !== $templateName && null !== $codeSource) {
            $io->error('--template and --code are mutually exclusive');

            return Command::FAILURE;
        }

        if (null === $templateName && null === $codeSource) {
            $io->error('Either --template or --code must be provided');

            return Command::FAILURE;
        }

        /** @var array<int, string> $paramStrings */
        $paramStrings = $input->getOption('param');
        $params = $this->parseParams($paramStrings);

        $unverified = (bool) $input->getOption('unverified');

        try {
            $block = null !== $templateName
                ? $this->blockFactory->buildBlock($templateName, $params)
                : $this->tokenManager->createBlockBuilder($codeSource, $params);
            $sourceLabel = null !== $templateName
                ? sprintf('template "%s"', $templateName)
                : 'inline code';

            if ($unverified) {
                $parent = UnverifiedBiscuit::fromBase64($tokenString);
                $child = $parent->append($block);
            } else {
                $verifiedParent = $this->tokenManager->parse($tokenString);
                $child = null !== $templateName
                    ? $this->blockFactory->attenuate($verifiedParent, $templateName, $params)
                    : $this->tokenManager->attenuate($verifiedParent, $block);
            }
        } catch (Throwable $e) {
            $io->error('Failed to attenuate token: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->section('Attenuation Applied');
        $io->definitionList(
            ['Source' => $sourceLabel],
            ['Verified' => $unverified ? 'no (--unverified)' : 'yes'],
            ['Block index' => (string) ($child->blockCount() - 1)],
        );

        $io->section('New Block Source');
        $io->writeln($child->blockSource($child->blockCount() - 1));

        if ($child instanceof UnverifiedBiscuit) {
            $io->section('Derived Token (base64)');
            $io->writeln('<comment>not available: UnverifiedBiscuit cannot be re-serialised (upstream limitation)</comment>');
        } else {
            $io->section('Derived Token (base64)');
            $io->writeln($child->toBase64());
        }

        $io->section('Revocation IDs');
        foreach ($child->revocationIds() as $id) {
            $io->writeln('- ' . $id);
        }

        $io->success('Token attenuated successfully.');

        return Command::SUCCESS;
    }

    private function listTemplates(SymfonyStyle $io): int
    {
        $names = $this->blockFactory->getTemplateNames();

        if ([] === $names) {
            $io->warning('No block templates configured.');
            $io->writeln('Add templates under biscuit.block_templates in your configuration.');

            return Command::SUCCESS;
        }

        $io->section('Available Block Templates');
        foreach ($names as $name) {
            $io->writeln('  - ' . $name);
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
                continue;
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
