<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\CliMenu;

use PhpSchool\CliMenu\Builder\CliMenuBuilder;

interface MenuBuilder
{
    public function name(): string;
    public function builder(): CliMenuBuilder;
}
