<?php

declare(strict_types=1);

use BudgetCalculator\Cli\Command\Budget\DefineBudgetCommand;
use BudgetCalculator\Cli\Command\Budget\DeleteBudgetsCommand;
use BudgetCalculator\Cli\Command\Budget\DisplayBudgetsCommand;
use BudgetCalculator\Cli\Command\Transaction\AddTransactionCommand;
use BudgetCalculator\Cli\Command\Transaction\DeleteTransactionsCommand;
use BudgetCalculator\Cli\Command\Transaction\DisplayTransactionsCommand;
use BudgetCalculator\Cli\Command\Transaction\EditTransactionsCommand;
use BudgetCalculator\Cli\Command\User\DeleteUserCommand;
use BudgetCalculator\Cli\Command\User\RegisterUserCommand;
use function DI\autowire;

return [
    RegisterUserCommand::class => autowire()->lazy(),
    DeleteUserCommand::class => autowire()->lazy(),

    AddTransactionCommand::class => autowire()->lazy(),
    DisplayTransactionsCommand::class => autowire()->lazy(),
    EditTransactionsCommand::class => autowire()->lazy(),
    DeleteTransactionsCommand::class => autowire()->lazy(),

    DefineBudgetCommand::class => autowire()->lazy(),
    DisplayBudgetsCommand::class => autowire()->lazy(),
    DeleteBudgetsCommand::class => autowire()->lazy(),
];
