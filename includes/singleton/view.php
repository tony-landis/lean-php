<?php 

/**
 * Singleton View  
 * 
 * The base class for handling all the view functionality in Lean PHP.
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

/**
 * The Lean PHP View Signleton
 * 
 * @author Tony Landis
 * @package View
 */
class sview { 
 
	static $nodes; 
	static $type; 
	
	private static $redirect;
	private static $redirectParams;
	
	private $alert=false;
	private $error=false; 
	private $module; 
	private $method;  
	private $theme;
	private $methodResult;
	private $title;
	
	
	/**
	 * Global singleton
	 *
	 * @var sparam
	 */
	static private $ViewSingleton;
	
	/**
	 * Create the singleton
	 *
	 * @param string $ImplementationViewClass Name of class extending base sview 
	 * @return sparam
	 */
	static function & instance($module=false, $method=false, $ImplementationViewClass=false) 
	{
		if(!self::$ViewSingleton)  
		{
			if($ImplementationViewClass)
				self::$ViewSingleton = new $ImplementationViewClass($module,$method);
			else
				self::$ViewSingleton = new sview($module,$method);
				
			self::$ViewSingleton->module = $module;
			self::$ViewSingleton->method = $method;				
		}
		
		// return a reference the instance
		return self::$ViewSingleton;
	}
	
	// set the singleton from a reference to another object
	static function setInstance($instance) 
	{
		self::$ViewSingleton = clone $instance;
	}
	
	// destroy the current singleton instance
	static function destroyInstance()
	{
		self::$ViewSingleton=false;
	}
 
	/**
	 * Force the view to a different template
	 *
	 * @param string $module
	 * @param string $method
	 */
	static function setForce($module,$method) {
		self::$ViewSingleton->module=&$module; 
		self::$ViewSingleton->method=&$method;
	}	

	/**
	 * Set a node value
	 *
	 * @param string $node_name
	 * @param mixed $node_value
	 * @param string $node_parent
	 */
	static function setNode($node_name, $node_value, $node_parent=false) {
		if($node_parent)
			@self::$ViewSingleton->nodes->$node_name->$node_parent=&$node_value;
		else
			@self::$ViewSingleton->nodes->$node_name=&$node_value; 		
	}
	
	/**
	 * Convert a PDO prepared statement (query/prepare) into a view node
	 *
	 * @param string $node_name
	 * @param PDOStatement $stmt
	 * @param string $parent_node
	 */
	static function setPdoNode($node_name, &$PDOStatement, $parent_node=false) { 
		if(!$PDOStatement) return false; 
		self::$ViewSingleton->setNode($node_name, $PDOStatement->fetchAll(PDO::FETCH_ASSOC), $parent_node); 
	}
	
	/**
	 * Get one node
	 *
	 * @param string $node_name
	 * @return mixed
	 */
	static function getNode($node_name) { 
		if(!isset(self::$ViewSingleton->nodes->$node_name)) return false;
		return self::$ViewSingleton->nodes->$node_name; 
	} 
		
	/**
	 * Get all nodes 
	 *
	 * @return mixed
	 */
	static function getNodes() {
		if(!isset(self::$ViewSingleton->nodes)) return false; 
		return @self::$ViewSingleton->nodes; 
	}

	/**
	 * Render the nodes to the view format
	 *
	 */
	static function render() { 
		if(self::$ViewSingleton->getAlert()) self::$ViewSingleton->setNode('alert', self::$ViewSingleton->getAlert());
		if(self::$ViewSingleton->getError()) self::$ViewSingleton->setNode('error', self::$ViewSingleton->getError());

		/* redirect */
		if($r = self::getRedirect())
		{
			$o = (array) self::getRedirectParams();
			if(is_array($o)) {
				foreach($o as $p=>$v) {
					if(empty($str)) $str='?'; else $str.='&';
					$str.="$p=". urlencode($v);
				}
			}

			if(!eregi('^http', $r)) {
                $location = "../". $r .".htm{$str}";				
			} else {
				$location = $r ."{$str}";
			}
            
            if(empty($location)) return true;
            header("Location: $location", TRUE, 301);
			exit;
		}
	}	
  
	static function setModule($module) {self::$ViewSingleton->module=&$module;}	
	static function setMethod($method) {self::$ViewSingleton->method=&$method;} 
	static function setTheme($theme) {self::$ViewSingleton->theme=&$theme;}	 
	static function setMethodResult($r) { self::$ViewSingleton->methodResult=$r; } 
	static function setReturn($r) { self::$ViewSingleton->methodResult=$r; }
	static function setRedirect($resource) {  self::$ViewSingleton->redirect = $resource; }
	static function setRedirectParam($f, $v) { self::$ViewSingleton->redirectParams[$f] = $v; }
	static function setError($error) { self::$ViewSingleton->error .= $error; } 
	static function setAlert($alert) { self::$ViewSingleton->alert .= $alert; } 
	static function getAlert() { return self::$ViewSingleton->alert; }
	static function delAlert() { self::$ViewSingleton->alert = false; }
	static function getError() { return self::$ViewSingleton->error; }
	static function getMethodResult() { return (self::$ViewSingleton->methodResult == false) ? false:true; }
	static function & getModule() { return self::$ViewSingleton->module; }
	static function & getMethod() { return self::$ViewSingleton->method; } 
	static function getRedirect() { if(isset(self::$ViewSingleton->redirect)) return self::$ViewSingleton->redirect; }	
	static function getRedirectParams() { if(isset(self::$ViewSingleton->redirectParams)) return self::$ViewSingleton->redirectParams; }	
	
	
	/**
	 * process adodb recordset for output 
	 *
	 * @param string $node
	 * @param adodobojbect $rs
	 * @param string $parent
	 * 
	 * @todo check for any references to this function and remove,
	 */
	static function RsToNode($node,&$rs,$parent=false) {
		$arr=array();
		foreach ($rs as $r) $arr[]=$r;  
		self::$ViewSingleton->setNode($node,$arr,$parent);
	}		
}
?>