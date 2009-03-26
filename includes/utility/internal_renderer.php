<?php

/**
 * Render views to the internal buffer for use internally
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */ 

/**
 * A utility class to call a specific module/method from within the code and return the rendered view as a string.
 * 
 * @package View 
 */
class internal_renderer
{
	public $module;
	public $method;
	public $view;
	public $params;
	
	public $Obj;
	
	public $oldErrorLevel;
	public $oldSparam;
	public $oldSview;

	public function __construct($module, $method, $view, $params=false)
	{
		$this->module = $module;
		$this->method = $method;
		$this->view   = $view;
		$this->params = $params;
		
		// create a clone of the current params singleton 
		$this->oldSparam = clone sparam::instance();
		// destroy the current params singleton
		sparam::destroyInstance();
		// get a new param singleton
		sparam::instance();
		// push the parameters to the param instance
		sparam::setFromArray($params);
		
		// create a clone of the current view singleton
		$this->oldSview = clone sview::instance();
		// destroy the current view singleton
		sview::destroyInstance();
		// get a new view singleton
		View_Factory($view, $module, $method);
		
		// load the module
		require_once 'modules/'. $module . '/' . $module . '.php';
		$this->Obj = new $module();
	}
	
	public function render()
	{
		ob_start();
		$this->Obj->callMethod($this->method);
		sview::instance()->render(true); 
		return ob_get_clean();				
	}
	
	
	public function destroy() 
	{
		// destroy the current param instance
		sparam::destroyInstance();
		// reuse the origonal param instance
		sparam::setInstance($this->oldSparam);
		// destroy our copy
		unset($this->oldSparam);
		
		// destroy the current view instance
		sview::destroyInstance();
		// reuse the origonal view instance
		sview::setInstance($this->oldSview);
		// destroy our copy
		unset($this->oldSview);
	}
}
?>