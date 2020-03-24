<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Output\Question;

use BudgetCalculator\Cli\Output\Input;

final class CheckboxesInput implements Input
{
    private string $name;
    private string $label;
    private array $options;
    private ?array $defaultValues;

    public function __construct(string $name, string $label, array $options, ?array $defaultSelected = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->defaultValues = $defaultSelected;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function defaultValue(): ?array
    {
        return $this->defaultValues;
    }

    public function type(): string
    {
        return Input::TYPE_CHECKBOXES;
    }

    public function choices(): array
    {
        return $this->options;
    }

    public function accept(): array
    {
        return [];
    }
}
