<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Output;

interface Input
{
    public const TYPE_TEXT = 'text';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_RADIO = 'radio';
    public const TYPE_CHECKBOXES = 'checkboxes';

    public function name(): string;
    public function label(): string;
    public function type(): string;
    public function choices(): array;
    public function accept(): array;
    public function defaultValue();
}
