<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'],
    'migrations_namespace' => 'BudgetCalculator\\Migration\\Doctrine',
    'table_name' => 'doctrine_migration_versions',
    'column_name' => 'version',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory' => $_ENV['SRC_DIR'].'/Migration/Doctrine',
    'all_or_nothing' => true,
    'check_database_platform' => false,
];
