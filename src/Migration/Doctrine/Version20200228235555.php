<?php

declare(strict_types=1);

namespace App\Migration\Doctrine;

use BudgetCalculator\EntityRepository\BudgetRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200228235555 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create budget table.';
    }

    public function up(Schema $schema) : void
    {
        $table = BudgetRepository::TABLE;

        $sql = <<<SQL
CREATE TABLE $table (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.user (id),
    name VARCHAR NOT NULL,
    amount VARCHAR NOT NULL,
    currency VARCHAR(3) NOT NULL,
    priority SMALLINT NOT NULL CHECK (priority > 0),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT current_timestamp,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NULL
)
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $table = BudgetRepository::TABLE;

        $sql = <<<SQL
DROP TABLE $table
SQL;

        $this->addSql($sql);
    }
}
