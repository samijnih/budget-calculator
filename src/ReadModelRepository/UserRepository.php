<?php

declare(strict_types=1);

namespace BudgetCalculator\ReadModelRepository;

use BudgetCalculator\ReadModel\User\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    public const TABLE = 'public.user';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(string $id): ?User
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

        return User::fromArrayResult($result);
    }

    public function findByEmail(string $email): ?User
    {
        $result = $this->connection->fetchAssoc(sprintf(<<<SQL
            SELECT *
            FROM %s
            WHERE email = :email
            SQL, self::TABLE), ['email' => $email]
        );

        if ($result === false) {
            return null;
        }

        return User::fromArrayResult($result);
    }
}
