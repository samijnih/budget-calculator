<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command;

interface Command
{
    public function label(): string;
    public function name(): string;
    public function execute();
}
