<?php

declare(strict_types=1);

namespace BudgetCalculator\Facade;

use BudgetCalculator\Model\Budget\Budget as BudgetEntity;
use BudgetCalculator\EntityRepository\BudgetRepository;
use BudgetCalculator\Model\Budget\Priority;
use BudgetCalculator\ReadModel\Budget\Budget as BudgetReadModel;
use BudgetCalculator\ReadModelRepository\BudgetRepository as BudgetReadModelRepository;
use BudgetCalculator\Service\Clock;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

class BudgetFacade
{
    private UuidFactory $uuidFactory;
    private BudgetRepository $entityRepository;
    private BudgetReadModelRepository $readModelRepository;
    private Clock $clock;

    public function __construct(
        UuidFactory $uuidFactory,
        BudgetRepository $entityRepository,
        BudgetReadModelRepository $readModelRepository,
        Clock $clock
    ) {
        $this->uuidFactory = $uuidFactory;
        $this->entityRepository = $entityRepository;
        $this->readModelRepository = $readModelRepository;
        $this->clock = $clock;
    }

    public function generateId(): UuidInterface
    {
        return $this->uuidFactory->uuid4();
    }

    public function add(
        string $id,
        string $userId,
        string $name,
        Money $amount,
        int $priority
    ): void {
        $budget = new BudgetEntity(
            Uuid::fromString($id),
            Uuid::fromString($userId),
            $name,
            $amount,
            new Priority($priority),
            $this->clock->immutableNow(),
            null
        );

        $this->entityRepository->create($budget);
    }

    public function delete(string $id): void
    {
        $this->entityRepository->delete(Uuid::fromString($id));
    }

    public function deleteForUser(string $userId)
    {
        $this->entityRepository->deleteByUser(Uuid::fromString($userId));
    }

    public function deleteMany(array $budgets): void
    {
        $identifiers = array_map(fn (string $budgetId): UuidInterface => Uuid::fromString($budgetId), $budgets);

        $this->entityRepository->deleteMany($identifiers);
    }

    public function find(string $id): BudgetReadModel
    {
        return $this->readModelRepository->find($id);
    }

    public function listForUser(string $userId): iterable
    {
        return $this->readModelRepository->findAllByUser($userId);
    }
}
