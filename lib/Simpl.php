<?php

namespace Simpl;

use Simpl\Contracts\SimplContract;

// Must be included after the composer autoloader
include_once(__DIR__ . '/config.php');

/**
 * Base PHPSimpl Class used to control Simpl at its highest level
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Simpl implements SimplContract
{
    /**
     * @var array
     */
    public $settings = array(
        'form' => array(
            'required_indicator' => 'before',
            'label_ending' => ':'
        )
    );

    /**
     * Class Constructor
     *
     * Creates a Simpl Class with nothing in it
     *
     * @return \Simpl\Simpl
     */
    public function __construct()
    {
    }

    /**
     * Does various Actions with the Cache
     *
     * @param string $action
     * @return bool
     */
    public function ClearCache($action)
    {
        switch ($action) {
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

        if (is_array($files)) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        return true;
    }
}
