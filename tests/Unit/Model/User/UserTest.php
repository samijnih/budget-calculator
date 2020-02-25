<?php

namespace Tests\Unit\Model\User;

use BudgetCalculator\Model\User\User;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private User $sut;
    private Money $balance;

    protected function setUp(): void
    {
        $this->sut = new User(
            aUuid('4a0efdaa-9471-4c1d-955a-bf6cd931f5b6'),
            'toto@toto.fr',
            'toto',
            $this->balance = Money::EUR(999),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2020-01-01T04:00:00+01:00'),
            DateTime::createFromFormat(DATE_ATOM, '2020-01-02T04:00:00+01:00')
        );
    }

    /** @test */
    public function replaceBalance(): void
    {
        $newBalance = Money::EUR(1199);
        $updatedAt = DateTime::createFromFormat(DATE_ATOM, '2020-03-01T04:00:00+01:00');

        $this->sut->replaceBalance($newBalance, $updatedAt);

        $expected = new User(
            aUuid('4a0efdaa-9471-4c1d-955a-bf6cd931f5b6'),
            'toto@toto.fr',
            'toto',
            Money::EUR(1199),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2020-01-01T04:00:00+01:00'),
            DateTime::createFromFormat(DATE_ATOM, '2020-03-01T04:00:00+01:00')
        );
        static::assertEquals($expected, $this->sut);
    }

    /** @test */
    public function normalize(): void
    {
        $actual = $this->sut->normalize();

        $expected = [
            'id' => '4a0efdaa-9471-4c1d-955a-bf6cd931f5b6',
            'email' => 'toto@toto.fr',
            'password' => 'toto',
            'balance_amount' => $this->balance->getAmount(),
            'balance_currency' => $this->balance->getCurrency()->getCode(),
            'created_at' => '2020-01-01T04:00:00+01:00',
            'updated_at' => '2020-01-02T04:00:00+01:00',
        ];
        static::assertSame($expected, $actual);
    }
}
