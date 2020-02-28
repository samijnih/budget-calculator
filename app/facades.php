<?php

declare(strict_types=1);

use BudgetCalculator\Facade\BudgetFacade;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Facade\UserFacade;
use function DI\autowire;

return [
    UserFacade::class => autowire(),
    TransactionFacade::class => autowire(),
    BudgetFacade::class => autowire(),
];
