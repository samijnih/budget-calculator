<?php

declare(strict_types=1);

use BudgetCalculator\Cli\CliBudgetCalculator;
use BudgetCalculator\Cli\Command\Budget\DefineBudgetCommand;
use BudgetCalculator\Cli\Command\Budget\DeleteBudgetsCommand;
use BudgetCalculator\Cli\Command\Budget\DisplayBudgetsCommand;
use BudgetCalculator\Cli\Command\Transaction\AddTransactionCommand;
use BudgetCalculator\Cli\Command\Transaction\DeleteTransactionsCommand;
use BudgetCalculator\Cli\Command\Transaction\DisplayTransactionsCommand;
use BudgetCalculator\Cli\Command\Transaction\EditTransactionsCommand;
use BudgetCalculator\Cli\Command\User\DeleteUserCommand;
use BudgetCalculator\Cli\Command\User\RegisterUserCommand;
use BudgetCalculator\Cli\Security\Guard;
use League\CLImate\CLImate;
use Psr\Container\ContainerInterface;
use function DI\factory;

return [
    CliBudgetCalculator::class => factory(static function (ContainerInterface $container) {
        $app = new CliBudgetCalculator($container->get(CLImate::class), $container->get(Guard::class));

        $app->registerCommand($container->get(RegisterUserCommand::class));
        $app->registerCommand($container->get(DeleteUserCommand::class));

        $app->registerCommand($container->get(AddTransactionCommand::class));
        $app->registerCommand($container->get(DisplayTransactionsCommand::class));
        $app->registerCommand($container->get(EditTransactionsCommand::class));
        $app->registerCommand($container->get(DeleteTransactionsCommand::class));

        $app->registerCommand($container->get(DefineBudgetCommand::class));
        $app->registerCommand($container->get(DisplayBudgetsCommand::class));
        $app->registerCommand($container->get(DeleteBudgetsCommand::class));

        return $app;
    })
];
