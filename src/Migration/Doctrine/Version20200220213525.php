<?php

declare(strict_types=1);

namespace BudgetCalculator\Migration\Doctrine;

use BudgetCalculator\EntityRepository\TransactionRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200220213525 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create transaction table.';
    }

    public function up(Schema $schema) : void
    {
        $table = TransactionRepository::TABLE;

        $sql = <<<SQL
CREATE TYPE transaction_type AS ENUM ('debit', 'credit')
SQL;

        $this->addSql($sql);

        $sql = <<<SQL
CREATE TABLE $table (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.user (id),
    label VARCHAR NOT NULL,
    amount VARCHAR NOT NULL,
    currency VARCHAR(3) NOT NULL,
    type transaction_type NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT current_timestamp,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NULL
)
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $table = TransactionRepository::TABLE;

        $sql = [
            <<<SQL
DROP TABLE $table
SQL,
            <<<SQL
DROP TYPE transaction_type
SQL
            ];

        foreach ($sql as $s) {
            $this->addSql($s);
        }
    }
}
