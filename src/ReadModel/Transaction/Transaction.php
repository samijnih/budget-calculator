<?php

declare(strict_types=1);

namespace App\ReadModel\Transaction;

use App\Helper\MoneyHelper;
use Assert\Assert;
use JsonSerializable;
use Money\Money;

final class Transaction implements JsonSerializable
{
    use MoneyHelper;

    private string $id;
    private string $userId;
    private string $label;
    private Money $amount;
    private string $type;
    private string $date;
    private string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $userId,
        string $label,
        string $amount,
        string $currency,
        string $type,
        string $date,
        string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->label = $label;
        $this->amount = $this->buildMoney($amount, $currency);
        $this->type = $type;
        $this->date = $date;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArrayResult(array $result): self
    {
        Assert::that($result)
            ->isArray()
            ->keyExists('id')
            ->keyExists('user_id')
            ->keyExists('label')
            ->keyExists('amount')->keyExists('currency')
            ->keyExists('type')
            ->keyExists('date')
            ->keyExists('created_at')
            ->keyExists('updated_at')
        ;

        return new self(
            $result['id'],
            $result['user_id'],
            $result['label'],
            $result['amount'],
            $result['currency'],
            $result['type'],
            $result['date'],
            $result['created_at'],
            $result['updated_at'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'label' => $this->label,
            'amount' => $this->amount,
            'type' => $this->type,
            'date' => $this->date,
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

    public function label(): string
    {
        return $this->label;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function date(): string
    {
        return $this->date;
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
