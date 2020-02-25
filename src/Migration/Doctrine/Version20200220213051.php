<?php

declare(strict_types=1);

namespace BudgetCalculator\Migration\Doctrine;

use BudgetCalculator\EntityRepository\UserRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200220213051 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create user table.';
    }

    public function up(Schema $schema) : void
    {
        $table = UserRepository::TABLE;

        $sql = <<<SQL
CREATE TABLE $table (
    id UUID PRIMARY KEY,
    email VARCHAR NOT NULL,
    password VARCHAR NOT NULL,
    balance_amount VARCHAR NOT NULL,
    balance_currency VARCHAR(3) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT current_timestamp,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NULL
)
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $table = UserRepository::TABLE;

        $sql = <<<SQL
DROP TABLE $table
SQL;

        $this->addSql($sql);
    }
}
