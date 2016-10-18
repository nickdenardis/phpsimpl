<?php
// Global Defines
include_once(__DIR__ . '/define.php');

// Composer autoload
require __DIR__ . '/../../vendor/autoload.php';

// Start the app
require __DIR__ . '/../../lib/start.php';

// Custom Functions and Classes
include_once(DIR_ABS . DIR_INC . 'functions.php');
include_once(DIR_ABS . DIR_INC . 'classes.php');

// Make the DB Connection
$db->Connect();
