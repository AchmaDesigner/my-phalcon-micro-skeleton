<?php

/**
 * based on variable environment APPLICATION_ENV define display errors
 * if production does not display errors
 */
require __DIR__ . '/../display_errors.php';

require __DIR__ . '/../vendor/autoload.php';

use App\MicroBuilder;

/**
 * configure the components to micro application
 */
$app = (new MicroBuilder())
    ->withExceptionHandler()
    ->withNotFoundHandler()
    ->withRoutes()
    ->withServices()
    ->withConnections()
    ->getMicro();

/**
 * boot application
 */
$app->handle();
