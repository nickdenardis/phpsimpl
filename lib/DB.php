<?php

namespace Simpl;

use PDO;
use PDOException;
use Simpl\Contracts\DbContract;

/**
 * Database Interaction Class
 *
 */
class DB extends Simpl implements DbContract
{
    /**
     * @var string
     * Current connected database name
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
     * @var array
     */
    private $config = array();
    /**
     * @var PDO
     */
    private $db_link;

    /**
     * Class Constructor
     *
     * Creates a DB Class with all the information to use the DB
     *
     * @return \Simpl\DB
     */
    public function __construct()
    {
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
    public function Connect($server = DB_HOST, $username = DB_USER, $password = DB_PASS, $database = DB_DEFAULT)
    {
        // Save the database config to connect later
        if (!array_key_exists($database, $this->config)) {
            $this->config[$database] = array(
                'connected' => false,
                'host' => $server,
                'username' => $username,
                'password' => $password,
                'link' => null,
            );
        }

        // If there is no default database, set that
        if (!isset($this->database)) {
            $this->database = $database;
        }

        return true;
    }

    /**
     * Connect to the DB when needed
     *
     * @return bool
     */
    public function DbConnect()
    {
        // If already connected
        if ($this->config[$this->database]['connected']) {
            return true;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Connect to MySQL
        try {
            $this->config[$this->database]['link'] = new PDO(
                "mysql:host=" . $this->config[$this->database]['host'] . ";dbname=" . $this->database,
                $this->config[$this->database]['username'],
                $this->config[$this->database]['password'],
                $options);
        } catch(PDOException $e) {
            $this->Error('', (int)$e->getCode(), $e->getMessage());
            return false;
        }

        // Update the state
        $this->config[$this->database]['connected'] = true;

        return true;
    }

    /**
     * Return if the DB is connected
     *
     * @return boolean
     */
    public function IsConnected()
    {
        return $this->config[$this->database]['connected'];
    }

    protected function getConnection($database = null)
    {
        $name = $this->database;

        if ($database != null && array_key_exists($database, $this->config)) {
            $name = $database;
        }

        if ($this->config[$name]['connected']) {
            return $this->config[$name]['link'];
        }

        return false;
    }

    /**
     * Return the currently connected database
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Change the Database
     *
     * @param string $database
     * @return bool
     */
    public function Change($database)
    {
        if ((string)trim($database) == '') {
            return false;
        }

        // Check to see if we already have a connection to this database
        if ($this->database == $database && $this->config[$database]['connected']) {
            return $this->config[$database]['link'];
        }

        // If not, connect to it with the default connection info
        try {
            $this->config[$database]['link'] = new PDO(
                "mysql:host=" . $this->config[$this->database]['host'] . ";dbname=" . $this->database,
                $this->config[$this->database]['username'],
                $this->config[$this->database]['password']);
        } catch(PDOException $e) {
            $this->Error('', (int)$e->getCode(), $e->getMessage());
            return false;
        }

        // Update the state
        $this->config[$database]['connected'] = true;

        // Increment the query counter
        $this->query_count++;

        // Report to the user
        Debug('DbChange(), Changed database to: ' . $database);

        return $this->config[$database]['link'];
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
    public function Query($query, $db = '', $cache = true, $log = true)
    {
        // Track the start time of the query
        if (DB_LOG && $log) {
            $start = explode(' ', microtime());
            $start = (float)$start[1] + (float)$start[0];
        }
        Debug('Query: ' . $query, 'query');
        /*
                // Default is not to read cache
                $is_cache = false;

                // If we can look for cache
                if ($cache && QUERY_CACHE && strtolower(substr($query, 0, 6)) == 'select' && is_writable(FS_CACHE)) {
                    // Format the cache file
                    $cache_file = FS_CACHE . 'query_' . bin2hex(md5($query, true)) . '.cache.php';
                    $is_cache = true;

                    // If there is a cache file
                    if (is_file($cache_file)) {
                        // Retrieve and return cached results
                        $this->results = unserialize(file_get_contents($cache_file));
                        return $this->results;
                    }
                }
        */

        // Make sure we are connected
        $this->DbConnect();
        $connection = $this->getConnection();

        // Change the DB if needed
        if ($db != '') {
            $connection = $this->Change($db);
        }

        // Display the query if needed
        if (DEBUG_QUERY == true) {
            echo '<pre class="debug">';
            print_r($query);
            echo '</pre>';
        }

        // Do the Query
        try {
            $result = $connection->query($query)->fetchAll();
        } catch(PDOException $e) {
            $this->Error($query, (int)$e->getCode(), $e->getMessage());
        }

        // Increment the query counter
        $this->query_count++;

        // Track the total time of the request
        if (DB_LOG && $log) {
            $end = explode(' ', microtime());
            $end = (float)$end[1] + (float)$end[0];
            $time_take = sprintf('%.6f', (float)$end - (float)$start);
        }

        /*
                // Cache the Query if possible
                if ($is_cache) {
                    // Clear the array
                    $this->results = array();

                    // Create the results array
                    while ($info = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $this->results[] = $info;
                    }

                    // Serialize it and save it
                    $cache = serialize($this->results);
                    $fp = fopen($cache_file, "w");
                    fwrite($fp, $cache);
                    fclose($fp);
                    chmod($cache_file, 0777);
                }
        */
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
    public function Perform($table, $data, $action = 'insert', $parameters = '', $db = '', $clear = true)
    {
        // Make sure we are connected
        $this->DbConnect();

        // Clear the Query Cache
        if ($clear == true) {
            $this->ClearCache('clear_query');
        }

        // Decide how to create the query
        if ($action == 'insert') {
            // Start the Query
            $query = 'INSERT INTO `' . trim($table) . '` (';
            $values = '';
            // Add each column in
            foreach ($data as $column => $value) {
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
        } else {
            if ($action == 'update') {
                // Start the Query
                $query = 'UPDATE `' . $table . '` SET ';
                foreach ($data as $column => $value) {
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
        }

        return $this->Query($query, $this->db_link);
    }

    /**
     * Close the database connection
     *
     * @return bool
     */
    public function Close()
    {
        // Close all connections
        foreach($this->config as $connection) {
            if ($connection['connected']) {
                $connection['link'] = null;
                $connection['connected'] = false;
            }
        }

        return true;
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
    public function Error($query, $errno, $error)
    {
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
    public function FetchArray($result)
    {
        // Determine weather to get the result from an array or mysql
        if (QUERY_CACHE && is_array($this->results)) {
            return array_shift($this->results);
        } else {
            return mysql_fetch_array($result, MYSQL_ASSOC);
        }
    }

    /**
     * Number of Rows Returned
     *
     * @param mixed $result from DB
     * @return int
     */
    public function NumRows($result)
    {
        // Determine where to find the number of rows
        if (QUERY_CACHE && is_array($result)) {
            return count($this->results);
        } else {
            return mysql_num_rows($result);
        }
    }

    /**
     * Number of Rows Affected
     *
     * @return int
     */
    public function RowsAffected()
    {
        return mysql_affected_rows($this->db_link);
    }

    /**
     * The ID that was just inserted
     *
     * @return int
     */
    public function InsertID()
    {
        return mysql_insert_id();
    }

    /**
     * Free Resulting Memory
     *
     * @param mixed $result from DB
     * @return bool
     */
    public function FreeResult($result)
    {
        return mysql_free_result($result);
    }

    /**
     * Get the Field Information
     *
     * @param mixed $result from DB
     * @return object
     */
    public function FetchField($result)
    {
        return mysql_fetch_field($result);
    }

    /**
     * Get the Field Length
     *
     * @param mixed $result from DB
     * @param string $field
     * @return object
     */
    public function FieldLength($result, $field)
    {
        return mysql_field_len($result, $field);
    }

    /**
     * Format the string for output from the Database
     *
     * @param string $string
     * @return string
     */
    public function Output($string)
    {
        return htmlspecialchars(stripslashes($string));
    }


    /**
     * Format the string for input into the Database
     *
     * @param string $string
     * @return string
     */
    public function Prepare($string)
    {
        // This is now done in prepared statements
        return $string;
    }

    public function ParseLength($type_string)
    {
        if( preg_match( '!\(([^\)]+)\)!', $type_string, $match ) ) {
            return $match[1];
        }

        return 0;
    }

    public function ParseType($Type)
    {
    }
}
