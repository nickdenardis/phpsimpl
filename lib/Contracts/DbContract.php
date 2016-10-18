<?php

namespace Simpl\Contracts;

use Simpl\result;

/**
 * Database Interaction
 *
 */
interface DbContract
{
    /**
     * Setup the DB Connection
     *
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $database
     * @return bool
     */
    public function Connect($server = DB_HOST, $username = DB_USER, $password = DB_PASS, $database = DB_DEFAULT);

    /**
     * Connect to the DB when needed
     *
     * @return bool
     */
    public function DbConnect();

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
    public function Query($query, $db = '', $cache = true, $log = true);

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
    public function Perform($table, $data, $action = 'insert', $parameters = '', $db = '', $clear = true);

    /**
     * Close the database connection
     *
     * @return bool
     */
    public function Close();

    /**
     * Change the Database
     *
     * @param string $database
     * @return bool
     */
    public function Change($database);

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
    public function Error($query, $errno, $error);

    /**
     * Fetch the results Array
     *
     * @param mixed $result from DB
     * @return array
     */
    public function FetchArray($result);

    /**
     * Number of Rows Returned
     *
     * @param mixed $result from DB
     * @return int
     */
    public function NumRows($result);

    /**
     * Number of Rows Affected
     *
     * @return int
     */
    public function RowsAffected();

    /**
     * The ID that was just inserted
     *
     * @return int
     */
    public function InsertID();

    /**
     * Free Resulting Memory
     *
     * @param mixed $result from DB
     * @return bool
     */
    public function FreeResult($result);

    /**
     * Get the Field Information
     *
     * @param mixed $result from DB
     * @return object
     */
    public function FetchField($result);

    /**
     * Get the Field Length
     *
     * @param mixed $result from DB
     * @param string $field
     * @return object
     */
    public function FieldLength($result, $field);

    /**
     * Format the string for output from the Database
     *
     * @param string $string
     * @return string
     */
    public function Output($string);

    /**
     * Return if the DB is connecte
     *
     * @return boolean
     */
    public function IsConnected();

    /**
     * Format the string for input into the Database
     *
     * @param string $string
     * @return string
     */
    public function Prepare($string);

    /**
     * @return string
     */
    public function getDatabase();
}