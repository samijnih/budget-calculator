<?php

declare(strict_types=1);

namespace BudgetCalculator\Facade;

use Assert\Assert;
use BudgetCalculator\Model\User\User as UserEntity;
use BudgetCalculator\EntityRepository\UserRepository;
use BudgetCalculator\ReadModel\User\User;
use BudgetCalculator\ReadModel\User\User as UserReadModel;
use BudgetCalculator\ReadModelRepository\UserRepository as UserReadModelRepository;
use BudgetCalculator\Cli\Security\Authenticator;
use BudgetCalculator\Service\Clock;
use DomainException;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

class UserFacade implements Authenticator
{
    private UuidFactory $uuidFactory;
    private UserRepository $entityRepository;
    private UserReadModelRepository $readModelRepository;
    private Clock $clock;

    public function __construct(
        UuidFactory $uuidFactory,
        UserRepository $entityRepository,
        UserReadModelRepository $readModelRepository,
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

    public function register(
        string $id,
        string $email,
        string $password,
        Money $balance
    ): void {
        $user = $this->readModelRepository->findByEmail($email);

        if (null !== $user) {
            throw new DomainException("User already exists with $email.");
        }

        $user = new UserEntity(
            Uuid::fromString($id),
            $email,
            PasswordFacade::hash($password),
            $balance,
            $this->clock->immutableNow(),
            null
        );

        $this->entityRepository->create($user);
    }

    public function delete(string $id): void
    {
        $this->entityRepository->delete(Uuid::fromString($id));
    }

    public function find(string $id): UserReadModel
    {
        return $this->readModelRepository->find($id);
    }

    public function checkCredentials(array $credentials): bool
    {
        Assert::that($credentials)
            ->notEmptyKey('email')
            ->notEmptyKey('password');

        ['email' => $email, 'password' => $password] = $credentials;

        $user = $this->readModelRepository->findByEmail($email);

        if (null === $user) {
            return false;
        }

        return PasswordFacade::verify($password, $user->password());
    }

    public function getUserByCredentials(array $credentials): ?UserReadModel
    {
        Assert::that($credentials)
            ->notEmptyKey('email');

        ['email' => $email] = $credentials;

        return $this->readModelRepository->findByEmail($email);
    }
}
