<?php

declare(strict_types=1);

namespace BudgetCalculator\EntityRepository;

use Assert\Assert;
use BudgetCalculator\Helper\MoneyHelper;
use BudgetCalculator\Model\Budget\Budget;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * @see Budget
 */
class BudgetRepository
{
    use MoneyHelper;

    public const TABLE = 'public.budget';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(UuidInterface $id): Budget
    {
        $result = $this->connection->fetchAssoc(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE id = :id
            SQL, self::TABLE), ['id' => $id->toString()]
        );

        return new Budget(
            Uuid::fromString($result['id']),
            Uuid::fromString($result['user_id']),
            $result['name'],
            $this->buildMoney($result['amount'], $result['currency']),
            new DateTimeImmutable($result['created_at']),
            $result['updated_at'] !== null ? new DateTime($result['updated_at']) : null
        );
    }

    public function create(Budget $budget): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->insert(self::TABLE, $budget->normalize());
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }

    public function update(Budget $budget): void
    {
        $this->connection->beginTransaction();

        try {
            $id = $budget->id();

            $this->connection->update(self::TABLE, $budget->normalize(), [
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

    public function deleteMany(array $identifiers): void
    {
        Assert::that($identifiers)->all()->isInstanceOf(UuidInterface::class);

        $this->connection->beginTransaction();

        try {
            foreach ($identifiers as $id) {
                $this->connection->delete(
                    self::TABLE, [
                    'id' => $id->toString(),
                ]);
            }
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }
    }
}
