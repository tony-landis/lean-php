<?php

/**
 * Singleton Setup  
 * 
 * The base class for handling all the project setup functionality in Lean PHP.
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Setup
 */

/**
 * The Lean PHP Setup Singleton
 * 
 * @author Tony Landis
 * @package Setup
 */
class ssetup
{
	/**
	 * Global singleton
	 *
	 * @var sparam
	 */
	static private $SetupSingleton;
	
	/**
	 * Create the singleton
	 *
	 * @return sparam
	 */
	static function & instance($ImplementationSetup=false) 
	{
		if(!self::$SetupSingleton)  {
			self::$SetupSingleton = new $ImplementationSetup(); 
						 
			// set the import paths
			define('LEAN_PHP_DIR', self::$SetupSingleton->get('lean_php_dir'));
			define('PROJECT_DIR',  self::$SetupSingleton->get('project_dir'));
			ini_set ("include_path", get_include_path() . PATH_SEPARATOR .
				 LEAN_PHP_DIR . // Lean PHP Base Dir
				 PATH_SEPARATOR . 
				 PROJECT_DIR    // Project Base Dir (where /views and /modules exit) 
			);
			
			// set the timezone
			if($tz = self::$SetupSingleton->get('timezone'))
			{
				date_default_timezone_set($tz);
			}
		}
		
		// return the instance
		return self::$SetupSingleton;
	}		
	
	/**
	 * Get the value of a setup parameter
	 *
	 * @param string $arg
	 */
	public static function get($arg) {
		if(isset(self::$SetupSingleton->$arg)) return self::$SetupSingleton->$arg;
		return null;
	}
	
	/**
	 * Set the value of a setup parameter
	 *
	 * @param string $arg
	 * @param mixed $val 
	 */
	public static function set($arg, $val) {
		if(!is_string($arg)) return false;
		self::$SetupSingleton->$arg =& $val;
	}
	
	/**
	 * Get the text formatted value of a date
	 */
	public static function getDateText($date, $format=false)
	{
		if(!$format) $format = self::get('date_text_format');
		return date($format, strtotime($date));	
	}
	
	/* the cookie name */
	public $cookie_name;
	
	/* cookie expiration, in minutes */
	public $cookie_expire=0; 
	
	/* module acls */
	public $acl_modules=false;
		
	/* default view */
	public $default_view = 'htm';
	
	/* default module */
	public $default_module = 'default';
	
	/* default method */
	public $default_method = 'index';
}	 
?>