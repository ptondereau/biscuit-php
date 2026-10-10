<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Provides compatibility for adding commands to Symfony Console Application
 * across different Symfony versions (6.4-8.0).
 *
 * - Symfony 6.4/7.x: uses add()
 * - Symfony 8.0+: uses addCommand()
 */
trait ConsoleApplicationTrait
{
    private function addCommandToApplication(Application $application, Command $command): void
    {
        if (method_exists($application, 'addCommand')) {
            $application->addCommand($command);
        } else {
            $application->add($command);
        }
    }
}
