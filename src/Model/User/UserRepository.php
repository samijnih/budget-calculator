<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Helper\MoneyHelper;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * @see User
 */
class UserRepository
{
    use MoneyHelper;

    public const TABLE = 'public.user';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(UuidInterface $id): User
    {
        $result = $this->connection->fetchAssoc(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE id = :id
            SQL, self::TABLE), ['id' => $id->toString()]
        );

        return new User(
            Uuid::fromString($result['id']),
            $result['email'],
            $result['password'],
            $this->buildMoney($result['balance_amount'], $result['balance_currency']),
            new DateTimeImmutable($result['created_at']),
            $result['updated_at'] !== null ? new DateTime($result['updated_at']) : null
        );
    }

    public function create(User $user): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->insert(self::TABLE, $user->normalize());
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }

    public function update(User $user): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->update(self::TABLE, $user->normalize(), [
                'id' => $user->id()->toString()
            ]);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }

    public function delete(UuidInterface $id): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->delete(self::TABLE, [
                'id' => $id->toString()
            ]);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }
}
