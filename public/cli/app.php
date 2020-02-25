#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Cli\Solde;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require_once __DIR__.'/../../app/bootstrap.php';

$app = $container->get(Solde::class);
$app->run();
