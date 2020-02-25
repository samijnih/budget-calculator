<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\Guard;
use League\CLImate\CLImate;

final class CliBudgetCalculator
{
    private CLImate $climate;
    private Guard $guard;
    /** @var Command[] */
    private array $commands = [];
    private array $menu = [];

    public function __construct(CLImate $climate, Guard $guard)
    {
        $this->climate = $climate;
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

        while (true) {
            $this->climate->clear();

            $selected = $this->climate->radio('Menu', $this->menu)->prompt();

            foreach ($this->commands as $command) {
                if ($command->name() === $selected) {
                    if ($command instanceof AuthenticationRequired) {
                        $this->guard->authenticate();
                    }

                    $command->execute();
                }
            }

            $this->climate->br();
            $confirmation = $this->climate->confirm('Return to the menu?');

            if (!$confirmation->confirmed()) {
                exit(0);
            }
        }
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
