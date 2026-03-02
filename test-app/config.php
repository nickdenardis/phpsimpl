<?php
/**
 * Test Application Configuration
 * 
 * This file demonstrates how to configure PHPSimpl for a simple application
 * NOTE: This must be loaded BEFORE any HTML output for session_start() to work
 */

// Database credentials (matching docker-compose.yml)
// Detect if running in Docker (check if 'mariadb' hostname resolves)
$inDocker = @gethostbyname('mariadb') !== 'mariadb';

// The framework uses both DBHOST and DB_HOST (see lib/config.php)
define('DBHOST', $inDocker ? 'mariadb' : '127.0.0.1:3307');
define('DBUSER', 'test_user');
define('DBPASS', 'test_pass');
define('DB_DEFAULT', 'phpsimpl_test');

// Also define the DB_* versions that the DB class uses directly
define('DB_HOST', DBHOST);
define('DB_USER', DBUSER);
define('DB_PASS', DBPASS);

// Application paths
define('DIR_ABS', __DIR__ . '/');

// Debug settings
define('DEBUG', false);
define('DEBUG_QUERY', false);
define('DEBUG_LOG', false);

// Cache settings
define('USE_CACHE', false);
define('QUERY_CACHE', false);
define('DB_LOG', false);

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Start session (must be before any output)
session_start();
