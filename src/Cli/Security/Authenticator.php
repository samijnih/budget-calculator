<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Security;

use BudgetCalculator\ReadModel\User\User;

interface Authenticator
{
    public function checkCredentials(array $credentials): bool;
    public function getUserByCredentials(array $credentials): ?User;
}
