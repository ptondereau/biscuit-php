<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Command;

use Biscuit\Auth\Fact;
use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\Exception\AuthorizationException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'biscuit:policy:test',
    description: 'Test a policy against facts with an optional token',
)]
final class TestPolicyCommand extends Command
{
    use CliParams;

    public function __construct(
        private readonly PolicyRegistry $policyRegistry,
        private readonly BiscuitTokenManager $tokenManager,
        private readonly AuthorizerBuilderFactory $authorizerBuilderFactory = new AuthorizerBuilderFactory(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'policy',
                InputArgument::OPTIONAL,
                'The policy name or inline policy string (e.g., "allow if user($u)")',
            )
            ->addOption(
                'fact',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Facts to add to the authorizer (e.g., -f \'user("alice")\' -f \'resource("file1")\')',
            )
            ->addOption(
                'token',
                't',
                InputOption::VALUE_REQUIRED,
                'Base64-encoded token to authorize (if not provided, tests policy with facts only)',
            )
            ->addOption(
                'param',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Policy parameters in key=value format (e.g., -p user_id=123)',
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'List available policies instead of testing',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listPolicies($io);
        }

        /** @var string|null $policyName */
        $policyName = $input->getArgument('policy');

        if (null === $policyName) {
            $io->error('Policy argument is required when not using --list');

            return Command::FAILURE;
        }

        /** @var array<int, string> $factStrings */
        $factStrings = $input->getOption('fact');

        /** @var string|null $tokenString */
        $tokenString = $input->getOption('token');

        /** @var array<int, string> $paramStrings */
        $paramStrings = $input->getOption('param');
        $params = $this->parseParams($paramStrings);

        try {
            $policy = $this->policyRegistry->get($policyName, $params);
        } catch (Throwable $e) {
            $io->error(sprintf('Failed to load policy "%s": %s', $policyName, $e->getMessage()));

            return Command::FAILURE;
        }

        $io->section('Testing Policy');
        $io->definitionList(
            ['Policy' => $policyName],
            ['Resolved' => (string) $policy],
            ['With token' => null !== $tokenString ? 'Yes' : 'No'],
        );

        try {
            $authorizerBuilder = $this->authorizerBuilderFactory->create($policyName, $params);

            foreach ($factStrings as $factString) {
                if ('' === $factString) {
                    $io->error('Fact option must not be empty.');

                    return Command::FAILURE;
                }

                $authorizerBuilder->addFact(new Fact($factString));
            }

            $authorizerBuilder->addPolicy($policy);

            if (null !== $tokenString) {
                $biscuit = $this->tokenManager->parse($tokenString);

                $io->section('Token Contents');
                for ($i = 0; $i < $biscuit->blockCount(); ++$i) {
                    $io->writeln(sprintf('<info>Block %d:</info>', $i));
                    $io->writeln($biscuit->blockSource($i));
                }

                $authorizer = $authorizerBuilder->build($biscuit);
            } else {
                $authorizer = $authorizerBuilder->buildUnauthenticated();
            }

            $io->section('Authorizer State');
            $io->writeln((string) $authorizer);

            $matched = $authorizer->authorize();

            $io->section('Matched Policy');
            $io->definitionList(
                ['Kind' => $matched->getKind()],
                ['Id' => (string) $matched->getPolicyId()],
            );

            $io->success('Authorization PASSED.');

            return Command::SUCCESS;
        } catch (AuthorizationException $e) {
            $io->error('Authorization FAILED: ' . $e->getMessage());

            $matched = $e->getMatchedPolicy();
            if (null !== $matched) {
                $io->section('Matched Policy');
                $io->definitionList(
                    ['Kind' => $matched->getKind()],
                    ['Id' => (string) $matched->getPolicyId()],
                    ['Code' => $matched->getCode() ?? '(none)'],
                );
            }

            $failedChecks = $e->getFailedChecks();
            if ([] !== $failedChecks) {
                $io->section('Failed Checks');
                $rows = [];
                foreach ($failedChecks as $check) {
                    $rows[] = [
                        $check->getOrigin(),
                        null !== $check->getBlockId() ? (string) $check->getBlockId() : '(authorizer)',
                        (string) $check->getCheckId(),
                        $check->getRule(),
                    ];
                }
                $io->table(['Origin', 'Block ID', 'Check ID', 'Rule'], $rows);
            }

            return Command::FAILURE;
        } catch (Throwable $e) {
            $io->error('Error during authorization: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function listPolicies(SymfonyStyle $io): int
    {
        $policies = $this->policyRegistry->all();

        if ([] === $policies) {
            $io->warning('No policies configured.');
            $io->writeln('');
            $io->writeln('Add policies to your configuration:');
            $io->writeln([
                '',
                '# config/packages/biscuit.yaml',
                'biscuit:',
                '    policies:',
                '        is_admin: \'allow if user($u), role($u, "admin")\'',
                '        can_read: \'allow if right($resource, "read")\'',
            ]);

            return Command::SUCCESS;
        }

        $io->section('Available Policies');
        $rows = [];
        foreach ($policies as $name => $policy) {
            $rows[] = [$name, $policy];
        }
        $io->table(['Name', 'Policy'], $rows);

        return Command::SUCCESS;
    }
}
