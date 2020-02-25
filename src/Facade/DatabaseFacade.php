<?php

declare(strict_types=1);

namespace BudgetCalculator\Facade;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class DatabaseFacade
{
    private static ?Connection $connection = null;

    public static function getConnectionFromUrl(string $url): Connection
    {
        if (self::$connection === null) {
            self::$connection = DriverManager::getConnection([
                'url' => $url,
            ]);
        }

        return self::$connection;
    }
}
