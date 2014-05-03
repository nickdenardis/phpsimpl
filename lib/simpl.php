<?php
// Override the IP address if in a load balanced environment
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

// Include the Config
if (defined('FS_SIMPL'))
	include_once(FS_SIMPL . 'config.php');
else
	include_once(DIR_ABS . 'simpl/config.php');

// Include the functions
include_once(FS_SIMPL . 'functions.php');

// Include the Simpl Loader class
include_once(FS_SIMPL . 'main.php');

// Load the Base Classes
$mySimpl = new Simpl;
$myValidator = new Validate;

// If using DB Sessions
if (DB_SESSIONS == true){
	// Create the DB Sesssion
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
}else{
	// Start a session if not already started
	if (session_id() == '')
		@session_start();
}
?>
