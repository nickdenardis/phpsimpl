<?php
	// Global Defines
	include_once(__DIR__ . '/inc/define.php');
	
	// Simpl Framework
	include_once(FS_SIMPL . 'simpl.php');
	
	// Custom Functions and Classes
	include_once(DIR_ABS . DIR_INC . 'functions.php');
	include_once(DIR_ABS . DIR_INC . 'classes.php');
	
	// Make the DB Connection
	$db = new DB;
	$db->Connect();
?>