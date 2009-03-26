<?php

/**
 * JSON View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * JSON View Renderer
 *
 * @package View 
 */
class View_JSON extends sview 
{ 
	 
	public function __construct() {
		parent::$type = 'json';
	}  
 
	/**
	 * Render the output in JSON
	 */
	public static function render() {  

		parent::render();
		
		// set header for jquery/ext?
		header("X-JSON: body");
 
		/* redirect */
		if(isset(self::$redirect)) { 
			$str='';   
			if(isset(self::$redirectParams)) {
				foreach(self::$redirectParams as $p=>$v) { 
					if(empty($str)) $str='?'; else $str.='&';
					$str.="$p=". urlencode($v); 
				}
			}
			if(!eregi('^http', self::$redirect)) { 		
				self::setNode('redirect', ssetup::get('url')."/".self::$redirect.".htm".$str);		 
			} else {
				self::setNode('redirect', self::$redirect . $str);
			} 
		}

		$encoded = '';
		if(!is_callable('json_encode'))
		{
			require LEAN_PHP_DIR . 'includes/class/view/json.php';
			$jsonObj = new Services_JSON();
			$encoded = $jsonObj->encode(array("result" => self::getMethodResult(), "nodes" => self::getNodes()));
		} else {
			$encoded = json_encode(array("result" => self::getMethodResult(), "nodes" => self::getNodes()));
		}
		
		// callback defined?
		$jsonCallback = sparam::get('jsonCallback'); 
		if(!empty($jsonCallback))
		{
			// callback defined? (encapsulate in javascript function call)
			$pass = sparam::get('jsonPassback');  
			print $jsonCallback . "(" . $encoded . ((empty($pass)) ? '' : ",". stripslashes($pass)) .
			")";
		} else {
			// return raw json
			echo $encoded;
		}  
		
		
	}
}
?>