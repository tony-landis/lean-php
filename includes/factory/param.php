<?php

/**
 * Param Factory
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Param
 */

/**
 * Get the user defined keys from POST,GET, or CLI
 *
 * @package Param
 * @return Object sparam
 */
function Param_Factory() {
	 
	/* CLI */ 
	global $argv,$_GET,$_POST;
	if(isset($argv)) {		 
		$params = array(); 
		for($i=4; $i<count($argv); $i++) {
			$pa = explode('=', $argv[$i]); 
			@$params["{$pa[0]}"] =& $pa[1];
		}    
		$p =& new ParamClass($params);
		@$p->module =& $argv[1];
		@$p->method =& $argv[2];
		@$p->view   =& $argv[3]; 
		return $p;
		
	/* POST / GET */
	} else {			 
		return new ParamClass(array_merge($_POST,$_GET));
	}
}

/**
 * Put the user defined keys into an object
 *
 */
class ParamClass { 
	function ParamClass($args) {
		if(!is_array($args)) return;
		foreach($args as $k=>$v) $this->$k = $v;
	}
}
?>