<?php

declare(strict_types=1);

namespace App\Model\Budget;

use App\Model\DateFormatter;
use App\Model\Normalizable;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @see \App\Migration\Doctrine\Version20200228235555
 */
final class Budget implements Normalizable
{
    use DateFormatter;

    private UuidInterface $id;
    private UuidInterface $userId;
    private string $name;
    private Money $amount;
    private Priority $priority;
    private DateTimeImmutable $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $userId,
        string $name,
        Money $amount,
        Priority $priority,
        DateTimeImmutable $createdAt,
        ?DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->amount = $amount;
        $this->priority = $priority;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id->toString(),
            'user_id' => $this->userId->toString(),
            'name' => $this->name,
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'priority' => $this->priority->value(),
            'created_at' => $this->formatCreatedAt($this->createdAt),
            'updated_at' => $this->formatUpdatedAt($this->updatedAt),
        ];
    }

    /**
     * @return UuidInterface[]
     */
    public function id(): array
    {
        return ['id' => $this->id, 'user_id' => $this->userId];
    }
}
