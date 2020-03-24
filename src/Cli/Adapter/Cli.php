<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Adapter;

use BudgetCalculator\Cli\Output\Input;

interface Cli
{
    public function prompt(Input $input);
    public function lineBreak(int $number = 1): void;
    public function tab(int $number): void;
    public function confirm(string $text): bool;
    public function outputError(string $error): void;
    public function outputInfo(string $text): void;
    public function output(string $text, ?string $color = null): void;
    public function table(array $data): void;
    public function clear(): void;
    public function getWrappedCli(): object;
}
