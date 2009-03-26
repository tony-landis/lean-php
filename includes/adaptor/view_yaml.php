<?php

/**
 * YAML View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * YAML View Renderer
 *
 * @package View 
 */
class View_YAML extends sview 
{ 
	 
	public function __construct() {
		parent::$type = 'yaml';
	} 
 
	/**
	 * Render the output in YAML
	 * 
	 * @todo: render objects
	 */
	public static function render() 
	{
		parent::render();
		
		if(is_callable('sych_load')) {
			print sych_load($this->getNodes());
		} else {
			require_once LEAN_PHP_DIR . 'includes/class/view/yaml.php';
			print Spyc::YAMLDump(self::getNodes(),4,60);
		}
	}
}
?>