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

// If using DB Sessions
if ( DB_SESSIONS == true ){
    // Create the DB Session
    $s = new Session((defined('DB_CMS'))?DB_CMS:DB_DEFAULT);

    //Change the save_handler to use the class functions
    session_set_save_handler (
        array(&$s, 'open'),
        array(&$s, 'close'),
        array(&$s, 'read'),
        array(&$s, 'write'),
        array(&$s, 'destroy'),
        array(&$s, 'gc')
    );
}

// Start a session if not already started
if (session_id() == '')
    @session_start();
*/