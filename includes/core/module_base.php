<?php

/**
 * Controller
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Controller
 */

/**
 * Base Controller Class - everyting exposed to the web must extend this class
 *
 * @package Controller 
 */
class Module_Base
{
	public $module; 
	public $method;
	protected $doScaffold=array(); // add, edit, search, delete
		 
	/** 
	 * DB Scaffolding to Search Records 
	 */
	protected function doSearch() 
	{		
		if(!in_array('search', $this->doScaffold)) {
			sview::setAlert("Search scaffolding not enabled for this class.");  
			return false;
		}
		
		include_once LEAN_PHP_DIR . 'includes/decorator/db_select.php'; 
		$d=new DB_Select($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), true); 
		
		$helperResult = true;
		if(is_callable(array($this, 'doSearchHelper')))
			$helperResult = $this->doSearchHelper($d);
		
		if($helperResult !== false)
			return $d->processForView(sview::instance());
	}	
	
	/** 
	 * DB Scaffolding to Add Single Record  
	 */
	protected function doAdd() 
	{  
		if(!in_array('add', $this->doScaffold)) {
			sview::setAlert("Add scaffolding not enabled for this class.");  
			return false;
		}
				
		include_once LEAN_PHP_DIR . 'includes/decorator/db_insert.php';
		$d=new DB_Insert($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), true);
		$d->setValues(sparam::getAll());
		
		$helperResult = true;
		if(is_callable(array($this, 'doAddHelper')))
			$helperResult = $this->doAddHelper($d);
			
		if($helperResult !== false)
			return $d->validateAndSave(sview::instance());
	}
	
	/**
	 * DB Scaffolding to Edit Single Record
	 */
	protected function doEdit($id) 
	{  
		if(!in_array('edit', $this->doScaffold)) {
			sview::setAlert("Edit scaffolding not enabled for this class.");  
			return false;
		}
			
		include_once LEAN_PHP_DIR . 'includes/decorator/db_update.php';
		$d=new DB_Update($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), true); 
		$d->updateByPk($id);
		$d->setValues(sparam::getAll());
		 		
		$helperResult = true;
		if(is_callable(array($this, 'doEditHelper')))
			$helperResult = $this->doEditHelper($d);
			
		if($helperResult !== false)
			return $d->validateAndSave(sview::instance());
	} 
	
	/**
	 * DB Scaffolding to Delete Single Record
	 */
	protected function doDelete($id) {
		include_once LEAN_PHP_DIR . 'includes/decorator/db_delete.php';
		$d=new DB_Delete($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), true);
		return $d->deleteByPK($id); 
	}
	
	/**
	 * DB Scaffolding to Edit/Insert multiple records 
	 */
	protected function doEditMulti($json) {
		if(empty($json)) return false;
		
		if(!is_callable('json_encode'))
		{
			require LEAN_PHP_DIR . 'includes/class/view/json.php';
			$jsonObj = new Services_JSON();
			$json = $jsonObj->decode(urldecode(stripslashes($json)));
		} else {
			$json = json_decode(urldecode(stripslashes($json)));
		} 
		
		if(!empty($json))
		{ 
			/* validate scaffolding available for editMulti */
			$allowUpdate = (in_array('editMulti', $this->doScaffold)) ? true : false;  
			if($allowUpdate) include_once LEAN_PHP_DIR . 'includes/decorator/db_update.php';
			
			/* validate scaffolding available for deleteMulti */
			$allowInsert = (in_array('addMulti', $this->doScaffold)) ? true : false;
			if($allowInsert) include_once LEAN_PHP_DIR . 'includes/decorator/db_insert.php';
				  
			$helperResult = true; 
			foreach($json as $row) { 
				if(isset($row->id) && $row->id>0)  { /* update */
					if(!$allowUpdate) return sview::setAlert("editMulti scaffolding not enabled for this class."); 
					$d=new DB_Update($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), $row);
					$d->updateByPk($row->id);
					$d->setValues($row);
					$d->dropFieldsEmpty(); 
					  
					if(is_callable(array($this, 'doEditMultiHelper'))) $helperResult = $this->doEditMultiHelper($d);	
				} else { /* insert */
					if(!$allowInsert) return sview::setAlert("addMulti scaffolding not enabled for this class."); 
					$d=new DB_Insert($this->module, sdb::instance(), ssetup::instance(), sparam::instance(), $row);
					$d->setValues($row);
					if(is_callable(array($this, 'doAddMultiHelper'))) $helperResult = $this->doAddMultiHelper($d);					
				} 
				  
				// save
				if($helperResult !== false) $d->validateAndSave(sview::instance());
			}
		}
		return true;
	} 
	
	/**
	 * Development Scaffolding (Ext, Data Dictionary)
	 */
	public function dev($action=false) {  
		include 'includes/utility/dev-handler.php';
		dev_handler::doAction($action, $this->module); 
	}
	
	/**
	 * Instantize another module
	 * 
	 * @param string $module
	 * @todo move all methods called by this method to a helper class and retire this method
	 */ 
	protected function newModule($module) { 
		include_once PROJECT_DIR . 'modules/' . $module.'/'.$module.'.php';
		$obj = new $module();
 		return $obj;				
	} 

	/* Delegate method to call the appropriate method needed to handle task */
	public function callMethod($method) 
	{  		 
		// auth caching
		$this->method = $method;
		$acl = sstate::get('acl');
		$apc = ssetup::get('apc'); 
		$apc_cache = false;
		if($apc) { 
			// try accessing method params and protected/public from memory
			$md5 = md5($apc . $this->module . $this->method);
			if($apc_cache =& apc_fetch($md5)) { 
				$mc = $apc_cache;
					
			} 
		}
			
		// this method exists in the array?
		if(!isset($mc[$method])) {
			$mc[$method] = $this->isAllowed();
		}

		// something wrong here
		//if(!isset($mc[$method][0])) die("something weird happened");
		 
		// protected method, user logged in
		if($mc[$method][0] === 1 && sstate::getLogin()) 
		{			 
			// unknown acl, check now
			if(!isset($mc[$method][2][$acl])) { 
				#echo "<BR><BR>Checking ACL from disk";				
				$mc[$method][2][$acl] = $this->getModuleACLAuth(); 
			}
			
			if(!$mc[$method][2][$acl]) {
				// know acl, not permitted here
				sview::setAlert("The requested resource is beyond your authentication level.");
				sview::setForce('def','unauthorized'); 
				return false;				
			}			
		} elseif ($mc[$method][0] === 2) { 
			// not protected, continue without login/acl check 
		} else {
		
			
			/* call constructor */
			if(method_exists($this->module, 'construct'))
			{ 
				call_user_func_array(array(&$this, 'construct'), false);
			}	
			else
			{
				// protected method, login required
				sstate::setLoginForce(sview::instance());
			} 
			return false; 			
		}
		
		// update cache? 
		if($apc && $mc != $apc_cache) 
		{
			// try updating the cache with the full info
			apc_store($md5, $mc, 86400*7);  		
		}		 
				
		/* call constructor */
		if(method_exists($this->module, 'construct')) call_user_func_array(array(&$this, 'construct'), array());	
		
		if(isset($mc[$method][0]))
		{
			$args = array();
			if(isset($mc[$method][1])) {
				foreach($mc[$method][1] as &$v) { 
					if(isset(sparam::instance()->{$v[0]})) {
						array_push($args, sparam::instance()->{$v[0]});
					} else {
						array_push($args, $v[1]);
					}
				} 
			}
			sview::setMethodResult( call_user_func_array(array(&$this, $this->method), $args) );
		}
			
		/* call destructor */
		if(method_exists($this->module, 'destruct')) call_user_func_array(array(&$this, 'destruct'), false);	 	
			
	}
	
	/**
	 * Get auth for current module/method
	 * By default, all non-public methods are blocked, and must be specifically allowed
	 * 
	 * @return boolean
	 */
	public function getModuleACLAuth() { 
		if($this->module==='default' && $this->method==='index')  return true; /* never block */  
		@$acl =& sstate::get('acl'); /* get the acl for user */
		@$acl_methods =& ssetup::get('acl_methods');
		@$rules =& $acl_methods["$acl"]['allow']; /* get the ALLOW rules for this ACL */
		if(!is_array($rules)) return false;		
		foreach($rules as &$r) 
			if ( ($r[0] === '*' || $r[0] == $this->module || @preg_match($r[0], $this->module))  &&  
				 ($r[1] === '*' || $r[1] == $this->method || @preg_match($r[1], $this->method)) 
			) return true;   		
		return false;	 
	}
	
	
	/* check if method is Private/Protected/Public */
	private function isAllowed() {
		  
		/* Method private or non-existant exists */
		if(!is_callable(array($this, $this->method))) return false;	   
		 
		/* Module level ACL */
		try {
			
			$acl_modules = ssetup::get('acl_modules');  
			if(!strcmp($this->module, 'default') && is_array($acl_modules)) {	
				/* acl modules defined? */
				if(isset($acl_modules['block'])) { 
					/* anything blocked? */
					if(!empty($acl_modules['block']) && preg_match($modules['block'], $this->module)) { 
						/* all modules or this module on acl block */
						if(!$acl_modules['allow'] || !preg_match($acl_modules['allow'], $this->module)) { 
							/* not on allowed acl list */
							throw new Exception();
						}
					}
				}
			}
		} catch (Exception $e) {
			sview::setAlert("Module Not Callable");
			return false;
		}
     
	 	if(method_exists('ReflectionMethod', 'isProtected')) {		/* Reflection Available */  
		 
	 		$ref = new ReflectionMethod($this->module, $this->method);
	 		  
	 		// scope
	 		$isProtected=false;
	 		if($ref->isPrivate())
	 			return false;
	 		elseif($ref->isProtected())
	 			$isProtected=1;
	 		else
	 			$isProtected=2; 
	 		
	 		// parameters
	 		$params=array();
	 		$ref_params=$ref->getParameters();
	 		foreach($ref_params as $i=>$p) {
	 			$n=$p->getName();
	 			if($p->isDefaultValueAvailable())
	 				array_push($params, array($n, $p->getDefaultValue()));
	 			else
	 				array_push($params, array($n, ''));
	 		}

	 		// return
	 		return array($isProtected, $params);
 
	 	} else {	/* Use scary file regex parse on file/class - better have APC installed or this will be sloooooow! */
	 		  
			$regex = '/(private|public|protected)?[ ]{1,}?function[ ]{1,}'.$this->method.'([ ]{1,})?\(([a-zA-Z0-9\=\'\"\,\&\$\_ ]{1,})?\)/';
			if(preg_match($regex, file_get_contents(PROJECT_DIR . 'modules/' . $this->module . '/' . $this->module .'.php'), $p)) {
			  					
				// scope
				$isProtected=false;
				if($p[1] === 'private')
					return false;
				elseif($p[1] === 'protected')
					$isProtected=1;
				else 
					$isProtected=2;
	  
				// parameters
				$params=array(); 
				$explode = explode(',', $p[3]);
				foreach($explode as $p) { 
					$p = explode('=', $p);
					$def = '';
					$p_count=$i<count($p); for($i=1; $p_count; $i++) $def .= ereg_replace("[\'\"]", '', $p[$i]);
					if(!empty($p[0])) array_push($params, array(trim(ereg_replace('[$&]', '', $p[0])), $def));
				}
				  
				return array($isProtected, $params);
				
			} else {
				return false; /* no match for called method */
			} 
	 	}
		return true;		
	}  
}
?>