<?php namespace Simpl;

require __DIR__ . '/functions.php';

/**
 * Base PHPSimpl Class used to control Simpl at its highest level
 */
class Simpl extends \Pimple {
    /**
     * Class Constructor
     *
     * @return \Simpl\Simpl
     */
    public function __construct()
    {
        $this->loadConfig();

        $this->startSession();
    }

    /**
     * @param array $app_config
     */
    public function loadConfig($app_config = array()) {
        $config = require __DIR__ . '/config.php';
        foreach (array_merge($config, $app_config) as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function start() {
        $this['db'] = $this->share( function(Simpl $c) {
            $db = new DB($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_default']);
            $db->setDebug($c['debug']);
            $db->setFsCache($c['fs_cache']);
            $db->setDbLog($c['db_log']);
            $db->setQueryLog($c['query_log']);
            $db->DbConnect();
            return $db;
        });

        $this['session'] = $this->share( function(Simpl $c) {
            return new Session($c['db'], $this['db_default']);
        });

        $this['validate'] = $this->share( function(Simpl $c) {
           return new Validate;
        });
    }

    /**
     * @param string $env
     * @return string
     */
    public function detectEnvironment($env = '') {
        return (getenv('PHP_ENV'))?:$env;
    }

    public function startSession()
    {
        // If using DB Sessions
        if ($this['db_sessions'] == true) {
            // Create the DB Session
            $s = $this['session'];

            //Change the save_handler to use the class functions
            session_set_save_handler(
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
    }
}
