<?php

declare(strict_types=1);

use BudgetCalculator\Cli\CliMenu\RegisterUserMenuBuilder;
use function DI\autowire;

return [
    RegisterUserMenuBuilder::class => autowire()->lazy(),
];
