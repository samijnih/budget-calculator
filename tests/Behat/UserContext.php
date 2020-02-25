<?php

declare(strict_types=1);

namespace Tests\Behat;

use App\Model\User\User;
use App\Model\User\UserRepository;
use App\ReadModel\User\User as UserReadModel;
use App\ReadModel\User\UserRepository as UserReadModelRepository;
use Behat\Gherkin\Node\TableNode;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Behat\Bootstrap\FeatureContext;
use Tests\Fixtures\Fixtures;
use Throwable;

final class UserContext extends FeatureContext
{
    private UserRepository $entityRepository;
    private UserReadModelRepository $readModelRepository;
    private ?User $entity = null;

    public function __construct()
    {
        parent::__construct();

        $this->entityRepository = new UserRepository($this->connection);
        $this->readModelRepository = new UserReadModelRepository($this->connection);
        $this->fixtures = new Fixtures(UserRepository::TABLE, $this->connection);
    }

    /**
     * @Given /^a new user with:$/
     */
    public function aNewUserWith(TableNode $table): void
    {
        $row = $this->extractRow($table);

        $this->entity = new User(
            aUuid($row['id']),
            $row['email'],
            $row['password'],
            $this->buildMoney($row['balance_amount'], $row['balance_currency']),
            new DateTimeImmutable($row['created_at']),
            $row['updated_at'] ?? null
        );
    }

    /**
     * @Given /^there are users in my system with:$/
     */
    public function thereAreUsersInMySystemWith(TableNode $table): void
    {
        $row = $this->extractRow($table);

        $this->fixtures
            ->append([
                'id' => $row['id'],
                'email' => $row['email'],
                'password' => $row['password'],
                'balance_amount' => $row['balance_amount'],
                'balance_currency' => $row['balance_currency'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'] ?? null
            ])
            ->load()
        ;
    }

    /**
     * @Given /^I retrieve a user identified by id (.*)$/
     */
    public function iRetrieveAUserIdentifiedById(string $id): void
    {
        $this->entity = $this->entityRepository->find(aUuid($id));
    }

    /**
     * @Given /^I replace the balance with "([^"]*)" "([^"]*)" at (.*)$/
     */
    public function iReplaceTheBalanceWithAt(string $amount, string $currency, string $updatedAt): void
    {
        $this->entity->replaceBalance($this->buildMoney($amount, $currency), $this->recordNow($updatedAt));
    }

    /**
     * @When /^I call the entity repository to register the user$/
     */
    public function iCallTheEntityRepositoryToRegisterTheUser(): void
    {
        try {
            $this->entityRepository->create($this->entity);
        } catch (Throwable $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When /^I call the entity repository to update the user$/
     */
    public function iCallTheEntityRepositoryToUpdateTheUser(): void
    {
        try {
            $this->entityRepository->update($this->entity);
        } catch (Throwable $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When /^I call the entity repository to remove the user$/
     */
    public function iCallTheEntityRepositoryToRemoveTheUser(): void
    {
        try {
            $this->entityRepository->delete($this->entity->id());
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
            new UserReadModel(
                $expected['id'],
                $expected['email'],
                $expected['password'],
                $expected['balance_amount'],
                $expected['balance_currency'],
                $expected['created_at'],
                $expected['updated_at']
            ),
            $actual
        );
    }

    /**
     * @Then /^I get null from the read model repository with id (.*)$/
     */
    public function iGetNullFromTheReadModelRepositoryWithId(string $id): void
    {
        $actual = $this->readModelRepository->find($id);

        Assert::assertNull($this->exception);
        Assert::assertNull($actual);
    }
}
