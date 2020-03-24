<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Security;

interface Guard
{
    public function authenticate(): void;
}
