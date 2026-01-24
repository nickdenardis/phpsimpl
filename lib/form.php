<?php namespace Simpl;

/**
 * Base Form Class
 *
 * Used to create xhtml and validate forms
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Form {
    /**
     * @var array
     */
    protected $fields = array();
    /**
     * @var array
     */
    protected $display = array();
    /**
     * @var string
     */
    protected $prefix;




    /**
     * Class Constructor
     *
     * Creates a Form Class with all the information to use the Form functions
     *
     * @param $data An Array of all the values for the fields
     * @param array|\Simpl\An $required An Array of all the required keys for the form
     * @param array|\Simpl\An $labels An Array of all the custom labels for the form
     * @param array|\Simpl\An $examples An Array of all the exmples for each form element
     * @return \Simpl\Form
     */
    public function __construct($data, $required=array(), $labels=array(), $examples=array()){
        // Loop through all the data
        foreach ($data as $key=>$data){
            // Set all the field info
            $tmpField = new Field(new Validate);
            $tmpField->Set('name', $key);
            $tmpField->Set('value', $data);
            $tmpField->Set('required', (isset($required[$key]) ? $required[$key] : false));
            $tmpField->Set('label', (isset($labels[$key]) ? $labels[$key] : ''));
            $tmpField->Set('example', (isset($examples[$key]) ? $examples[$key] : ''));
            $tmpField->Set('display', (count($this->fields)+1));

            // Add the field to the list
            $this->fields[$key] = $tmpField;
        }

        // Set the local display
        $this->display = $this->GetFields();
    }

    /**
     * Validate the Form
     *
     * @return bool
     */
    public function Validate(){
        $valid = true;

        // Loop through the fields
        foreach ($this->fields as $name=>$field){
            // Validate the Field
            if (!$field->Validate() && $valid)
                $valid = false;
        }

        return $valid;
    }

    /**
     * Check the Data from the class
     *
     * @return array
     */
    public function CheckRequired(){
        // Validate the Form
        $this->Validate();

        // Return the error
        return $this->GetErrors();
    }

    /**
     * Get Field Property
     *
     * @param $property string
     * @param $field string
     * @return mixed
     */
    public function Get($property, $field){
        // Return the field property
        if ($this->IsField($field))
            return $this->fields[$field]->Get($property);

        return '';
    }

    /**
     * Set Field Property
     *
     * Set a specific property about a field
     *
     * @param $property string
     * @param $field string
     * @param $value mixed
     * @return bool
     */
    public function Set($property, $field, $value){
        // Set the fields property
        if ($this->IsField($field))
            return $this->fields[$field]->Set($property, $value);

        return false;
    }

    /**
     * Get Value
     *
     * @param $field string
     * @return mixed
     */
    public function GetValue($field){
        // Get the value of the field
        return $this->Get('value', $field);
    }

    /**
     * Set Value
     *
     * @param $field string
     * @param $value mixed
     * @return bool
     */
    public function SetValue($field, $value){
        // Set the value of the field
        return $this->Set('value', $field, $value);
    }

    /**
     * Get Error
     *
     * @param $field string
     * @return mixed
     */
    public function GetError($field){
        // Get the error of the field
        return $this->Get('error', $field);
    }

    /**
     * Set Error
     *
     * @param $field string
     * @param $value mixed
     * @return bool
     */
    public function SetError($field, $value){
        // Set the error of the field
        $str = (is_array($value))?implode('<br />', $value):$value;

        return $this->Set('error', $field, $str);
    }

    /**
     * Is Error
     *
     * @return bool
     */
    public function IsError(){
        // Loop through the fields
        foreach ($this->fields as $name=>$field){
            // Check for Error
            if ($field->Get('error') != '')
                return true;
        }

        return false;
    }

    /**
     * Get Errors
     *
     * Get a list of all the errors in the class
     *
     * @return array
     */
    public function GetErrors(){
        $data = array();

        // Loop through all the fields
        foreach($this->fields as $name=>$field)
            if ($field->Get('error') != '')
                $data[$name] = $field->Get('error');

        return $data;
    }

    /**
     * Get Label
     *
     * @param $field string
     * @return string
     */
    public function GetLabel($field, $append=''){
        // Make sure it is a field
        if ($this->IsField($field))
            return $this->fields[$field]->Label($append);

        return '';
    }

    /**
     * Set Labels
     *
     * Set the labels for the fields in the class
     *
     * @param $labels array
     * @return bool
     */
    public function SetLabels($labels){
        // Require an array
        if (!is_array($labels))
            return false;

        // Loop through all the newly hidden
        foreach($labels as $field=>$label){
            // Make it hidden
            $this->Set('label', $field, $label);
        }

        return true;
    }

    /**
     * Set Examples
     *
     * Set the examples for the fields in the class
     *
     * @param $examples array
     * @return bool
     */
    public function SetExamples($examples){
        // Require an array
        if (!is_array($examples))
            return false;

        // Loop through all the newly hidden
        foreach($examples as $field=>$example){
            // Make it hidden
            $this->Set('example', $field, $example);
        }

        return true;
    }

    /**
     * Get Fields
     *
     * Get a list of all the fields in the database
     *
     * @return array
     */
    public function GetFields(){
        return array_keys($this->fields);
    }

    /**
     * Is Field
     *
     * Check to see if a field exists
     *
     * @param $field string of the field
     * @return bool
     */
    public function IsField($field){
        return (isset($this->fields[$field]) && is_object($this->fields[$field]));
    }

    /**
     * Set Values
     *
     * Set all the Values of a Class
     *
     * @param $data An associtive array with all the keys and values for the object
     * @return bool
     */
    public function SetValues($data){
        Debug('SetValues(), Values: ' . print_r($data, true));

        // Make sure data is an array
        if (!is_array($data))
            return false;

        // Loop through all the values
        foreach($this->fields as $name=>$field){
            // Set the Field Values
            switch($field->Get('type')){
                case 'date':
                    if ($data[$name] != ''){
                        if($time = strtotime(urldecode($data[$name])))
                            $this->Set('value', $name, date("Y-m-d",$time));
                        else
                            $this->Set('error', $name, 'Invalid date (MM/DD/YYYY)');
                    }
                    break;
                case 'time':
                    if ($data[$name] != ''){
                        if($time = strtotime(urldecode($data[$name])))
                            $this->Set('value', $name, date("H:i:s",$time));
                        else
                            $this->Set('error', $name, 'Invalid time (HH:MM:SS)');
                    }
                    break;
                case 'datetime':
                    if (!empty($data[$name])){
                        // Try to get the time
                        $time = urldecode($data[$name]);

                        if ($time != '0000-00-00 00:00:00')
                            $time = date("Y-m-d H:i:s",strtotime($time));

                        if($time != '')
                            $this->Set('value', $name, $time);
                        else
                            $this->Set('error', $name, 'Invalid time (MM/DD/YYYY HH:MM:SS)');
                    }
                    break;
                default:
                    // If there is a value being passed
                    if (isset($data[$name]) && (!empty($data[$name]) || (string)$data[$name] == '0')){
                        if (is_array($data[$name]))
                            $this->Set('value', $name, implode(',', $data[$name]));
                        else
                            $this->Set('value', $name, (($field->Get('display') == 0)?urldecode($data[$name]):$data[$name]));
                    }else{
                        // If no value, set the value to default
                        $this->Set('value', $name, $this->Get('default', $name));
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Reset Values
     *
     * Reset all the Values of a Class
     *
     * @return bool
     */
    public function ResetValues(){
        // Loop through all the fields
        foreach($this->fields as $name=>$field){
            $this->Set('value', $name, '');
            $this->Set('error', $name, '');
        }

        return true;
    }

    /**
     * Reset Errors
     *
     * Reset all the Errors of a Class
     *
     * @return bool
     */
    public function ResetErrors(){
        // Loop through all the fields
        foreach($this->fields as $name=>$field)
            $this->Set('error', $name, '');

        return true;
    }

    /**
     * Get Values
     *
     * Get a list of all the fields and values in the class
     *
     * @return array
     */
    public function GetValues(){
        $data = array();

        // Loop through all the fields
        foreach($this->fields as $name=>$field)
            $data[$name] = $field->Get('value');

        return $data;
    }

    /**
     * Get Required
     *
     * Get a list of all the required fields in the class
     *
     * @return array
     */
    public function GetRequired(){
        $data = array();

        // Loop through all the fields
        foreach($this->fields as $name=>$field)
            if ($field->Get('required') == true)
                $data[] = $name;

        return $data;
    }

    /**
     * Set Required
     *
     * Set the required fields in the class
     *
     * @param $fields array
     * @return bool
     */
    public function SetRequired($fields){
        // Require an array
        if (!is_array($fields))
            return false;

        // Get the field names
        $keys = $this->GetFields();

        // Loop through all the fields
        foreach($keys as $name)
            $this->Set('required', $name, (in_array($name,$fields)));

        return true;
    }

    /**
     * Set Display
     *
     * Set the display fields in the class
     *
     * @param $fields array
     * @return bool
     */
    public function SetDisplay($fields){
        // Require an array
        if (!is_array($fields))
            return false;

        // Get the field names
        $keys = $this->GetFields();

        // Loop through all the fields
        foreach($keys as $name){
            // Get its display position
            $pos = array_search($name, $fields);
            // If it is in the display array
            if ($pos !== false){
                // Set the new display order
                $this->Set('display', $name, ($pos+1));

                // Place it in the correct order
                $old_pos = array_search($name, $this->display);
                if ($pos != $old_pos){
                    $tmp = $this->display[$pos];
                    $this->display[$pos] = $name;
                    $this->display[$old_pos] = $tmp;
                }
            }else{
                // If the field is no longer in the display
                if ($this->Get('display', $name) > 0){
                    // Make it hidden
                    $this->Set('display', $name, 0);
                }
            }
        }

        return true;
    }

    /**
     * Get Display
     *
     * Get the display fields in the class
     *
     * @return array
     */
    public function GetDisplay(){
        return $this->display;
    }

    /**
     * Set Hidden
     *
     * Set the hidden fields in the class
     *
     * @param $fields array
     * @return bool
     */
    public function SetHidden($fields){
        // Require an array
        if (!is_array($fields))
            return false;

        // Loop through all the newly hidden
        foreach($fields as $name){
            // Make it hidden
            $this->Set('display', $name, 0);
        }

        return true;
    }

    /**
     * Set Omit
     *
     * Set the omitted fields in the class
     *
     * @param $fields array
     * @return bool
     */
    public function SetOmit($fields){
        // Require an array
        if (!is_array($fields))
            return false;

        // Loop through all the newly omited
        foreach($fields as $name){
            // Make it omit
            $this->Set('display', $name, -1);
        }

        // Remove it from the display array
        $this->display = array_diff($this->display, $fields);

        return true;
    }

    /**
     * Set Defaults
     *
     * Set the default values for the fields
     *
     * @param $fields array
     * @return bool
     */
    public function SetDefaults($fields){
        // Require an array
        if (!is_array($fields))
            return false;

        // Loop through all the new defaults
        foreach($fields as $name=>$value){
            // Make it always the default
            $this->Set('default', $name, $value);
        }

        return true;
    }

    /**
     * Set Options
     *
     * Set the option values for the fields
     *
     * @param $options array
     * @return bool
     */
    public function SetOptions($options){
        // Require an array
        if (!is_array($options))
            return false;

        // Loop through all the options
        foreach($options as $name=>$value){
            // Set the options in their individual classes
            $this->Set('options', $name, $value);
        }

        // All options set
        return true;
    }

    /**
     * Set Options for a specific field
     *
     * Set the option values for a specific field and an optional first item
     *
     * @param $field string
     * @param $options array
     * @param $first string
     * @return bool
     */
    public function SetOption($field, $options, $first=''){
        // Make sure it is a valid field
        if (!$this->IsField($field))
            return false;

        // Append the first item to the options if needed
        if ($first != '' && is_array($options))
            $options = array(''=>$first) + $options;

        // Set the options for the field
        return $this->Set('options', $field, $options);
    }

    /**
     * Set Config
     *
     * Set the config values for the fields
     *
     * @param $config string
     * @return bool
     */
    public function SetConfig($config){
        // Require an array
        if (!is_array($config))
            return false;

        // Loop through all the options
        foreach($config as $name=>$value){
            // Set the options in their individual classes
            $this->Set('config', $name, $value);
        }

        // All options set
        return true;
    }

    /**
     * Set Setting
     *
     * @param $setting string
     * @param $value string
     * @return bool
     */
    public function SetSetting($setting, $value){
        // Make sure it is a valid setting
        if (!array_key_exists($setting, $this->settings['form']))
            return false;

        // Set the Setting
        $this->settings['form'][$setting] = $value;

        return true;
    }

    /**
     * Get Setting
     *
     * @param $setting string
     * @return bool
     */
    public function GetSetting($setting){
        // Get the Setting
        return $this->settings['form'][$setting];
    }

    /**
     * Set the Prefix of the form
     *
     * @param boolean $prefix
     * @return bool
     */
    public function SetPrefix($prefix){
        $this->prefix = $prefix;
        return true;
    }

    public function Nice(){
        return $this->SimpleFormat();
    }

    /**
     * Simple Format
     *
     * Formats $this is a easy to read compact way to be used for Debug
     *
     * @return string
     */
    public function SimpleFormat(){
        // Start the Output
        $output = '';
        $required = $this->GetRequired();
        $errors = $this->GetErrors();
        $fields = $this->GetFields();

        // Format a nice Summary
        $output .= '<strong>Name:</strong>' . "\t" . get_class($this) . ' ' . "\t" . '<strong>Parent:</strong>' . "\t" . get_parent_class($this) . '' . "\n";
        $output .= '<strong>Required:</strong>' . "\n\t" . ((is_array($required))?implode(', ',$required):'No Required Fields') . "\n";
        $output .= '<strong>Errors:</strong>' . "\n";
        if (count($errors) > 0)
            foreach($errors as $name=>$error)
                $output .= "\t" . $name . ' => ' . $error . "\n";
        else
            $output .= "\t" . 'No Errors' . "\n";
        $output .= '<strong>Fields:</strong>' . "\n";
        if (count($fields) > 0)
            foreach($fields as $name=>$field)
                $output .= "\t" . $field . ' => ' . $this->GetValue($field) . ((!empty($errors[$field]))?' <strong>:</strong> ' . $errors[$field]:'') . "\n";
        else
            $output .= "\t" . 'No Fields' . "\n";

        return $output;
    }

    /**
     * Display Individual Field
     *
     * @param $field string
     * @param $hidden boolean
     * @param $options array
     * @param $config array
     * @return NULL
     */
    public function FormField($field, $hidden=false, $options='', $config='', $multi=false){
        if ($this->IsField($field)){
            if ($hidden != true){
                // Force Display value for $field if display for the $field is hidden (0) or omitted (-1)
                if($this->fields[$field]->Get('display') <= 0){
                    $this->fields[$field]->Set('display', 1);
                }

                $this->fields[$field]->Form($options, $config, $multi, $this->prefix);
            }else{
                $name = ($prefix != '')?$prefix . '[' . $field . ']':$field;
                echo '<input name="' . $name . (($multi)?'[]':'') . '" type="hidden" id="' . $name . (($multi)?'_' . $this->Get('multi'):'') . '" value="' . urlencode($this->Output($this->fields[$field]->Get('value'))) . '" />' . "\n";
            }
        }
    }

    /**
     * Display Form
     *
     * @param $display array
     * @param $hidden array
     * @param $options array
     * @param $config array
     * @param $omit array
     * @return string
     */
    public function Form($display='', $hidden=array(), $options=array(), $config=array(), $omit=array(), $mutli=false){
        // Set the Displays
        $this->SetDisplay($display);
        $this->SetHidden($hidden);
        $this->SetOmit($omit);

        // Start the fieldset
        echo '<fieldset><legend>Information</legend>' . "\n";

        // Show the fields
        foreach($this->display as $field)
            $this->fields[$field]->Form($options[$field], $config[$field], $mutli, $this->prefix);

        // End the fieldset
        echo '</fieldset>' . "\n";
    }

    /**
     * Display a Mutli type Form
     *
     * @param $display array
     * @param $hidden array
     * @param $options array
     * @param $config array
     * @param $omit array
     * @return NULL
     */
    public function MultiForm($display='', $hidden=array(), $options=array(), $config=array(), $omit=array()){
        // Call the original form with the multi flag
        $this->Form($display, $hidden, $options, $config, $omit, true);
    }

    /**
     * Display Individual Mutli type Field
     *
     * @param $field string
     * @param $hidden boolean
     * @param $options array
     * @param $config array
     * @return NULL
     */
    public function MultiFormField($field, $hidden=false, $options='', $config=''){
        // Call the original form field with the multi flag
        $this->FormField($field, $hidden, $options, $config, true);
    }

    /**
     * Display the error messages in one place
     *
     * @return string
     */
    public function ErrorMessages(){
        // Get the errors
        $errors = $this->GetErrors();

        $output = '';

        // If there are errors
        if (count($errors) > 0){
            $output .= '<div class="errorExplaination">';
            $output .= '<h2>' . count($errors) . ' errors prohibited this ' . get_class($this) . ' from being saved</h2>';
            $output .= '<p>There were problems with the following fields:</p>';
            $output .= '<ul>';

            foreach($errors as $field=>$error)
                $output .= '<li>' . $this->Output($error) . '</li>';
            $output .= '</ul>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Format output
     *
     * @param $string string
     * @return string
     */
    public function Output($string){
        return stripslashes($string);
    }
}