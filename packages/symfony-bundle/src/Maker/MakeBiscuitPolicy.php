<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeBiscuitPolicy extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:biscuit-policy';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Biscuit policy class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the policy class (e.g. ArticleVoterPolicy)')
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command generates a new Biscuit policy class.

                    <info>php %command.full_name% ArticleVoterPolicy</info>
                    HELP
            );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $name */
        $name = $input->getArgument('name');

        $policyClassNameDetails = $generator->createClassNameDetails(
            $name,
            'Security\\Policy\\',
            'Policy',
        );

        $generator->generateClass(
            $policyClassNameDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/policy/Policy.tpl.php',
            [
                'class_name' => $policyClassNameDetails->getShortName(),
            ],
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: Open your new policy class and customize the authorization logic.',
            'Documentation: <fg=yellow>https://www.biscuitsec.org/</>',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(
            Command::class,
            'symfony/console',
        );
    }
}
