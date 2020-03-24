<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Output\Question;

use BudgetCalculator\Cli\Output\Input;
use LogicException;

final class PasswordInput extends TextInput
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label, [],null);
    }

    public function defaultValue(): ?string
    {
        throw new LogicException('Password input cannot have default value.');
    }

    public function type(): string
    {
        return Input::TYPE_PASSWORD;
    }
}
