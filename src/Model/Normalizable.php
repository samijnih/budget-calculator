<?php

declare(strict_types=1);

namespace BudgetCalculator\Model;

interface Normalizable
{
    public function normalize(): array;
}
