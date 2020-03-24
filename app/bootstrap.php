<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

require_once __DIR__.'/../vendor/autoload.php';

$_ENV['PROJECT_DIR'] = dirname(__DIR__);

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$errorHandler = Debug::enable();

$containerBuilder = new ContainerBuilder();
$containerBuilder->enableCompilation($_ENV['VAR_DIR'].'/cache');
$containerBuilder->useAnnotations(false);
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions(
    __DIR__.'/cli.php',
    __DIR__.'/commands.php',
    __DIR__.'/common.php',
    __DIR__.'/facades.php',
    __DIR__.'/menu_builders.php',
    __DIR__.'/repositories.php',
    __DIR__.'/security.php',
);

return $containerBuilder->build();
