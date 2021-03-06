<?php

declare(strict_types=1);

use BudgetCalculator\Facade\DatabaseFacade;
use Doctrine\DBAL\Tools\Console\ConsoleRunner;

require_once __DIR__.'/app/bootstrap.php';

$connection = DatabaseFacade::getConnectionFromUrl($_ENV['DB_URL']);
$connection
    ->getDatabasePlatform()
    ->registerDoctrineTypeMapping('transaction_type', 'string');

return ConsoleRunner::createHelperSet($connection);
