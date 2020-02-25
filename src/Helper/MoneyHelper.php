<?php

declare(strict_types=1);

namespace App\Helper;

use Money\Currency;
use Money\Money;

trait MoneyHelper
{
    protected function buildMoney(string $amount, string $currency): Money
    {
        return new Money($amount, new Currency($currency));
    }
}
