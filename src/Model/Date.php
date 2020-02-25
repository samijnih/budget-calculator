<?php

declare(strict_types=1);

namespace App\Model;

use Assert\Assert;
use DateTime;
use DateTimeInterface;

final class Date
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::that(DateTime::createFromFormat('Y-m-d', $value))
            ->isInstanceOf(DateTimeInterface::class);

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
