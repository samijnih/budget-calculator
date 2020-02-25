<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\DateFormatter;
use App\Model\Normalizable;
use Assert\Assert;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @see \App\Migration\Doctrine\Version20200220213051
 */
final class User implements Normalizable
{
    use DateFormatter;

    private UuidInterface $id;
    private string $email;
    private string $password;
    private Money $balance;
    private DateTimeImmutable $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        UuidInterface $id,
        string $email,
        string $password,
        Money $balance,
        DateTimeImmutable $createdAt,
        ?DateTime $updatedAt
    ) {
        Assert::that($email)->email();
        Assert::that($password)->notBlank()->minLength(3);

        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->balance = $balance;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function replaceBalance(Money $balance, DateTime $updatedAt): void
    {
        $this->balance = $balance;
        $this->updatedAt = $updatedAt;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id->toString(),
            'email' => $this->email,
            'password' => $this->password,
            'balance_amount' => $this->balance->getAmount(),
            'balance_currency' => $this->balance->getCurrency()->getCode(),
            'created_at' => $this->formatCreatedAt($this->createdAt),
            'updated_at' => $this->formatUpdatedAt($this->updatedAt),
        ];
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }
}
