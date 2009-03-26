<?php

/**
 * Singleton Database
 * 
 * The base class for handling all the database functionality in Lean PHP.
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Database
 */

/**
 * The Lean PHP DB Singleton
 * 
 * @author Tony Landis
 * @package Database
 */
class sdb 
{  
    /**
     * The singleton instance 
     */
    static private $PDOInstance;
    public $debug=false;
    public $readonly=false;
     
  	/**
  	 * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
  	 * 
  	 * @param string $dsn The full DSN, eg: mysql:host=localhost;dbname=testdb
  	 * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers.
  	 * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers.
  	 * @param array $driver_options A key=>value array of driver-specific connection options
  	 * 
  	 * @return PDO
  	 */
    public function __construct($dsn, $username=false, $password=false, $driver_options=false) 
    {
        if(!self::$PDOInstance) { 
	        try {
			   self::$PDOInstance = new PDO($dsn, $username, $password, $driver_options);
			   self::debug(false);
               self::readonly(false);
			} catch (PDOException $e) { 
			   die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
			}
    	}
      	return self::$PDOInstance;    	    	
    }

    /**
     * Get the current PDO instance
     *
     * @return PDO
     */
    static function & instance()
    {
    	return self::$PDOInstance;
    }
	 
  	/**
  	 * Initiates a transaction
  	 *
  	 * @return bool
  	 */
	public static function beginTransaction() {
		return self::$PDOInstance->beginTransaction();	  
	}
        
	/**
	 * Commits a transaction
	 *
	 * @return bool
	 */
	public static function commit() {
		return self::$PDOInstance->commit();
	}
	
	/**
	 * Do debugging?
	 *
	 * @param bool $debugging 
	 */
	public static function debug($debug) {
		self::$PDOInstance->debug = (bool) $debug;
		if(self::$PDOInstance->debug) {
			self::$PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// firebug debugging!
			require_once('includes/class/firephp/fb.php');	
		} else {
			self::$PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		} 
	}

    public static function readonly($state)
    {
        self::$PDOInstance->readonly = (bool) $state;
    }

    /**
     * Get an array with the SQL EXPLAIN results for debugging purposes
     * 
     * @param string $statement
     * @return array
     */
    public static function explain($statement)
    { 
        if(preg_match("/^(explain|insert|update|delete)/", trim($statement))) return false;
        $rs=self::$PDOInstance->query("EXPLAIN " . $statement);
        if(!$rs) return false;
        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }
	
	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 * 
	 * @return string 
	 */
    public static function errorCode() {
    	return self::$PDOInstance->errorCode();
    }
    
    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public static function errorInfo() {
    	return self::$PDOInstance->errorInfo();
    }
    
    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public static function getAttribute($attribute) {
    	return self::$PDOInstance->getAttribute($attribute);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public static function getAvailableDrivers() {
    	return Self::$PDOInstance->getAvailableDrivers();
    }
    
    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
	public static function lastInsertId($name=false) {
		return self::$PDOInstance->lastInsertId($name);
	}
        
   	/**
     * Prepares a statement for execution and returns a statement object 
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj returned  
     * @return PDOStatement
     */
    public static function prepare($statement, $driver_options=false) {
        if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
    	if(!$driver_options) $driver_options=array();
		if(self::$PDOInstance->debug) {
            fb($statement,'sdb::prepare',FirePHP::LOG);
        }
    	return self::$PDOInstance->prepare($statement, $driver_options);
    }
    
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public static function query($statement) {
        if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
		try {
			if(self::$PDOInstance->debug) {
                fb($statement,'sdb::query',FirePHP::LOG);
                fb( self::explain($statement), 'EXPLAIN', FirePHP::LOG);
            }
			return self::$PDOInstance->query($statement);
		} catch(PDOException $e) {
			if(self::$PDOInstance->debug) fb($e->getMessage(),'sdb::query',FirePHP::ERROR);
		}
    }
    
    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public static function all($statement) {
        //if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
		try {
			if(self::$PDOInstance->debug) {
                fb($statement,'sdb::all',FirePHP::LOG);
                fb(self::explain($statement), 'EXPLAIN', FirePHP::LOG);
            }
			if($o = self::$PDOInstance->query($statement))
				return $o->fetchAll(PDO::FETCH_ASSOC);
			else
				return false;			
		} catch(PDOException $e) { 
			if(self::$PDOInstance->debug) fb($e->getMessage(),'sdb::all',FirePHP::ERROR);
		}  
    }
    
    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public static function row($statement) {
        //if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
		try {
			if(self::$PDOInstance->debug) {
                fb($statement,'sdb::row',FirePHP::LOG);
                fb(self::explain($statement), 'EXPLAIN', FirePHP::LOG);
            }
    		if($o = self::$PDOInstance->query($statement))
    			return $o->fetch(PDO::FETCH_ASSOC);
    		else 
    			return false; 
		} catch(PDOException $e) { 
			if(self::$PDOInstance->debug) fb($e->getMessage(),'sdb::qeuryFetchRowAssoc',FirePHP::ERROR);
		}     	
    }
    
    /**
     * Execute query and select one column only 
     *
     * @param string $statement
     * @return mixed
     */
    public static function col($statement) {
        if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
		try {
			if(self::$PDOInstance->debug) {
                fb($statement,'sdb::col',FirePHP::LOG);
                fb(self::explain($statement), 'EXPLAIN', FirePHP::LOG);
            }
    		if($o = self::$PDOInstance->query($statement))
    			return $o->fetchColumn();
    		else 
    			return false; 
		} catch(PDOException $e) { 
			if(self::$PDOInstance->debug) fb($e->getMessage(),'sdb::col',FirePHP::ERROR);
		}      	
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     */
    public static function exec($statement) {
        if(self::$PDOInstance->readonly && preg_match("/^(insert|update|delete)/",trim($statement))) return false;
    	try {
            if(self::$PDOInstance->debug) {
                fb($statement,'sdb::exec', FirePHP::LOG);
                fb(self::explain($statement), 'EXPLAIN', FirePHP::LOG);
            }
            return self::$PDOInstance->exec($statement);
        } catch(PDOException $e) {
            if(self::$PDOInstance->debug) fb($e->getMessage(), 'sdb::exec', FirePHP::ERROR);
        }
    }
    
    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public static function quote($input, $parameter_type=0) {
    	return self::$PDOInstance->quote($input, $parameter_type);
    }
    
    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public static function rollBack() {
		if(self::$PDOInstance->debug) fb('sdb::rollBack',FirePHP::LOG);
    	return self::$PDOInstance->rollBack();
    }      
    
    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public static function setAttribute($attribute, $value  ) {
    	return self::$PDOInstance->setAttribute($attribute, $value);
    } 
}
?>