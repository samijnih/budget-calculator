<?php

declare(strict_types=1);

namespace App\ReadModel\Budget;

use App\Helper\MoneyHelper;
use Assert\Assert;
use JsonSerializable;
use Money\Money;

final class Budget implements JsonSerializable
{
    use MoneyHelper;

    private string $id;
    private string $userId;
    private string $name;
    private Money $amount;
    private int $priority;
    private string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $userId,
        string $name,
        string $amount,
        string $currency,
        int $priority,
        string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->amount = $this->buildMoney($amount, $currency);
        $this->priority = $priority;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArrayResult(array $result): self
    {
        Assert::that($result)
            ->isArray()
            ->keyExists('id')
            ->keyExists('user_id')
            ->keyExists('name')
            ->keyExists('amount')->keyExists('currency')
            ->keyExists('priority')
            ->keyExists('created_at')
            ->keyExists('updated_at')
        ;

        return new self(
            $result['id'],
            $result['user_id'],
            $result['name'],
            $result['amount'],
            $result['currency'],
            $result['priority'],
            $result['created_at'],
            $result['updated_at'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'amount' => $this->amount,
            'priority' => $this->priority,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function priority(): int
    {
        return $this->priority;
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
