<?php

/**
 * Singleton Params
 * 
 * The base class for handling all the parameter passing functionality in Lean PHP.
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Param
 */

/**
 * The Lean PHP DB Singleton
 * 
 * @author Tony Landis
 * @package Param
 */
class sparam
{
	/**
	 * Global singleton
	 *
	 * @var sparam
	 */
	static private $ParamSingleton;
	
	/**
	 * Create the singleton
	 *
	 * @return sparam
	 */
	static function & instance() 
	{
		if(!self::$ParamSingleton) 
		{
			self::$ParamSingleton = new sparam();
			
			// set the initial parameters
			global $argv;
			if(isset($argv)) 
			{
				/* CLI */
						 
				$params = array(); 
				for($i=4; $i<count($argv); $i++) {
					$pa = explode('=', $argv[$i]); 
					@$params["{$pa[0]}"] =& $pa[1];
				}    
				
				self::setFromArray($params);
				self::set('module', $argv[1]);
				self::set('method', $argv[2]);
				self::set('view',   $argv[3]);
				   
			} else {	
				
				/* POST / GET */
				global $_GET,$_POST;		 
				self::setFromArray(array_merge($_POST, $_GET));
			}			
			
		}
		
		// return the instance
		return self::$ParamSingleton;
	}
	
	// set the singleton from a reference to another object
	static function setInstance($instance) 
	{
		self::$ParamSingleton = clone $instance;
	}
	
	// destroy the current singleton instance
	static function destroyInstance()
	{
		self::$ParamSingleton=false;
	}
	
	/**
	 * Set a params value
	 *
	 * @param string $arg
	 * @param mixed $val
	 */
	public static function set($arg,$val) {
		self::$ParamSingleton->$arg =& $val;
	}
	
	/**
	 * Get a params value
	 *
	 * @param string $arg
	 * @return mixed
	 */
	public static function get($arg) {
		if(isset(self::$ParamSingleton->$arg)) 
			return self::$ParamSingleton->$arg;
		else
			return null;
	}
	
	/**
	 * Get all params
	 * 
	 * @return StandardObject
	 */
	public static function & getAll() {
		return self::$ParamSingleton;
	}
	
	/**
	 * Set muliple param values from array keys
	 *
	 * @param array $args
	 */
	public static function setFromArray($args) {
		if(!is_array($args)) return;
		foreach($args as $k=>$v) 
			self::$ParamSingleton->set($k, $v);
	}

    public function getUrlString($ignoreKeys=false)
    {
        if(!is_array($ignoreKeys)) $ignoreKeys=array();
        array_push($ignoreKeys, 'module');
        array_push($ignoreKeys, 'method');
        array_push($ignoreKeys, 'view');

        $p='';
        $params = (array) self::getAll();
        foreach($params as $k=>$v) if(!in_array($k, $ignoreKeys)) $p .= "&$k=" . urlencode($v);
        return $p;
    }
}
?>