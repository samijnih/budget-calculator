<?php

declare(strict_types=1);

namespace BudgetCalculator\ReadModelRepository;

use BudgetCalculator\ReadModel\Budget\Budget;
use Doctrine\DBAL\Connection;

class BudgetRepository
{
    public const TABLE = 'public.budget';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(string $id): ?Budget
    {
        $result = $this->connection->fetchAssoc(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE id = :id
            SQL, self::TABLE), ['id' => $id]
        );

        if ($result === false) {
            return null;
        }

        return Budget::fromArrayResult($result);
    }

    public function findAllByUser(string $userId): iterable
    {
        $results = $this->connection->fetchAll(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE user_id = :user_id
            ORDER BY priority
            SQL, self::TABLE), ['user_id' => $userId]
        );

        $budgets = [];

        foreach ($results as $result) {
            $budgets[] = Budget::fromArrayResult($result);
        }

        return $budgets;
    }
}
