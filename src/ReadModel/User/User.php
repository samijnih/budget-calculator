<?php

declare(strict_types=1);

namespace BudgetCalculator\ReadModel\User;

use Assert\Assert;
use BudgetCalculator\Helper\MoneyHelper;
use JsonSerializable;
use Money\Money;

final class User implements JsonSerializable
{
    use MoneyHelper;

    private string $id;
    private string $email;
    private string $password;
    private Money $balance;
    private string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $email,
        string $password,
        string $balanceAmount,
        string $balanceCurrency,
        string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->balance = $this->buildMoney($balanceAmount, $balanceCurrency);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArrayResult(array $result): self
    {
        Assert::that($result)
            ->isArray()
            ->keyExists('id')
            ->keyExists('email')->keyExists('password')
            ->keyExists('balance_amount')->keyExists('balance_currency')
            ->keyExists('created_at')
            ->keyExists('updated_at')
        ;

        return new self(
            $result['id'],
            $result['email'],
            $result['password'],
            $result['balance_amount'],
            $result['balance_currency'],
            $result['created_at'],
            $result['updated_at'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'balance_amount' => $this->balance->getAmount(),
            'balance_currency' => $this->balance->getCurrency()->getCode(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
