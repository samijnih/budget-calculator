<?php

declare(strict_types=1);

namespace App\ReadModel\Transaction;

use Doctrine\DBAL\Connection;

class TransactionRepository
{
    public const TABLE = 'public.transaction';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(string $id): ?Transaction
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

        return Transaction::fromArrayResult($result);
    }

    public function findAllByUser(string $userId): iterable
    {
        $results = $this->connection->fetchAll(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE user_id = :user_id
            ORDER BY date
            SQL, self::TABLE), ['user_id' => $userId]
        );

        $transactions = [];

        foreach ($results as $result) {
            $transactions[] = Transaction::fromArrayResult($result);
        }

        return $transactions;
    }
}
