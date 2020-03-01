<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Output\Question;

use BudgetCalculator\Cli\Output\Input;

final class RadioInput implements Input
{
    private string $name;
    private string $label;
    private array $options;
    private ?string $defaultValue;

    public function __construct(string $name, string $label, array $options, ?string $defaultSelected = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->defaultValue = $defaultSelected;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function defaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function type(): string
    {
        return Input::TYPE_RADIO;
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
