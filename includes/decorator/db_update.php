<?php

/**
 * Scaffolding
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Scaffold
 */

include_once LEAN_PHP_DIR . 'includes/core/db_construct.php';

/**
 * Scaffoldnig for Database UPDATE commands
 *
 * @package Scaffold 
 */
class DB_Update extends DB_Construct_Base 
{	 
	public $getDefaultValues=false; 
	public static $conditions=array();
	 
	/* save the current record */
	public function save() {
		
		/* todo: fields, validation, convert */
		if(!is_object($this->construct)) {
			$this->error = "Construct not defined";
			return false;
		}	 
		 
		/* generate sql */
		$this->getUpdateFieldVals(); 
		if(!empty($this->fields)) {
			$this->sql = "UPDATE $this->table SET $this->fields WHERE (".$this->getConditions().")"; 
			$this->result = sdb::query($this->sql);
		} else {
			$this->result=true;
		} 
		
		/* error */
		if (!$this->result) {
			$this->error = print_r(sdb::errorInfo(),true);	
			return false;
		} else {
			/* save foreign keys */
			$this->updateForeign();		
		}
		unset($this->conditions);
		return true; 
	}
	
	/* save the foreign keys */
	public function updateForeign() 
    { 
		$this->getForeignAssoConstruct();
		if(!empty($this->foreign)) {			
			$id = $this->id;
			foreach($this->foreign as $foreign)
            {
				$fieldname = $foreign->getName(); 
				if(sparam::get($fieldname))
                {
					$remoteIds = explode(",", sparam::get($fieldname));
	
					/* delete old valuse */
					$foreign->setValue($id);
					$sql = $foreign->getDeleteSQL();
					sdb::query($sql);	 
	
					/* insert the new values */			
					foreach($remoteIds as $rid) {
						if(!empty($rid)) { 			
							$foreign->setRemoteValue($rid);
							/* insert current values */
							$sql = $foreign->getInsertSQL();
							sdb::query($sql);
						}
					}
				}
			}
		}
	}
	
	/* validate and save by primary key */
	public function updateByPk($id) {
		$this->setId($id);
		$this->conditions[] = $this->getPKFieldName() . " = ". sdb::quote($id);
	}
	
	/* validate and save by a column other than the primary key */
	public function updateByCol($column, $value) {
		$this->conditions[] = $column . " = ". sdb::quote($value);
	}
	
	/* get conditions for sql */
	public function getConditions() {
		$sql='';
		if(isset($this->conditions) && is_array($this->conditions)) {
			foreach($this->conditions as $c) {
				if(!empty($sql)) $sql .= ' AND ';
				$sql .= $c;
			}
		}
		return $sql;
	}
	
	/* validate and save handling interfaces with view object */
	public function validateAndSave(&$viewObject) { 
		#$viewObject->setOnMethodSuccess($this->module.'/view');
		#$viewObject->setOnMethodFail($this->module, 'edit');
		
		if(!$this->validateValues()) { 
			$viewObject->setNode("validation", $this->getValidateErrors() );
			$viewObject->setAlert("Input Validation Failed!");
			$viewObject->setMethodResult(false);
			return false; 
			
		} elseif(!$this->save()) {
			$viewObject->setError($this->getError()); 
			$viewObject->setMethodResult(false);
			return false;
			
		} else {
			$this->setId($this->getLastId());
			$viewObject->setNode('id', $this->id);
			$viewObject->setMethodResult(true);
			$viewObject->setRedirectParam('id', $this->id);
			return true;
		}
	}
	
	/* set $this->fields and $this->values for sql use */
	public function getUpdateFieldVals() {
		if(empty($this->fld)) return false;
		foreach($this->fld as $f) {
			if(is_object($f) && !$f->getAutoIncrement()) { 
				if(!empty($this->fields)) $this->fields .=",";
				
				// postgres
				/**
				 * @todo PDO detect db type
				 
				if (eregi("postgres", sdb::databaseType)) 
					$this->fields .= $f->getName() . "=" . sdb::quote($f->getValue());
				else 
				*/
					$this->fields .= "`".$f->getName()."`" . "=" . sdb::quote($f->getValue());    
			}
		}
	}
}