<?php

define('DEBUG', true);
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_HOST', '127.0.0.1');
define('DB_DEFAULT', 'phpsimpl');

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Start the app
require __DIR__ . '/../lib/start.php';

var_dump($mySimpl);