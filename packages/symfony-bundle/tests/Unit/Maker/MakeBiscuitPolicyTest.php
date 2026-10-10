<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Maker;

use Biscuit\BiscuitBundle\Maker\MakeBiscuitPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;

#[CoversClass(MakeBiscuitPolicy::class)]
final class MakeBiscuitPolicyTest extends TestCase
{
    private MakeBiscuitPolicy $maker;

    protected function setUp(): void
    {
        $this->maker = new MakeBiscuitPolicy();
    }

    #[Test]
    public function itReturnsCorrectCommandName(): void
    {
        self::assertSame('make:biscuit-policy', MakeBiscuitPolicy::getCommandName());
    }

    #[Test]
    public function itReturnsCorrectCommandDescription(): void
    {
        self::assertSame('Creates a new Biscuit policy class', MakeBiscuitPolicy::getCommandDescription());
    }

    #[Test]
    public function itConfiguresCommandWithNameArgument(): void
    {
        $command = new Command('test');
        $inputConfig = new InputConfiguration();

        $this->maker->configureCommand($command, $inputConfig);

        $definition = $command->getDefinition();

        self::assertTrue($definition->hasArgument('name'));

        $argument = $definition->getArgument('name');
        self::assertTrue($argument->isRequired());
        self::assertSame('The name of the policy class (e.g. ArticleVoterPolicy)', $argument->getDescription());
    }

    #[Test]
    public function itConfiguresCommandWithHelpText(): void
    {
        $command = new Command('test');
        $inputConfig = new InputConfiguration();

        $this->maker->configureCommand($command, $inputConfig);

        $help = $command->getHelp();
        self::assertStringContainsString('%command.name%', $help);
        self::assertStringContainsString('ArticleVoterPolicy', $help);
    }

    #[Test]
    public function itConfiguresDependencies(): void
    {
        $dependencies = new DependencyBuilder();

        $this->maker->configureDependencies($dependencies);

        $missingPackages = $dependencies->getMissingDependencies();
        self::assertEmpty($missingPackages);
    }

    #[Test]
    public function templateFileExists(): void
    {
        $templatePath = __DIR__ . '/../../../src/Resources/skeleton/policy/Policy.tpl.php';
        self::assertFileExists($templatePath);
    }

    #[Test]
    public function templateContainsExpectedContent(): void
    {
        $templatePath = __DIR__ . '/../../../src/Resources/skeleton/policy/Policy.tpl.php';
        $content = file_get_contents($templatePath);

        self::assertIsString($content);
        self::assertStringContainsString('declare(strict_types=1)', $content);
        self::assertStringContainsString('namespace <?php echo $namespace; ?>', $content);
        self::assertStringContainsString('final class <?php echo $class_name; ?>', $content);
        self::assertStringContainsString('const NAME', $content);
        self::assertStringContainsString('const POLICY', $content);
        self::assertStringContainsString('allow if user($id)', $content);
        self::assertStringContainsString('https://www.biscuitsec.org/', $content);
    }

    #[Test]
    public function templateHasProperPhpDocumentation(): void
    {
        $templatePath = __DIR__ . '/../../../src/Resources/skeleton/policy/Policy.tpl.php';
        $content = file_get_contents($templatePath);

        self::assertIsString($content);
        self::assertStringContainsString('Biscuit policy class', $content);
        self::assertStringContainsString('#[IsGranted]', $content);
        self::assertStringContainsString('Using #[IsGranted] attribute', $content);
    }

    #[Test]
    public function templateGeneratesValidPolicyName(): void
    {
        $templatePath = __DIR__ . '/../../../src/Resources/skeleton/policy/Policy.tpl.php';
        $content = file_get_contents($templatePath);

        self::assertIsString($content);
        self::assertStringContainsString("strtolower(preg_replace('/([a-z])([A-Z])/'", $content);
        self::assertStringContainsString("str_replace('Policy', ''", $content);
    }
}
