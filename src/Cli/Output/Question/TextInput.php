<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Output\Question;

use BudgetCalculator\Cli\Output\Input;

class TextInput implements Input
{
    protected string $name;
    protected string $label;
    protected array $accept;
    protected ?string $defaultValue;

    public function __construct(
        string $name,
        string $label,
        array $accept = [],
        ?string $defaultValue = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->accept = $accept;
        $this->defaultValue = $defaultValue;
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
        return Input::TYPE_TEXT;
    }

    public function choices(): array
    {
        return [];
    }

    public function accept(): array
    {
        return $this->accept;
    }
}
