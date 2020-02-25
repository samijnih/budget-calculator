<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Helper;

use DateTimeImmutable;

trait FormatterHelper
{
    private function formatDate(string $date, string $fromFormat, string $toFormat): string
    {
        return DateTimeImmutable::createFromFormat($fromFormat, $date)->format($toFormat);
    }

    private function replaceInString($search, $replace, $subject): string
    {
        return str_replace($search, $replace, $subject);
    }
}
