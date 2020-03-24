<?php

declare(strict_types=1);

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Adapter\ClimateAdapter;
use BudgetCalculator\Cli\BudgetCalculator;
use BudgetCalculator\Cli\ClimateBudgetCalculator;
use BudgetCalculator\Cli\CliMenu\RegisterUserMenuBuilder;
use BudgetCalculator\Cli\CliMenuBudgetCalculator;
use BudgetCalculator\Cli\Command\Climate\Budget\DefineBudgetCommand;
use BudgetCalculator\Cli\Command\Climate\Budget\DeleteBudgetsCommand;
use BudgetCalculator\Cli\Command\Climate\Budget\DisplayBudgetsCommand;
use BudgetCalculator\Cli\Command\Climate\Transaction\AddTransactionCommand;
use BudgetCalculator\Cli\Command\Climate\Transaction\DeleteTransactionsCommand;
use BudgetCalculator\Cli\Command\Climate\Transaction\DisplayTransactionsCommand;
use BudgetCalculator\Cli\Command\Climate\Transaction\EditTransactionsCommand;
use BudgetCalculator\Cli\Command\Climate\User\DeleteUserCommand;
use BudgetCalculator\Cli\Command\Climate\User\RegisterUserCommand;
use BudgetCalculator\Cli\Security\Climate\ClimateGuard;
use League\CLImate\CLImate;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;
use function DI\factory;

return [
    'app.name' => $_ENV['APP_NAME'],
    CLImate::class => autowire()->lazy(),
    ClimateAdapter::class => autowire()->lazy(),
    Cli::class => factory(static fn (ContainerInterface $container): Cli => $container->get(ClimateAdapter::class)),
    ClimateBudgetCalculator::class => factory(static function (ContainerInterface $container): BudgetCalculator {
        $app = new ClimateBudgetCalculator(
            $container->get(ClimateAdapter::class),
            $container->get(ClimateGuard::class)
        );

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
    }),
    CliMenuBudgetCalculator::class => factory(static function (ContainerInterface $container): BudgetCalculator {
        $app = new CliMenuBudgetCalculator($container->get('app.name'));

        $app->registerMenuBuilder($container->get(RegisterUserMenuBuilder::class));

        return $app;
    }),
    BudgetCalculator::class => get(CliMenuBudgetCalculator::class),
];
