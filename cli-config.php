<?php

declare(strict_types=1);

use App\Facade\DatabaseFacade;
use Doctrine\DBAL\Tools\Console\ConsoleRunner;

$connection = DatabaseFacade::getConnectionFromUrl($_ENV['DB_URL']);
$connection
    ->getDatabasePlatform()
    ->registerDoctrineTypeMapping('transaction_type', 'string');

return ConsoleRunner::createHelperSet($connection);
