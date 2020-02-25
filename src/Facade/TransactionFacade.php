<?php

declare(strict_types=1);

namespace App\Facade;

use App\Model\Date;
use App\Model\Transaction\Transaction as TransactionEntity;
use App\Model\Transaction\TransactionRepository;
use App\Model\Transaction\Type;
use App\ReadModel\Transaction\Transaction as TransactionReadModel;
use App\ReadModel\Transaction\TransactionRepository as TransactionReadModelRepository;
use App\Service\Clock;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

class TransactionFacade
{
    private UuidFactory $uuidFactory;
    private TransactionRepository $entityRepository;
    private TransactionReadModelRepository $readModelRepository;
    private Clock $clock;

    public function __construct(
        UuidFactory $uuidFactory,
        TransactionRepository $entityRepository,
        TransactionReadModelRepository $readModelRepository,
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
        string $label,
        Money $amount,
        string $type,
        string $date
    ): void {
        $transaction = new TransactionEntity(
            Uuid::fromString($id),
            Uuid::fromString($userId),
            $label,
            $amount,
            Type::fromString($type),
            new Date($date),
            $this->clock->immutableNow(),
            null
        );

        $this->entityRepository->create($transaction);
    }

    public function delete(string $id): void
    {
        $this->entityRepository->delete(Uuid::fromString($id));
    }

    public function deleteForUser(string $userId): void
    {
        $this->entityRepository->deleteByUser(Uuid::fromString($userId));
    }

    public function find(string $id): TransactionReadModel
    {
        return $this->readModelRepository->find($id);
    }

    public function listForUser(string $userId): iterable
    {
        return $this->readModelRepository->findAllByUser($userId);
    }
}
