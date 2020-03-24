<?php

declare(strict_types=1);

use BudgetCalculator\Cli\Security\Authenticator;
use BudgetCalculator\Cli\Security\Climate\ClimateGuard;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\UserFacade;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\factory;

return [
    ClimateGuard::class => autowire()->lazy(),
    UserProvider::class => autowire()->lazy(),
    Authenticator::class => factory(static fn (ContainerInterface $container) => $container->get(UserFacade::class)),
];
