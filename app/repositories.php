<?php

declare(strict_types=1);

use BudgetCalculator\EntityRepository\BudgetRepository;
use BudgetCalculator\EntityRepository\TransactionRepository;
use BudgetCalculator\EntityRepository\UserRepository;
use BudgetCalculator\ReadModelRepository\BudgetRepository as BudgetReadModelRepository;
use BudgetCalculator\ReadModelRepository\TransactionRepository as TransactionReadModelRepository;
use BudgetCalculator\ReadModelRepository\UserRepository as UserReadModelRepository;
use function DI\autowire;

return [
    UserReadModelRepository::class => autowire()->lazy(),
    TransactionReadModelRepository::class => autowire()->lazy(),
    BudgetReadModelRepository::class => autowire()->lazy(),

    UserRepository::class => autowire()->lazy(),
    TransactionRepository::class => autowire()->lazy(),
    BudgetRepository::class => autowire()->lazy(),
];
