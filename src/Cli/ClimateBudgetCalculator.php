<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Output\Question\RadioInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\Climate\ClimateGuard;

final class ClimateBudgetCalculator implements BudgetCalculator
{
    private Cli $cli;
    private ClimateGuard $guard;
    /** @var Command[] */
    private array $commands = [];
    private array $menu = [];

    public function __construct(Cli $cli, ClimateGuard $guard)
    {
        $this->cli = $cli;
        $this->guard = $guard;
    }

    public function registerCommand(Command $command): void
    {
        $name = $command->name();

        $this->commands[$name] = $command;
        $this->menu[$name] = $command->label();
    }

    public function run(): void
    {
        $this->registerCommand($this->generateQuitCommand());

        $this->cli->clear();

        $selected = $this->cli->prompt(new RadioInput('menu', 'Menu', $this->menu));

        foreach ($this->commands as $command) {
            if ($command->name() === $selected) {
                if ($command instanceof AuthenticationRequired) {
                    $this->guard->authenticate();
                }

                $command->execute();
            }
        }

        $this->cli->lineBreak();

        if (!$this->cli->confirm('Return to the menu?')) {
            exit(0);
        }

        $this->run();
    }

    private function generateQuitCommand(): Command
    {
        return new class () implements Command {
            public function label(): string
            {
                return 'Quit';
            }

            public function name(): string
            {
                return 'app:quit';
            }

            public function execute(): void
            {
                exit(0);
            }
        };
    }
}
