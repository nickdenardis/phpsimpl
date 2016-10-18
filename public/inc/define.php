<?php
$mode = 'dev';

switch ($mode){
	case 'production':
		// Simpl Config
		define('LOGGING', true);
		define('DEBUG', false);
		define('DEBUG_QUERY', false);
		define('DEBUG_LOG', false);
		define('USE_CACHE', true);
		define('USE_ENUM', true);
		define('QUERY_CACHE', true);
		define('DB_SESSIONS', true);
		
		break;
	case 'dev':
	case 'testing':
	default:
		// Simpl Config
		define('LOGGING', true);
		define('DEBUG', false);
		define('DEBUG_QUERY', false);
		define('DEBUG_LOG', true);
		define('USE_CACHE', true);
		define('USE_ENUM', true);
		define('QUERY_CACHE', false);
		define('DB_SESSIONS', false);
		
		break;
}

// Basic Information
define('ADDRESS', 'http://' . $_SERVER['SERVER_NAME'] . '/');
define('TITLE', 'PHPSimpl Blog');

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS', __DIR__ . '/../');
define('DIR_INC', 'inc/');
define('DIR_CSS', 'css/');
define('DIR_MANAGER', 'manager/');

// Simpl Directories
// Change this to where simpl is installed on your server
define('FS_SIMPL', DIR_ABS . '../lib/');
define('FS_CACHE', DIR_ABS . 'cache/');


// Database Connection Options
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_HOST', '127.0.0.1');
define('DB_DEFAULT', 'phpsimpl');
?>