<?php
/**
 * Base Field Class
 *
 * Used to create individual fields on a form
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Field {
	/**
	 * @var string 
	 */
	private $name;
	/**
	 * @var string 
	 */
	private $label;
	/**
	 * @var string 
	 */
	private $example;
	/**
	 * @var various 
	 */
	private $value;
	/**
	 * @var various 
	 */
	private $default;
	/**
	 * @var string 
	 */
	private $error;
	/**
	 * @var string 
	 */
	private $validate;
	/**
	 * @var boolean 
	 */
	private $required = false;
	/**
	 * @var int 
	 */
	private $length;
	/**
	 * @var string 
	 */
	private $type;
	/**
	 * @var int 
	 */
	private $display;
	/**
	 * @var string 
	 */
	private $config;
	/**
	 * @var array 
	 */
	private $options;
	/**
	 * @var int
	 */
	private $primary = 0;
	/**
	 * @var int
	 */
	private $multi = 0;
	
	/**
	 * Field Constructor
	 * 
	 * @return bool
	 */
	public function __construct(){
		// Set the field to display
		$this->display = 1;
		
		return true;
	}
	
	/**
	 * Get Field Property
	 *
	 * @param $property string
	 * @return bool
	 */
	public function Get($property){
		// Retrun the value of the property
		return $this->$property;
	}
	
	/**
	 * Set Field Property
	 *
	 * @param $property string
	 * @param $value mixed
	 * @return bool
	 */
	public function Set($property, $value){
		// Set the new value to the property
		if ($value != '' && $property == 'value' && $this->Get('type') == 'int'){
			$this->$property = (int)$value;
		}else{
			$this->$property = $value;
		}
			
		return true;
	}
	
	/**
	 * Validate the Field against the vaildate type
	 * 
	 * @return bool
	 */
	public function Validate(){
		// If the field is omited no need to validate
		if ($this->Get('display') < 0)
			return true;
		
		global $myValidator;
		
		// Check to see if there is already an errror
		if ($this->Get('error') != '')
			return false;
			
		// Check to see if it is required first
		if ($this->Get('required') == true && (string)$this->Get('value') == NULL){
			// Set the Error
			$this->Set('error', $this->Label() . ' is required.');
			return false;
		}
		
		// Validate agaist the regular expression
		if ($this->Get('validate') != '' && (string)$this->Get('value') != '' && $myValidator->Check($this->Get('validate'), $this->Get('value')) == false){
			// Set the Error
			$this->Set('error', $this->ErrorString());
			return false;
		}
		
		// Make sure it is within the correct length
		if (strlen((string)$this->Get('value')) > $this->Get('length')){
			// Set the Error
			$this->Set('error', $this->Get('label') . ' is too long.');
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get a human readable label for the field
	 * 
	 * @param $append string of test to append to the label
	 * @return string
	 */
	public function Label($append=''){
		// Get the label
		$str = $this->Output($this->Get('label'));
		
		// If no label use the name
		if (trim($str) == '')
			$str = ucfirst(str_replace('_',' ',$this->Get('name')));

		// Remove any ":" from the label
		if (substr($str,-1) == ':')
			$str = substr($str,0,-1);
			
		// If this is a question or period leave it
		if (substr(strip_tags($str),-1) == '?' || substr(strip_tags($str),-1) == '.')
			return $str;
		
		return $str . $append;
	}

	/**
	 * XHTML Form field
	 * 
	 * @param $options array
	 * @param $config string
	 * @return bool
	 */
	public function Form($options='', $config='', $multi=false, $prefix=''){
		global $mySimpl;
		$settings = $mySimpl->settings['form'];

		// If there is a default value use that
		$my_value = ((string)$this->Get('value') == '' && $this->Get('default') != '')?$this->Get('default'):$this->Get('value');
		
		// If prefix is set use the class name
		$name = ($prefix != '')?$prefix . '[' . $this->Get('name') . ']':$this->Get('name');
		
		// Change the fieldname to a multi if needed
		if ($multi) $this->Set('multi', $this->Get('multi')+1);
		
		// Figure out how to display the form
		if ($this->Get('display') < 0){
			// Omit
			return true;
		}else if ($this->Get('display') == 0){
			// Hidden
			echo '<input name="' . $name . (($multi)?'[]':'') . '" type="hidden" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" value="' . urlencode($this->Output($my_value)) . '" />' . "\n";
			return true;
		}
		
		// If time display in nice format
		if ($this->Get('type') == 'time' && $this->Get('value') != '')
			$my_value = date("g:i a", strtotime($my_value));
		
		// Overwrite the options if needed
		if(is_array($options) || is_object($options))
			$this->Set('options', $options);
		
		// Overwrite the config if needed
		if($config != '')
			$this->Set('config', $config);
		
		$output = '<div class="field_' . $this->Get('name') . '">';
		$output .= '<label for="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '">';
		$output .= ($this->Get('required') && ($settings['required_indicator'] == 'before' || $settings['required_indicator'] == ''))?'<em>*</em>':'';
		$output .= $this->Label($settings['label_ending']);
		$output .= ($this->Get('required') && $settings['required_indicator'] == 'after')?'<em>*</em>':'';
		$output .= '</label>';
		$output .= ($this->Get('error') != '')?'<div class="error">':'';

		// If passing in a class call its Form function
		if(is_object($this->Get('options'))){
			switch(get_class($this->Get('options'))){
				// If we are uploading a file
				case 'Upload':
					// If there is something in the field
					if ($my_value != ''){
						$output .=  '<div id="form_' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '">' . $my_value . ' <label><input name="remove' . (($multi)?'[]':'') . '[]" type="checkbox" value="' . $this->Get('name') . '" id="remove_' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" /> Remove File</label></div>';
						$output .=  '<input name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" type="hidden" value="' . $my_value . '" />' . "\n";
					}else{
						$output .=  '<input name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" type="file" />';
					}
					break;
				default:
					// Custom Form
					$obj = $this->Get('options');
					$output .= $obj->Form($this, $this->Get('config'), $multi, $prefix);
					break;
			}
		}else if (is_array($this->Get('options')) && count($this->Get('options')) > 0){
			// Multi Options
			switch($this->Get('config')){
				case 'radio':
					foreach($this->Get('options') as $key=>$value){
						$selected = ($my_value == (string)$key)?' checked="checked"':'';
						$each .= '<div><input name="' . $name . (($multi)?'_' . $this->Get('multi') . '[]':'') . '" type="radio" value="' . $key . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_' . $key . '"' . $selected . ' /><label for="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_' . $key . '">' . $this->Output($value) . '</label></div>';
					}
					$output .= '<div class="radio">' . $each . '</div>';
				break;
				case 'checkbox':
					$split = explode(',',$my_value);
					foreach($this->Get('options') as $key=>$value){
						$selected = (in_array($key,$split))?' checked="checked"':'';
						$each .= '<div><input name="' . $name . (($multi)?'[]':'') . '[]" type="checkbox" value="' . $key . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_' . $key . '"' . $selected . ' /><label for="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_' . $key . '">' . $this->Output($value) . '</label></div>';
					}
					$output .= '<div class="checkbox">' . $each . '</div>';
				break;
				default:
					$each = '<select name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '"' . (($this->Get('required'))?' class="required"':'') . '>' . "\n";
					foreach($this->Get('options') as $key=>$value){
						$selected = ((string)$my_value == (string)$key)?' selected="selected"':'';
						$each .= '<option value="' . $key . '"' . $selected . '>' . $this->Output($value) . '</option>' . "\n";
					}
					$each .= '</select>';
					$output .= '<div class="select">' . $each . '</div>';
				break;
			}
		}elseif($this->Get('type') == 'blob' || $this->Get('type') == 'text'){
			// Textarea
			$output .= '<div><textarea name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '"' . (($this->Get('required'))?' class="required"':'') . ' cols="50" rows="4">' . $this->Output($my_value) . '</textarea></div>' . "\n";
		}elseif($this->Get('type') == 'date'){
			// Date Field
			$value = ($my_value != '0000-00-00' && $my_value != '')?date("F j, Y",strtotime($my_value)):'';
			$output .= '<input name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" type="text" size="19" maxlength="19" value="' . $value . '" /><button type="reset" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_b">...</button>';	
			$output .= '<script type="text/javascript">Calendar.setup({ inputField : "' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '", ifFormat : "%B %e, %Y", button : "' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '_b"});</script>';
		}else{
			// Single Field
			$type = ($this->Get('config') != '' && $this->Get('config') != 'text')?$this->Get('config'):'text';
			$size = ($this->Get('length') < 30)?$this->Get('length'):30;
			$output .= '<input name="' . $name . (($multi)?'[]':'') . '" id="' . $this->Get('name') . (($multi)?'_' . $this->Get('multi'):'') . '" type="' . $type . '"' . (($this->Get('required'))?' class="required"':'') . ' size="' . $size . '" maxlength="' . $this->Get('length') . '" value="' . htmlspecialchars($this->Output($my_value), ENT_QUOTES) . '" />';
		}

		$output .= ($this->Get('example') != '')?'<div class="example"><p>' . stripslashes($this->Get('example')) . '</p></div>':'';
		$output .= ($this->Get('error') != '')?'<p>' . $this->Output($this->Get('error')) . '</p></div>':'';
		$output .= '</div>';

		// Output the form
		echo $output;
		
		return true;
	}
	
	/**
	 * View a single table line of this item
	 * 
	 * @param $options array
	 * @return null
	 */
	public function View($options = ''){
		if ($this->Get('display') <= 0)
			return;
		
		// Overwrite the options for this field if desired
		if(is_array($options))
			$this->Set('options', $options);
			
		// Get the localised options
		$options = $this->Get('options');
		
		// Display the individual item
		$output = '<tr>';
		$output .= '<th scope="row">' . $this->Label(':') . '</th>';
		$output .= '<td>' . (($options[$this->Get('value')] != '')?$options[$this->Get('value')]:$this->Output($this->Get('value'))) . '</td>';
		$output .= '</tr>';
		
		echo $output;
	}
	
	/**
	 * Get the plain text error type
	 * 
	 * @return string
	 */
	private function ErrorString(){
		// Depending on the type return the correct error
		return 'Field is not a valid ' . $this->Label();
	}

	/**
	 * Format output
	 *
	 * @param $string string
	 * @return string
	 */
	private function Output($string){
		return stripslashes($string);
	}
}
?>