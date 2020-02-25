<?php

namespace Tests\Unit\Facade;

use BudgetCalculator\Facade\PasswordFacade;
use PHPUnit\Framework\TestCase;

final class PasswordFacadeTest extends TestCase
{
    /** @test */
    public function verify(): void
    {
        $actual = 'toto';
        $expected = password_hash($actual, PASSWORD_ARGON2ID);

        static::assertTrue(PasswordFacade::verify($actual, $expected));
    }

    /** @test */
    public function hash(): void
    {
        $password = 'toto';

        $actual = PasswordFacade::hash($password);

        static::assertTrue(password_verify($password, $actual));
    }
}
