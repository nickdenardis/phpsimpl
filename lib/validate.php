<?php namespace Simpl;

/**
 * Base Validate Class
 *
 * Used to validate individual fields in a form
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Validate extends Simpl {
    /**
     * @var array
     */
    protected $types = array('email' => '/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*\@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*\.[a-zA-Z]{2,4}$/',
        'phone' => '/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,3})|(\(?\d{2,3}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/',
        'int' => '/^([0-9])+$/',
        //'unsigned' => '^[0-9]*$',
        'alpha' => '/^([a-zA-Z])+$/',
        'alphanum' => '/^([a-zA-Z0-9])+$/',
        //'float' => '^[0-9]*\\.?[0-9]*$',
        'url' => '/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i');

    /**
     * Validate Constructor
     *
     * Used to setup all the validation types
     *
     * @return \Simpl\Validate
     */
    public function __construct(){
        return true;
    }

    /**
     * Validate a Field
     *
     * @param $type String of the Validation Type
     * @param $value Mixed value that needs to be validated
     * @return bool
     */
    public function Check($type, $value){
        // Check for the type
        if ((string)$value != '' && array_key_exists($type, $this->types))
            return preg_match($this->types[$type], $value);

        return true;
    }

    /**
     * Add Validation Type
     *
     * @param $type String of the Validation Type
     * @param $regex String of the Regular Expression
     * @return bool
     */
    public function AddValidation($type, $regex){
        // Add the Type to the list
        $this->types[$type] = $regex;

        return true;
    }
}
