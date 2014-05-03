<?php
/**
 * Base class for exporting data in various formats
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Export {
	/**
	 * @var array
	 */
	private $display = array();
	/**
	 * @var array
	 */
	private $data = array();
	/**
	 * @var string
	 */
	private $filename = 'export';
	/**
	 * @var string
	 */
	private $output = array();
	
	/**
	 * Class Constructor
	 *
	 * Creates an exported file from a given array or DbTemplate
	 *
	 * @param $data Array of items
	 * @param $display Array
	 * @param $file_name String of the filename
	 * @return null
	 */
	public function __construct($data='', $display='', $filename=''){
		// Set the data
		$this->SetData($data);
		
		// Set the display
		$this->SetDisplay($display);
			
		// Set the filename
		$this->SetFilename($filename);
	}
	
	/**
	 * Set Filename
	 *
	 * @param $filename String
	 * @return bool
	 */
	public function SetFilename($filename){
		// Cut out bad chars
		$filename = preg_replace("/[^A-Za-z0-9-]/i", "_", $filename);
	
		// Require not null
		if ((string)$filename == '')
			return false;
		
		// Set the filename
		$this->filename = (string)$filename;
			
		return true;
	}
	
	/**
	 * Set Display
	 *
	 * @param $display Array
	 * @return bool
	 */
	public function SetDisplay($display){
		// Require array
		if (!is_array($display))
			return false;
		
		// Overwrite the display
		$this->display = $display;
			
		return true;
	}
	
	/**
	 * Set Data
	 *
	 * @param $data Array of items
	 * @return bool
	 */
	public function SetData($data){
		// Setup the export to work with the info supplied
		if (is_object($data)){
			// Set the data
			if (is_array($data->results))
				$this->data = $data->results;
				
			// Set the display
			$this->display = $data->GetDisplay();
		}else if (is_array($data)){
			$this->data = $data;
		}else{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Retrieve the desired format to the output
	 *
	 * @param $type string (csv, json, xml, sql)
	 * @return string
	 */
	public function Retrieve($type){
		switch(strtolower($type)){
			case 'sql':
				return $this->CreateSQL();
				break;
			case 'json':
				return $this->CreateJSON();
				break;
			case 'xml':
				return $this->CreateXML();
				break;
			case 'csv':
			default:
				return $this->CreateCSV();
				break;
		}
	}
	
	/**
	 * Download the desired format to the output
	 *
	 * @param $type string (csv, json, xml, sql)
	 * @return null
	 */
	public function Download($type){
		switch(strtolower($type)){
			case 'sql':
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-type: text/plain sql");
				header("Content-disposition: attachment; filename=" .  $this->filename . ".sql");
				header("Content-Transfer-Encoding: binary");
				print $this->CreateSQL();
				die();
				
				break;
			case 'json':
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-type: application/json");
				header("Content-disposition: attachment; filename=" .  $this->filename . ".json");
				header("Content-Transfer-Encoding: binary");
				print $this->CreateJSON();
				die();
				
				break;
			case 'xml':
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-type: application/xml");
				header("Content-disposition: attachment; filename=" .  $this->filename . ".xml");
				header("Content-Transfer-Encoding: binary");
				print $this->CreateXML();
				die();
				
				break;
			case 'csv':
			default:
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-type: text/comma-separated-values");
				header("Content-disposition: attachment; filename=" .  $this->filename . ".csv");
				header("Content-Transfer-Encoding: binary");
				print $this->CreateCSV();
				die();
				
				break;
		}
	}
	
	/**
	 * Actually create the CSV string
	 *
	 * @return string
	 */
	private function CreateCSV(){
		// Reset this output string
		$this->output['csv'] = NULL;
		
		// Filter these out
		$bad_output = array('"');
		$good_output = array('""');
		
		// Line Ending
		$end = "\r\n";
		
		// Loop through all the fields in display to create the titles
		foreach($this->display as $title)
			$this->output['csv'] .= '"' . str_replace($bad_output, $good_output, $title) . '",';

		// End the header
		$this->output['csv'] = substr($this->output['csv'], 0, -1) . $end;
		
		// Loop through each row
		foreach($this->data as $line=>$set){
			if (is_array($set)){
				// Loop through each column
				foreach($set as $data)
					$this->output['csv'] .= '"' . str_replace($bad_output, $good_output, stripslashes($data)) . '",';
				
				// End the line
				$this->output['csv'] = substr($this->output['csv'], 0, -1) . $end;
			}
		}
		
		return $this->output['csv'];
	}
	
	/**
	 * Actually create the XML string
	 *
	 * @return string
	 */
	private function CreateXML($raw = false){
		// Reset this output string
		$this->output['xml'] = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		
		if (!$raw)
			$this->output['xml'] .= '<items>' . "\n";
		
		// Filter these out
		$bad_output = array('&', "'", '"', '>', '<');
		$good_output = array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;');
		
		// Loop through each row
		foreach($this->data as $line=>$set){
			if (is_array($set)){
				if (!$raw)
					$this->output['xml'] .= "\t" . '<item>' . "\n";
				
				// Loop through each column
				foreach($set as $name=>$data)
					$this->output['xml'] .= "\t\t" . '<' . $name . '>' . str_replace($bad_output, $good_output, stripslashes($data)) . '</' . $name . '>' . "\n";
				
				if (!$raw)
					$this->output['xml'] .= "\t" . '</item>' . "\n";
			}
		}
		
		if (!$raw)
			$this->output['xml'] .= '</items>';

		return $this->output['xml'];
	}
	
	/**
	 * Actually create the JSON string
	 *
	 * @return string
	 */
	private function CreateJSON(){
		// Reset this output string
		$this->output['json'] = NULL;
		
		$myJson = new Json;
		$this->output['json'] = $myJson->encode($this->data);
		
		return $this->output['json'];
	}
	
	/**
	 * Actually create the SQL string
	 *
	 * @return string
	 */
	private function CreateSQL(){
		// Require filename
		if ($this->filename == '')
			return '';
		
		// Reset this output string
		$this->output['sql'] = 'INSERT INTO `' . $this->filename . '` (';
		
		// List the field names
		foreach($this->display as $title)
			$this->output['sql'] .= '`' . $title . '`,';
			
		// Chop off the comma
		$this->output['sql'] = substr($this->output['sql'], 0, -1) .  ') VALUES ' . "\n";
		
		// Loop through each row
		foreach($this->data as $set){
			if (is_array($set)){
				$this->output['sql'] .= '(';
				
				// Loop through each column
				foreach($set as $data)
					$this->output['sql'] .= '\'' . addslashes(stripslashes($data)) . '\',';
					
				// Chop off the comma
				$this->output['sql'] = substr($this->output['sql'], 0, -1) . '),' . "\n";
			}
		}
		
		$this->output['sql'] = substr($this->output['sql'], 0, -2) . ';';

		return $this->output['sql'];
	}
	
	/**
	 * DEPRICATED! GetXLS
	 *
	 * Creates an output string from the class data
	 *
	 * @return bool
	 */
	public function GetXLS() {
		// Debug
		Debug('DEPRICATED! GetXLS(). Please update your code. "New function: Retrieve(\'csv\')"');
		
		return $this->Retrieve('csv');
	}
	
	/**
	* DEPRICATED! DisplayXLS
	*
	* Displays the XLS file
	*
	* @return null
	*/	
	public function DisplayXLS($output='') {
		// Debug
		Debug('DEPRICATED! DisplayXLS(). Please update your code. "New function: Download(\'csv\')"');
		
		$this->Retrieve('csv');
		
		// Check if they are sending ouput, if not just use the classes output
		$output = ($output == '')?$this->output['csv'] : $output;

		// Display the XLS
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-type: text/comma-separated-values");
		header("Content-disposition: attachment; filename=" .  $this->filename . ".csv");
		header("Content-Transfer-Encoding: binary");
		print $output;
		exit;
	}
}
?>