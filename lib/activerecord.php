<?php
/**
 * Active Record Class
 * Extends the DbTemplate class to give relational ability to DB tables
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class ActiveRecord extends DbTemplate {
	/**
	 * @var array
	 */
	private $belongs_to = array();
	/**
	 * @var array
	 */
	private $has_one = array();
	/**
	 * @var array
	 */
	private $has_many = array();
	/**
	 * @var array
	 */
	private $has_and_belongs_to_many = array();
	/**
	 * @var array
	 */
	private $associations = array();
	
	/**
	 * Active Record Constructor
	 * 
	 * @param string $table Table name
	 * @param string $database Database name
	 * @return bool
	 */
	public function __construct($table, $database){
		parent::__construct($table, $database);
	}
	
	/**
	 * Dynamic Function Call
	 * Used to call connections directly
	 * 
	 * @param string $method Class name
	 * @param string $args Any variables
	 * @return bool
	 */
	public function __call($method, $args){
		// Make sure the class called is valid
		if (!class_exists($method))
			echo 'No Class Exists';
		
		// Make sure the class called has an association
		if (!isset($this->associations[$method]))
			echo 'No Association Exists';
		
		// Require there is a primary key set
		if ($this->GetPrimary() == '')
			die('No primary key set');
		
		// Get the method these two classes are joined
		$join_type = $this->associations[$method];
		
		// Localize the config info
		$join_info = $this->$join_type;
		
		// See if there is a through class
		$through = $join_info[$method]['through'];
		
		// Create the relationship object to be returned
		$myObject = new $method;
		
		// Set the primary key if there is one
		$myObject->SetValue($this->primary, $this->GetPrimary());
		
		// Join the classes with the through
		if ($through != ''){
			$myThrough = new $through;
			$myThrough->SetValue($this->primary, $this->GetPrimary());
			$this->Join($myThrough, $this->primary, 'LEFT');
		}
		
		// Join the relationship object with the through object
		$myObject->Join($myThrough, $myObject->primary, 'LEFT');

		// Return the compiled Object
		return $myObject;
	}
	
	/**
	 * Count
	 * Gets the number of elements related to the relationship
	 * 
	 * @return int Items in the relationship
	 */
	public function Count(){
		// Make sure there is a primary key set
		// @todo Not sure how to do this from here
				
		// Join all the tables and determine the count
		$this->GetList('count');

		// Return the count		
		return $this->results['count'];
	}
	
	/**
	 * Items
	 * Gets an array of the relationship items
	 * 
	 * @param array $display Fields to be returned
	 * @return array Of relationship items
	 */
	public function Items($fields=array(), $order_by='', $sort='', $offset='', $limit=''){
		// Make sure there is a primary key set
		// @todo Not sure how to do this from here
				
		// Join all the tables and determine the count
		$this->GetList($fields, $order_by, $sort, $offset, $limit);
		
		// Return the count		
		return $this->Results();
	}

	
	/**
	 * Belongs To
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return int Items in the array
	 */
	public function BelongsTo($class){
		return array_push($this->belongs_to, $class);
	}
	
	/**
	 * Has One
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return int Items in the array
	 */
    public function HasOne($class){
		return array_push($this->has_one, $class);
    }
    
    /**
	 * Has Many
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return int Items in the array
	 */
    public function HasMany($class, $through=''){
    	$this->associations[$class] = 'has_many';
		$this->has_many[$class] = array('class' => $class, 'through' => $through);
		return true;
    }
    
     /**
	 * Has and Belongs to Many
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return int Items in the array
	 */
    public function HasAndBelongsToMany($class){
		return array_push($this->has_and_belongs_to_many, $class);
    }
	
	/**
	 * Active Record Destructor
	 * 
	 * @return NULL
	 */
	public function __destruct() {
		// Get a list of all the class variables
		unset($this);
	}
}
?>