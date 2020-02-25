<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Doctrine\DBAL\Connection;

final class Fixtures
{
    private string $table;
    private Connection $connection;
    private array $rows = [];

    public function __construct(string $table, Connection $connection)
    {
        $this->table = $table;
        $this->connection = $connection;
    }

    public function append(array $data): self
    {
        $this->rows[] = $data;

        return $this;
    }

    public function load(): void
    {
        foreach ($this->rows as $row) {
            $this->connection->insert($this->table, $row);
        }
    }
}
