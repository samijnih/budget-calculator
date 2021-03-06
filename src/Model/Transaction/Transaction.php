<?php

declare(strict_types=1);

namespace BudgetCalculator\Model\Transaction;

use BudgetCalculator\Model\Date;
use BudgetCalculator\Model\DateFormatter;
use BudgetCalculator\Model\Normalizable;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @see \BudgetCalculator\Migration\Doctrine\Version20200220213525
 */
final class Transaction implements Normalizable
{
    use DateFormatter;

    private UuidInterface $id;
    private UuidInterface $userId;
    private string $label;
    private Money $amount;
    private Type $type;
    private Date $date;
    private DateTimeImmutable $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $userId,
        string $label,
        Money $amount,
        Type $type,
        Date $date,
        DateTimeImmutable $createdAt,
        ?DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->label = $label;
        $this->amount = $amount;
        $this->type = $type;
        $this->date = $date;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id->toString(),
            'user_id' => $this->userId->toString(),
            'label' => $this->label,
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'type' => $this->type->toString(),
            'date' => $this->date->toString(),
            'created_at' => $this->formatCreatedAt($this->createdAt),
            'updated_at' => $this->formatUpdatedAt($this->updatedAt),
        ];
    }

    public function labeled(string $label, DateTime $updatedAt): void
    {
        $this->label = $label;
        $this->updatedAt = $updatedAt;
    }

    public function ofAmount(Money $amount, DateTime $updatedAt): void
    {
        $this->amount = $amount;
        $this->updatedAt = $updatedAt;
    }

    public function ofType(Type $type, DateTime $updatedAt): void
    {
        $this->type = $type;
        $this->updatedAt = $updatedAt;
    }

    public function onDate(Date $date, DateTime $updatedAt): void
    {
        $this->date = $date;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return UuidInterface[]
     */
    public function id(): array
    {
        return ['id' => $this->id, 'user_id' => $this->userId];
    }
}
