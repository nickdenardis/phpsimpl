<?php
// Debug everything, set only if you need to know exactly what is going on
if (!defined('DEBUG')) define('DEBUG', false);
// Debug only raw queies
if (!defined('DEBUG_QUERY')) define('DEBUG_QUERY', false);
// Store all the debug info in a debug log file
if (!defined('DEBUG_LOG')) define('DEBUG_LOG', false);
// Use a file cache to store the data structures (recommended)
if (!defined('USE_CACHE')) define('USE_CACHE', true);
// Pull the ENUM data from database as options (only use if your tables have ENUM data)
if (!defined('USE_ENUM')) define('USE_ENUM', false);
// Cache query results to a file for faster re-queries
if (!defined('QUERY_CACHE')) define('QUERY_CACHE', false);
// Store the session data in a table (import the table from the examples)
if (!defined('DB_SESSIONS')) define('DB_SESSIONS', false);
// Log all database queries
if (!defined('DB_LOG')) define('DB_LOG', false);
// For clearing the cache
if (!defined('CLEAR_CACHE')) define('CLEAR_CACHE', false);

// Table Stripes
if (!defined('SIMPL_TABLE_STRIPES')) define('SIMPL_TABLE_STRIPES', true);

// Where things are sitting
// Always Include trailing slash "/" in Direcories
if (!defined('DIR_ABS')) define('DIR_ABS','./');
if (!defined('WS_SIMPL')) define('WS_SIMPL','simpl/');
if (!defined('WS_SIMPL_IMAGE')) define('WS_SIMPL_IMAGE','img/');
if (!defined('WS_SIMPL_INC')) define('WS_SIMPL_INC','inc/');
if (!defined('WS_SIMPL_CSS')) define('WS_SIMPL_CSS','css/');
if (!defined('WS_SIMPL_JS')) define('WS_SIMPL_JS','js/');
if (!defined('WS_CACHE')) define('WS_CACHE','cache/');
if (!defined('FS_SIMPL')) define('FS_SIMPL',DIR_ABS . WS_SIMPL);
if (!defined('FS_CACHE')) define('FS_CACHE',FS_SIMPL . WS_CACHE);

// Database Connection Option
if (!defined('DB_USER')) define('DB_USER',DBUSER);
if (!defined('DB_HOST')) define('DB_HOST',DBHOST);
if (!defined('DB_PASS')) define('DB_PASS',DBPASS);
if (!defined('DB_DEFAULT')) define('DB_DEFAULT', NULL);
?>