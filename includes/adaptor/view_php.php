<?php

/**
 * PHP View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * PHP View Renderer
 *
 * @package View 
 */
class View_PHP extends sview 
{ 
	public function __construct() {
		parent::$type = 'php';
	}
	
	public static function render() 
	{   
		parent::render();
		 
		/* make nodes accessible */
		$nodes = self::getNodes();  
		if(!empty($nodes)) {
			foreach($nodes as $key => $n) { 
				$$key = $n;
			}
		}  
		
		$theme_dir = (ssetup::get('theme_dir')) ? ssetup::get('theme_dir') : false; 
		$style  = sparam::get('style'); 
		$module =& self::getModule();
		$method =& self::getMethod();
		
		if($theme_dir && $style != 'ajax'  ) 
			include ($theme_dir . 'head.php');	
					  
		include (PROJECT_DIR . 'views/php/' . $module . '/' . $method . '.php');
				 
		if($theme_dir   && $style != 'ajax'  ) 
			include ($theme_dir . 'foot.php');
		 
	}
}
?>