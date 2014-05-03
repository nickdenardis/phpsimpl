<?php
/**
 * Used to store sessions in the Database
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Session {
	private $ses_id;
	private $db;
	private $table;
	private $ses_life;
	private $ses_start;
	private $fingerprintKey = 'sdfkj43545lkjlkmndsf89a*(&(Nhnkj2h349*&(';
	private $threshold = 25;
	static private $fingerprintChecks = 0;

	/**
	 * Class Constructor
	 *
	 * @param $db string
	 * @param $table string
	 * @return null
	 */
	public function __construct($db, $table = 'session'){
		$this->db = $db;
		$this->table = $table;
	}
	
	/**
	 * Open Session
	 *
	 * @param $path string
	 * @param $name string
	 * @return null
	 */
	public function open($path, $name){
		$this->ses_life = ini_get('session.gc_maxlifetime');
	}
	
	/**
	 * Close Session
	 *
	 * @param $ses_id string
	 * @return null
	 */
	public function close(){
		$this->gc();
	}
	
	/**
	 * Read Session from DB
	 *
	 * @param $ses_id string
	 * @return string
	 */
	public function read($ses_id){
		global $db;
		
		$session_sql = 'SELECT * FROM `' . $this->table . '` WHERE ses_id = \'' . $ses_id . '\' LIMIT 1';
		$session_res = $db->Query($session_sql, $this->db, false);
		if (!$session_res){
			return '';
		}
		$session_num = $db->NumRows($session_res);
		if ($session_num > 0){
			$session_row = $db->FetchArray($session_res);
			$ses_data = unserialize($session_row['ses_value']);
			$this->ses_start = $session_row['ses_start'];
			$this->ses_id = $ses_id;
			return $ses_data;
		}else{
			return '';
		}
	}
	
	/**
	 * Write Session data to DB
	 *
	 * @param $ses_id string
	 * @param $data string
	 * @return bool
	 */
	public function write($ses_id, $data) {
		global $db;
		
		// If the $db object is not on the page any more recreate it.
		if (!is_object($db)){
			$db = new DB;
			$db->Connect();
		}
		
		if(!isset($this->ses_start))
			$this->ses_start = time();

		$session_sql = 'SELECT * FROM `' . $this->table . '` WHERE `ses_id` = \'' . $ses_id . '\' LIMIT 1';
		$res = $db->Query($session_sql, $this->db, false);
		
		if($db->NumRows($res) == 0) {
			$type = 'insert';
			$extra = '';
			$info = array('ses_id' => $ses_id, 'last_access' => time(), 'ses_start' => $this->ses_start, 'ses_value' => serialize($data));
		}else{
			$type = 'update';
			$extra = '`ses_id` = \'' . $ses_id . '\'';
			$info = array('last_access' => time(), 'ses_value' => serialize($data));
		}

		// Do the Operation
		$session_res = $db->Perform($this->table, $info, $type, $extra, $this->db, false);
		if (!$session_res)
			return false;
		
		return true;
	}
	
	/**
	 * Destroy the session
	 *
	 * @param $ses_id string
	 * @return null
	 */
	public function destroy($ses_id){
		global $db;
		
		$session_sql = 'DELETE FROM `' . $this->table . '` WHERE `ses_id` = \'' . $ses_id . '\' LIMIT 1';
		$res = $db->Query($session_sql, $this->db, false);
		
		return true;
	}
	
	/**
	 * Garbase Collector
	 *
	 * @return bool
	 */
	public function gc(){
		global $db;
		
		$ses_life = time() - $this->ses_life;
		$session_sql = 'DELETE FROM `' . $this->table . '` WHERE `last_access` < ' . $ses_life . '';
		$session_res = $db->Query($session_sql, $this->db, false);

		if (!$session_res)
			return false;
			
		return true;
	}
}
?>