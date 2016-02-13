<?php

$environment = getenv('APPLICATION_ENV');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if ($environment == 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
