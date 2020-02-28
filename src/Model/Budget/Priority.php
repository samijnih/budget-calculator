<?php

declare(strict_types=1);

namespace App\Model\Budget;

use Assert\Assert;

final class Priority
{
    public const MIN_VALUE = 1;
    public const MAX_VALUE = 10;

    private int $value;

    public function __construct(int $priority)
    {
        Assert::that($priority)->between(self::MIN_VALUE, self::MAX_VALUE);

        $this->value = $priority;
    }

    public function value(): int
    {
        return $this->value;
    }
}
