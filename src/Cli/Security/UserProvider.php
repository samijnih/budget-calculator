<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Security;

use BudgetCalculator\ReadModel\User\User;
use RuntimeException;

class UserProvider
{
    private ?User $user = null;

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        if (null === $this->user) {
            throw new RuntimeException('Cannot access user read model because it is not set.');
        }

        return $this->user;
    }
}
