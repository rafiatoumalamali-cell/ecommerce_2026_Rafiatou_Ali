<?php

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die('.env file not found.');
}

$env = parse_ini_file($envFile);

define('DB_HOST', $env['DB_HOST']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_NAME', $env['DB_NAME']);

?>
