<?php namespace Simpl;

/**
 * Database Interaction Class
 *
 * Used to interact with the database in a sane manner
 *
 * MIGRATION NOTE: Updated from mysql_* to mysqli_* for PHP 7+ compatibility
 * mysqli is available in PHP 5.0+ so this maintains backward compatibility
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class DB extends Simpl {
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
     * @var db_link
     */
    private $db_link;

    /**
     * Class Constructor
     *
     * Creates a DB Class with all the information to use the DB
     *
     * @return \Simpl\DB
     */
    public function __construct(){
        $this->connected = false;
        $this->query_count = 0;

        $this->Connect();
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
    public function Connect($server=DB_HOST, $username=DB_USER, $password=DB_PASS, $database=DB_DEFAULT, $port=null){
        // Save the config till we are ready to connect
        if ($this->connected)
            return true;

        if ($port === null && defined('DB_PORT'))
            $port = DB_PORT;
        
        if ($port === null)
            $port = 3306;

        $this->config = array($server,$username,$password,$database,$port);

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

        // If we are not connected
        if (is_array($this->config)){
            // Set all the local variables
            $this->database = $this->config[3];
            $port = (isset($this->config[4]) ? $this->config[4] : 3306);

            // Connect to MySQL using mysqli (compatible with PHP 5.0+)
            $this->db_link = @\mysqli_connect($this->config[0], $this->config[1], $this->config[2], $this->database, $port);

            if ($this->db_link){
                // Update the state
                $this->connected = true;

                // If there is a DB Defined select it (mysqli_connect already selects it, but keep for explicit changes)
                if ($this->database != NULL && !@\mysqli_select_db($this->db_link, $this->database)){
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
     * Execute a query on a particular database
     *
     * @param string $query
     * @param string $db to override default database
     * @param bool $cache
     * @param bool $log
     * @return Mixed
     */
    public function Query($query, $db='', $cache=true, $log=true) {
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
        if ($this->db_link == NULL) {
            Pre($this->Nice());
        }
        // Do the Query
        $result = \mysqli_query($this->db_link, $query) or $this->Error($query, \mysqli_errno($this->db_link), \mysqli_error($this->db_link));

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
            while($info = \mysqli_fetch_array($result, MYSQLI_ASSOC))
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
     * @param bool $clear
     * @return result
     */
    public function Perform($table, $data, $action = 'insert', $parameters = '', $db = '', $clear = true) {
        // Make sure we are connected
        $this->DbConnect();

        // Clear the Query Cache
        if ($clear == true)
            $this->ClearCache('clear_query');

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

        return $this->Query($query, $this->database);
    }

    /**
     * Close the database connection
     *
     * @return bool
     */
    public function Close(){
        // If Connected
        if ($this->connected){
            return @\mysqli_close($this->db_link);
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
        // Make sure we are connected
        $this->DbConnect();

        // If there is a connection
        if ($this->db_link && @\mysqli_select_db($this->db_link, $database)){
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
            return \mysqli_fetch_array($result, MYSQLI_ASSOC);
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
            return \mysqli_num_rows($result);
        }
    }

    /**
     * Number of Rows Affected
     *
     * @return int
     */
    public function RowsAffected() {
        return \mysqli_affected_rows($this->db_link);
    }

    /**
     * The ID that was just inserted
     *
     * @return int
     */
    public function InsertID() {
        return \mysqli_insert_id($this->db_link);
    }

    /**
     * Free Resulting Memory
     *
     * @param mixed $result from DB
     * @return bool
     */
    public function FreeResult($result) {
        return \mysqli_free_result($result);
    }

    /**
     * Get the Field Information
     *
     * @param mixed $result from DB
     * @return object
     */
    public function FetchField($result) {
        return \mysqli_fetch_field($result);
    }

    /**
     * Get the Field Length
     *
     * @param mixed $result from DB
     * @param string $field
     * @return object
     */
    public function FieldLength($result,$field) {
        // mysqli doesn't have mysql_field_len, use mysqli_fetch_field_direct instead
        $fieldInfo = \mysqli_fetch_field_direct($result, $field);
        return $fieldInfo ? $fieldInfo->length : 0;
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
        $this->DbConnect();

        // Escape the values from SQL injection
        return (is_numeric($string))?addslashes($string):\mysqli_real_escape_string($this->db_link, $string);
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        if (!$this->IsConnected())
            return $this->config[3];

        return $this->database;
    }
}