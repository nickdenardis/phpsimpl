<?php namespace Simpl;

require __DIR__ . '/functions.php';

/**
 * Base PHPSimpl Class used to control Simpl at its highest level
 */
class Simpl extends \Pimple {
    /**
     * @var registry
     */
    protected static $registry = array();

    /**
     * Class Constructor
     *
     * @return \Simpl\Simpl
     */
    public function __construct()
    {
        $this->loadConfig();
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

    /**
     * Add a new resolver to the registry array.
     * @param  string $name The id
     * @param object|\Simpl\Closure $resolve Closure that creates instance
     * @return void
     */
    public static function register($name, Closure $resolve)
    {
        static::$registry[$name] = $resolve;
    }

    /**
     * Create the instance
     * @param  string $name The id
     * @throws Exception
     * @return mixed
     */
    public static function resolve($name)
    {
        if ( static::registered($name) )
        {
            $name = static::$registry[$name];
            return $name();
        }

        throw new Exception('Nothing registered with that name, fool.');
    }

    /**
     * Determine whether the id is registered
     * @param  string $name The id
     * @return bool Whether to id exists or not
     */
    public static function registered($name)
    {
        return array_key_exists($name, static::$registry);
    }

    /**
     * Does various Actions with the Cache
     *
     * @param string $action
     * @return bool

    public function ClearCache($action){
        switch($action){
            case 'clear':
                $files = glob(FS_CACHE . "*.cache.php");
                break;
            case 'clear_query':
                $files = glob(FS_CACHE . "query_*.cache.php");
                break;
            case 'clear_table':
                $files = glob(FS_CACHE . "table_*.cache.php");
                break;
        }

        if (is_array($files))
            foreach($files as $file)
                unlink($file);

        return true;
    }
     */
}
