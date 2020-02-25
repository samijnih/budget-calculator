<?php

declare(strict_types=1);

namespace BudgetCalculator\EntityRepository;

use BudgetCalculator\Helper\MoneyHelper;
use BudgetCalculator\Model\Date;
use BudgetCalculator\Model\Transaction\Transaction;
use BudgetCalculator\Model\Transaction\Type;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * @see Transaction
 */
class TransactionRepository
{
    use MoneyHelper;

    public const TABLE = 'public.transaction';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(UuidInterface $id): Transaction
    {
        $result = $this->connection->fetchAssoc(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE id = :id
            SQL, self::TABLE), ['id' => $id->toString()]
        );

        return new Transaction(
            Uuid::fromString($result['id']),
            Uuid::fromString($result['user_id']),
            $result['label'],
            $this->buildMoney($result['amount'], $result['currency']),
            Type::fromString($result['type']),
            new Date($result['date']),
            new DateTimeImmutable($result['created_at']),
            $result['updated_at'] !== null ? new DateTime($result['updated_at']) : null
        );
    }

    public function create(Transaction $transaction): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->insert(self::TABLE, $transaction->normalize());
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }

    public function update(Transaction $transaction): void
    {
        $this->connection->beginTransaction();

        try {
            $id = $transaction->id();

            $this->connection->update(self::TABLE, $transaction->normalize(), [
                'id' => $id['id']->toString(),
                'user_id' => $id['user_id']->toString(),
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
                'id' => $id->toString(),
            ]);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }

    public function deleteByUser(UuidInterface $userId): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->delete(self::TABLE, [
                'user_id' => $userId->toString(),
            ]);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }
}
