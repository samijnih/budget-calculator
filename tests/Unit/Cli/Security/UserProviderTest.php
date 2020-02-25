<?php

namespace Tests\Unit\Cli\Security;

use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\ReadModel\User\User;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UserProviderTest extends TestCase
{
    private UserProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new UserProvider();
    }

    /** @test */
    public function userAccessorFailsWithoutSettingIt(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->getUser();
    }

    /** @test */
    public function userCanBeSet(): void
    {
        $user = new User(
            'f9499efe-d122-4a19-98b7-670727c97405',
            'toto@toto.fr',
            'toto',
            '20000',
            'EUR',
            '2020-01-01 17:00:00',
            null
        );

        $this->sut->setUser($user);

        static::assertSame($user, $this->sut->getUser());
    }
}
