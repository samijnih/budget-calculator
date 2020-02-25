<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

trait DateFormatter
{
    private function format(DateTimeInterface $dateTime, string $format): string
    {
        return $dateTime->format($format);
    }

    private function formatCreatedAt(DateTimeImmutable $createdAt): string
    {
        return $createdAt->format(DATE_ATOM);
    }

    private function formatUpdatedAt(?DateTime $updatedAt): ?string
    {
        return $updatedAt ? $updatedAt->format(DATE_ATOM) : null;
    }
}
