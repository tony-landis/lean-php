<?php

/**
 * State 
 * 
 * The base class for handling user state in Lean PHP.
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package State
 */
 
/**
 * Simple State Management Singleton
 * 
 * @author Tony Landis
 * @package State
 */
class sstate
{  
	public static $timeoutSeconds = 86400; // 1 day session
	public static $sessionName = 'PHPSESSID'; // default session name
	  
	/**
	 * Global singleton
	 *
	 * @var sparam
	 */
	static private $StateSingleton;
	
	/**
	 * Create the singleton
	 *
	 * @param string $ImplementationStateClass Name of state class extending base sstate class
	 * @return sparam
	 */
	static function & instance($ImplementationStateClass=false) 
	{
		if(!self::$StateSingleton)  {
			if($ImplementationStateClass)
				self::$StateSingleton = new $ImplementationStateClass();
			else
				self::$StateSingleton = new sstate();
		}
		
		// return the instance
		return self::$StateSingleton;
	}	
		
	public function __construct() { 
		if(!isset($_SESSION))
		{
			session_start(); 
			session_set_cookie_params(self::$timeoutSeconds);
		}
	}
	
	/** Get the current user's login status */
	public static function getLogin() { return @$_SESSION['logged']; }
	
	/** Set the logged in status */
	public static function setLogin($status) { $_SESSION['logged'] = $status; }
  
	/** Get the current session Id */
	public static function getSessionId() { return session_id(); }
	  
	/** Get the session values */
	public static function & getAll() { return $_SESSION; }
	
	/** Set one session value */
	public static function set($name, $value) { $_SESSION["$name"]=$value; }
	 
	/** Get the value of a session parameter  */
	public static function get($name) { return @$_SESSION["$name"]; }
	
	/** Destroy the current session  */ 
	public static function destroySession() { session_destroy(); } 

	/** 
	 * Login user redirect 
	 */
	public static function setLoginForce(&$view) { 
		$parms = sparam::getAll();  
		$str ='';
		foreach($parms as $p => $v) {
			if(!in_array($p, array('module','method','view'))) {
				if(empty($str)) $str='?'; else $str.='&';
				$str .= "$p=". $v;
			}
		}
		$style = (sparam::get('style')) ? "&style=" . sparam::get('style') : '';		
		$view->setRedirect(ssetup::get('url') . '/user/login.htm?redir=' . base64_encode(ssetup::get('url') . '/'. sparam::get('module') .'/'. sparam::get('method') .'.'. sparam::get('view') . $str ) . $style); 
	}
}
?>