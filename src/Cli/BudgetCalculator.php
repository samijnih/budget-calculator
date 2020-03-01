<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli;

interface BudgetCalculator
{
    public function run(): void;
}
