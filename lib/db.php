<?php
/**
 * Database Interaction Class
 *
 * Used to interact with the database in a sane manner
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class DB {
    /**
	 * @var string 
	 */
    private $database;
    /**
	 * @var int 
	 */
    public $query_count;
	/**
     * @var bool
     */
    private $connected;
    /**
     * @var array
     */
    protected $results;
    /**
     * @var string
     */
    private $config;
    
    /**
	 * Class Constructor
	 *
	 * Creates a DB Class with all the information to use the DB
	 *
	 * @return null
	 */
    public function __construct(){
		$this->connected = false;	
    	$this->query_count = 0;
    }
    
    /**
	 * Setup the DB Connection
	 *
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @return bool
	 */
	public function Connect($server=DB_HOST, $username=DB_USER, $password=DB_PASS, $database=DB_DEFAULT){
		global $db;
		
		// Save the config till we are ready to connect
		if (!$this->connected)
			$this->config = array($server,$username,$password,$database);
		
		// If using DB Sessions start them now
    	if (DB_SESSIONS == true && session_id() == '')
    		@session_start();
		
		return true;
	}
	
	/**
	 * Connect to the DB when needed
	 *
	 * @return bool
	 */
	public function DbConnect(){
		if ($this->connected)
			return true;
			
		// Use the Global Link
		global $db_link;
		
		// If we are not connected
		if (is_array($this->config)){
			// Set all the local variables
			$this->database = $this->config[3];
			
			// Connect to MySQL
			$db_link = @mysql_connect($this->config[0], $this->config[1], $this->config[2]);
			
			if ($db_link){
				// Update the state
				$this->connected = true;
				
				// If there is a DB Defined select it
				if ($this->database != NULL && !@mysql_select_db($this->database)){
					return false;
				}
				
				// Remove the unneeded variables
				unset($this->config);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Execute a Query
	 * 
	 * Execute a query on a particular databse
	 * 
	 * @param string $query
	 * @param string $db to override default database
	 * @return Mixed
	 * 
	 */
	public function Query($query, $db='', $cache=true, $log=true) {
		global $db_link;
		
		// Track the start time of the query
		if (DB_LOG && $log){
			$start = explode(' ',microtime());
			$start = (float)$start[1] + (float)$start[0];
		}
		
		Debug('Query: ' . $query, 'query');
		
		// Default is not to read cache
		$is_cache = false;
		
		// If we can look for cache
		if ($cache && QUERY_CACHE && strtolower(substr($query,0,6)) == 'select' && is_writable(FS_CACHE)){
			// Format the cache file
			$cache_file = FS_CACHE . 'query_' . bin2hex(md5($query, TRUE)) . '.cache.php';
			$is_cache = true;
			
			// If there is a cache file
			if (is_file($cache_file)){
				// Retrieve and return cached results
				$this->results = unserialize(file_get_contents($cache_file));
				return $this->results;
			}
		}
		
		// Make sure we are connected
		$this->DbConnect();
		
		// Change the DB if needed
		if ($db != '' && $db != $this->database){
			$old_db = $this->database;
			$this->Change($db);
		}
		
		// Display the query if needed
		if (DEBUG_QUERY == true){
			echo '<pre class="debug">';
			print_r($query);
			echo '</pre>';
		}
		
		// Do the Query
    	$result = mysql_query($query, $db_link) or $this->Error($query, mysql_errno(), mysql_error());
    	
    	// Increment the query counter
    	$this->query_count++;
    	
    	// Change the DB back is needed
    	if ($db != '' && $db != $this->database)
    		$this->Change($old_db);
    		
    	// Track the total time of the request
    	if (DB_LOG && $log){
			$end = explode(' ',microtime());
			$end = (float)$end[1] + (float)$end[0];
			$time_take = sprintf('%.6f',(float)$end - (float)$start);
			
			// Actually log the query, cross your fingers the table and DB exist
			
		}
    		
    	// Cache the Query if possible
    	if ($is_cache){
    		// Clear the array
    		$this->results = array();
    		
    		// Create the results array
    		while($info = mysql_fetch_array($result, MYSQL_ASSOC))
    			$this->results[] = $info;
    		
    		// Serialize it and save it
    		$cache = serialize($this->results);
    		$fp = fopen($cache_file ,"w");
			fwrite($fp,$cache);
			fclose($fp);
			chmod ($cache_file, 0777);
    	}
    	
    	return $result;
	}
	
	/**
	 * Perform a Query
	 * 
	 * Use a smart way to create a query from abstract data
	 * 
	 * @param string $table 
	 * @param array $data in form key=>value
	 * @param string $action (ex. "update" or "insert")
	 * @param string $parameters additional things that need to go on like "item_id='5'"
	 * @param string $db to override default database
	 * @return result
	 */
	public function Perform($table, $data, $action = 'insert', $parameters = '', $db = '', $clear = true) {
		// Use the Global Link
		global $db_link;
		global $mySimpl;
		
		// Make sure we are connected
		$this->DbConnect();
		
		// Clear the Query Cache
		if ($clear == true)
			$mySimpl->Cache('clear_query');
		
		// Decide how to create the query
		if ($action == 'insert'){
			// Start the Query
			$query = 'INSERT INTO `' . trim($table) . '` (';
			$values = '';
			// Add each column in
			foreach($data as $column=>$value){
				// Create the First Half
				$query .= '`' . $column . '`, ';
				// Create the Second Half
				switch ((string)$value) {
					case 'now()':
						$values .= 'now(), ';
					break;
					case 'null':
						$values .= 'null, ';
					break;
					default:
						$values .= '\'' . $this->Prepare($value) . '\', ';
					break;
				}
			}
			// Conntect the columns with the values
			$query = substr($query, 0, -2) . ') VALUES (' . substr($values, 0, -2) . ')';
		}else if($action == 'update') {
			// Start the Query
			$query = 'UPDATE `' . $table . '` SET ';
			foreach($data as $column=>$value){
				switch ((string)$value) {
					case 'now()':
						$query .= '`' . $column . '` = now(), ';
					break;
					case 'null':
						$query .= '`' . $column .= '` = null, ';
					break;
					default:
						$query .= '`' . $column . '` = \'' . $this->Prepare($value) . '\', ';
					break;
				}
			}
			// Finish off the query
			$query = substr($query, 0, -2) . ' WHERE ' . $parameters;
		}
		
		return $this->Query($query, $db);
	}
	
	/**
	 * Close the database connection
	 * 
	 * @return bool
	 */
	public function Close(){
		// If Connected
 		if ($this->connected){
			// Use the Global Link
			global $db_link;
 		
    		return @mysql_close($db_link);
 		}
 		
    	return true;
	}
	
	/**
	 * Change the Database
	 * 
	 * @param string $database
	 * @return bool
	 */
	public function Change($database){
		// Use the Global Link
		global $db_link;
		
		// Make sure we are connected
		$this->DbConnect();
 		
 		// If there is a connection
    	if ($db_link && @mysql_select_db($database)){
			// Increment the query counter
    		$this->query_count++;
    		
    		Debug('DbChange(), Changed database to: ' . $database);
    		
			return true;
    	}
    	
		return false;
	}
	
	/**
	 * Throw an error
	 * 
	 * Display the Error to the Screen and Record in DB then Die()
	 * 
	 * @param string $query
	 * @param int $errno
	 * @param string $error
	 * @return null
	 */
	public function Error($query, $errno, $error) {
		// Close the Database Connection
		$this->Close();
		
		Debug('DbError(), ' . $errno . ' - ' . $error . ' - ' . $query);
		
		// Kill the Script
  		die('<div class="db-error"><h1>' . $errno . ' - ' . $error . '</h1><p>' . $query . '</p></div>');
	}
	
	/**
	 * Fetch the results Array
	 * 
	 * @param mixed $result from DB
	 * @return array
	 */
	public function FetchArray($result) {
		// Determine weather to get the result from an array or mysql
		if (QUERY_CACHE && is_array($this->results))
			return array_shift($this->results);
		else
			return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	/**
	 * Number of Rows Returned
	 * 
	 * @param mixed $result from DB
	 * @return int
	 */
	public function NumRows($result) {
		// Determine where to find the number of rows
		if (QUERY_CACHE && is_array($result)){
			return count($this->results);
		}else{
			return mysql_num_rows($result);
		}
	}
	
	/**
	 * Number of Rows Affected
	 * 
	 * @return int
	 */
	public function RowsAffected() {
		global $db_link;
		return mysql_affected_rows($db_link);
	}
	
	/**
	 * The ID that was just inserted
	 * 
	 * @return int
	 */
	public function InsertID() {
		return mysql_insert_id();
	}

	/**
	 * Free Resulting Memory
	 * 
	 * @param mixed $result from DB
	 * @return bool
	 */
	public function FreeResult($result) {
		return mysql_free_result($result);
	}
	
	/**
	 * Get the Field Information
	 * 
	 * @param mixed $result from DB
	 * @return object
	 */
	public function FetchField($result) {
		return mysql_fetch_field($result);
	}
	
	/**
	 * Get the Field Length
	 * 
	 * @param mixed $result from DB
	 * @param string $field
	 * @return object
	 */
	public function FieldLength($result,$field) {
		return mysql_field_len($result, $field);
	}
	
	/**
	 * Format the string for output from the Database
	 * 
	 * @param string $string
	 * @return string
	 */
	public function Output($string) {
		return htmlspecialchars(stripslashes($string));
	}
	
	/**
	 * Return if the DB is connecte
	 * 
	 * @return boolean
	 */
	public function IsConnected() {
		return (bool)$this->connected;
	}

	/**
	 * Format the string for input into the Database
	 * 
	 * @param string $string
	 * @return string
	 */
	public function Prepare($string) {
		// Make sure we are connected first
		if (!$this->connected)
			$this->DbConnect();
		
		// Escape the values from SQL injection
		return (is_numeric($string))?addslashes($string):mysql_real_escape_string($string);
	}
}
?>