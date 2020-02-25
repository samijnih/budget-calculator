<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

function aUuid(string $uuid): UuidInterface
{
    return Uuid::fromString($uuid);
}
