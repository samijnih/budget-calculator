<?php

declare(strict_types=1);

namespace Tests\Behat;

use App\Model\Date;
use App\Model\Transaction\Transaction;
use App\Model\Transaction\TransactionRepository;
use App\Model\Transaction\Type;
use App\ReadModel\Transaction\Transaction as TransactionReadModel;
use App\ReadModel\Transaction\TransactionRepository as TransactionReadModelRepository;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Behat\Bootstrap\FeatureContext;
use Tests\Fixtures\Fixtures;
use Throwable;

final class TransactionContext extends FeatureContext
{
    private UserContext $userContext;
    private TransactionRepository $entityRepository;
    private TransactionReadModelRepository $readModelRepository;
    private ?Transaction $entity = null;

    public function __construct()
    {
        parent::__construct();

        $this->userContext = new UserContext();
        $this->entityRepository = new TransactionRepository($this->connection);
        $this->readModelRepository = new TransactionReadModelRepository($this->connection);
        $this->fixtures = new Fixtures(TransactionRepository::TABLE, $this->connection);
    }

    /**
     * @Given /^there are users in my system with:$/
     */
    public function thereAreUsersInMySystemWith(TableNode $table): void
    {
        $this->userContext->thereAreUsersInMySystemWith($table);
    }

    /**
     * @Given /^there are transactions in my system with:$/
     */
    public function thereAreTransactionsInMySystemWith(TableNode $table): void
    {
        $row = $this->extractRow($table);

        $this->fixtures
            ->append([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'label' => $row['label'],
                'amount' => $row['amount'],
                'currency' => $row['currency'],
                'type' => $row['type'],
                'date' => $row['date'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'] ?? null
            ])
            ->load()
        ;
    }

    /**
     * @Given /^a new transaction with:$/
     */
    public function aNewTransactionWith(TableNode $table): void
    {
        $row = $this->extractRow($table);

        $this->entity = new Transaction(
            aUuid($row['id']),
            aUuid($row['user_id']),
            $row['label'],
            $this->buildMoney($row['amount'], $row['currency']),
            Type::fromString($row['type']),
            new Date($row['date']),
            new DateTimeImmutable($row['created_at']),
            $row['updated_at'] ?? null
        );
    }

    /**
     * @When /^I call the entity repository to add the transaction$/
     */
    public function iCallTheEntityRepositoryToAddTheTransaction(): void
    {
        try {
            $this->entityRepository->create($this->entity);
        } catch (Throwable $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then /^I get a read model from the read model repository with:$/
     */
    public function iGetAReadModelFromTheReadModelRepositoryWith(TableNode $table): void
    {
        $expected = $this->extractRow($table);

        $actual = $this->readModelRepository->find($expected['id']);

        Assert::assertNull($this->exception);
        Assert::assertNotNull($actual);
        Assert::assertEquals(
            new TransactionReadModel(
                $expected['id'],
                $expected['user_id'],
                $expected['label'],
                $expected['amount'],
                $expected['currency'],
                $expected['type'],
                $expected['date'],
                $expected['created_at'],
                $expected['updated_at']
            ),
            $actual
        );
    }
}
