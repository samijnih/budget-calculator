<?php

declare(strict_types=1);

namespace App\Facade;

abstract class PasswordFacade
{
    private const ALGORITHM = PASSWORD_ARGON2I;

    public static function hash(string $password): string
    {
        return password_hash($password, self::ALGORITHM);
    }

    public static function verify(string $actual, string $expected): bool
    {
        return password_verify($actual, $expected);
    }
}
