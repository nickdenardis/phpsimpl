<?php
return array(
    // Debug everything, set only if you need to know exactly what is going on
    'debug' => false,

    // Debug only raw queries
    'debug_query' => false,

    // Store all the debug info in a debug log file
    'debug_log' => false,

    // Use a file cache to store the data structures (recommended)
    'use_cache' => false,

    // Pull the ENUM data from database as options (only use if your tables have ENUM data)
    'use_enum' => true,

    // Cache query results to a file for faster re-queries
    'query_cache' => false,

    // Store the session data in a table (import the table from the examples)
    'db_sessions' => false,

    // Log all database queries
    'db_log' => false,

    // Pretty sure this is a duplicate
    'query_log' => false,

    // For clearing the cache
    'clear_cache' => false,

    // Cache directory (always use trailing slash)
    'fs_cache' => 'cache/',

    'db_host' => '',
    'db_user' => '',
    'db_pass' => '',
    'db_default' => ''
);

/*
// Override the IP address if in a load balanced environment
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
*/