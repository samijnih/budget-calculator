<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use DateTimeImmutable;

class Clock
{
    public function immutableNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function mutableNow(): DateTime
    {
        return new DateTime();
    }
}
