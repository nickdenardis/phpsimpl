<?php namespace Simpl;

// Include the Config
include_once(__DIR__ . '/config.php');
include_once(__DIR__ . '/functions.php');

/**
 * Base PHPSimpl Class used to control Simpl at its highest level
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Simpl {
    /**
     * @var array
     */
    public $settings = array('form' => array(
        'required_indicator' => 'before',
        'label_ending' => ':'
    ));

    /**
     * Class Constructor
     *
     * Creates a Simpl Class with nothing in it
     *
     * @return \Simpl\Simpl
     */
    public function __construct(){
    }
}