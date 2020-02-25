<?php

namespace Tests\Unit\Model\Transaction;

use BudgetCalculator\Model\Date;
use BudgetCalculator\Model\Transaction\Transaction;
use BudgetCalculator\Model\Transaction\Type;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class TransactionTest extends TestCase
{
    private Transaction $sut;
    private Money $amount;

    protected function setUp(): void
    {
        $this->sut = new Transaction(
            aUuid('4a0efdaa-9471-4c1d-955a-bf6cd931f5b6'),
            aUuid('0dbe1a65-1808-420c-96ee-fdae98af9ee9'),
            'Netflix',
            $this->amount = Money::EUR(999),
            Type::debit(),
            new Date('2020-04-01'),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2020-01-01T04:00:00+01:00'),
            DateTime::createFromFormat(DATE_ATOM, '2020-01-02T04:00:00+01:00'),
        );
    }

    /** @test */
    public function normalize(): void
    {
        $actual = $this->sut->normalize();

        $expected = [
            'id' => '4a0efdaa-9471-4c1d-955a-bf6cd931f5b6',
            'user_id' => '0dbe1a65-1808-420c-96ee-fdae98af9ee9',
            'label' => 'Netflix',
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'type' => 'debit',
            'date' => '2020-04-01',
            'created_at' => '2020-01-01T04:00:00+01:00',
            'updated_at' => '2020-01-02T04:00:00+01:00',
        ];
        static::assertSame($expected, $actual);
    }
}
