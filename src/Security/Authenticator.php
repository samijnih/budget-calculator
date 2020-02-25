<?php

declare(strict_types=1);

namespace App\Security;

use App\ReadModel\User\User;

interface Authenticator
{
    public function checkCredentials(array $credentials): bool;
    public function getUser(array $credentials): ?User;
}
