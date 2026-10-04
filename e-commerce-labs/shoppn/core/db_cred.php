<?php

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die('.env file not found.');
}

$env = parse_ini_file($envFile, true, INI_SCANNER_TYPED);

if ($env === false) {
    die('Unable to parse .env file.');
}

define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? 'shoppn');
