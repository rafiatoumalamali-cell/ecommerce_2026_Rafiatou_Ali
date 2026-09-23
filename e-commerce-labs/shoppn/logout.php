<?php
require_once __DIR__ . '/core/core.php';
session_destroy();
redirect('index.php');
?>