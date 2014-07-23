<?php

// Create PHPSimpl
$mySimpl = new Simpl\Simpl;

// Create the DB
$db = new Simpl\DB;

// Create the Validator
$validator = new Simpl\Validate;

$reflectionPool = new Auryn\ReflectionPool;
$app = new Auryn\Provider($reflectionPool);

$app->share($mySimpl);
$app->share($db);
$app->share($validator);


// If using DB Sessions
if ( DB_SESSIONS == true ){
    /** @var Simpl\Session $session */
    $session = $app->make('Simpl\Session');

    //Change the save_handler to use the class functions
    session_set_save_handler (
        array(&$session, 'open'),
        array(&$session, 'close'),
        array(&$session, 'read'),
        array(&$session, 'write'),
        array(&$session, 'destroy'),
        array(&$session, (DB_SESSIONS_GC)?'gc':'gc_defer')
    );
}

// Start a session if not already started
if (session_id() == '')
    @session_start();