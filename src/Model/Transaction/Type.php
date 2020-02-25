<?php

declare(strict_types=1);

namespace App\Model\Transaction;

use Assert\Assert;

final class Type
{
    private const DEBIT = 'debit';
    private const CREDIT = 'credit';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function debit(): self
    {
        return new self(self::DEBIT);
    }

    public static function credit(): self
    {
        return new self(self::CREDIT);
    }

    public static function fromString($type): self
    {
        Assert::that($type)->inArray([self::CREDIT, self::DEBIT]);

        return new self($type);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
