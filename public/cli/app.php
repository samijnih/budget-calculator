#!/usr/bin/env php
<?php

declare(strict_types=1);

use BudgetCalculator\Cli\CliBudgetCalculator;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require_once __DIR__.'/../../app/bootstrap.php';

$app = $container->get(CliBudgetCalculator::class);
$app->run();
