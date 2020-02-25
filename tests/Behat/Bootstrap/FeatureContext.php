<?php

declare(strict_types=1);

namespace Tests\Behat\Bootstrap;

use App\Facade\DatabaseFacade;
use App\Helper\MoneyHelper;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\Fixtures\Fixtures;
use Throwable;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    use MoneyHelper;

    protected Connection $connection;

    protected Fixtures $fixtures;
    protected ?TableNode $table = null;
    protected ?Throwable $exception = null;
    protected ?DateTime $updatedAt = null;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->connection = DatabaseFacade::getConnectionFromUrl($_ENV['DB_URL_TEST']);
    }

    /**
     * @Given /^a fresh database$/
     */
    public function aFreshDatabase(): void
    {
        $this->connection->executeUpdate(<<<SQL
            DO $$ DECLARE
                r RECORD;
            BEGIN
                FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = current_schema()) LOOP
                    EXECUTE 'DROP TABLE IF EXISTS ' || quote_ident(r.tablename) || ' CASCADE';
                END LOOP;
            END $$;
        SQL);
        $this->connection->executeUpdate(<<<SQL
            DROP TYPE IF EXISTS transaction_type;
        SQL);
    }

    /**
     * @Given /^all the migrations are played$/
     */
    public function allTheMigrationsArePlayed(): void
    {
        $input = new StringInput('');
        $input->setInteractive(false);

        $command = new MigrateCommand();
        $command->setConnection($this->connection);
        $command->setHelperSet(
            new HelperSet([
                'db' => new ConnectionHelper($this->connection),
                'question' => new SymfonyQuestionHelper(),
            ])
        );
        $command->run($input, new NullOutput());
    }

    protected function extractRow(TableNode $table): array
    {
        return current($table->getHash());
    }

    protected function recordNow(?string $occurred = null): DateTime
    {
        $this->updatedAt = $occurred ? new DateTime($occurred) : new DateTime();

        return $this->updatedAt;
    }
}
