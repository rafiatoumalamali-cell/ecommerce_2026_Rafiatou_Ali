<?php

$env = is_file(__DIR__ . "/.env")
	? parse_ini_file(__DIR__ . "/.env", false, INI_SCANNER_RAW)
	: [];

$host = $env["DB_HOST"] ?? "localhost";
$db_user = $env["DB_USER"] ?? "root";
$db_pass = $env["DB_PASS"] ?? "";
$db_name = $env["DB_NAME"] ?? "tasks_app";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}