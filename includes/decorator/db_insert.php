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
 * Scaffolding for Database INSERT commands
 *
 * @package Scaffold 
 */
class DB_Insert extends DB_Construct_Base 
{	 
	var $getDefaultValues=true; 
	
	
	/* save the current record */
	public function save() {
		
		/* todo: fields, validation, convert */
		if(!is_object($this->construct)) {
			$this->error = "construct not defined";
			return false;
		}		
		if(empty($this->fld)) {
			$this->error = "no fields!";
			return false;
		}
		 
		/* generate sql */
		$this->getAddFieldVals(); 		
		$this->sql = "INSERT INTO `{$this->table}` ({$this->fields}) VALUES ({$this->values})";
		
		/* execute */
		$this->result = sdb::instance()->exec($this->sql);
		
		/* error */
		if (!$this->result) 
		{
			$this->error = print_r(sdb::errorInfo(),true);
			return false;
		} 
		else 
		{
			/* set last insert id before more inserts */
			$this->setId(sdb::lastInsertId());
			/* save foreign keys */
			$this->saveForeign();	
			/* save hierachical data */
			$this->saveHierarchicalData();
		}
		return true; 
	}
	
	/* save the foreign keys */
	public function saveForeign() 
	{  
		$this->getForeignAssoConstruct();
		if(!empty($this->foreign) && is_object($this->foreign)) 
			{			
			$id = $this->getId();	
			foreach($this->foreign as $foreign) 
			{
				/* insert foreign keys */
				$fieldname = $foreign->getName();
                $value = sparam::get($fieldname);
				if(!empty($value))
				{
					$remoteIds = explode(",", $value);
					$foreign->setValue($id);
					foreach($remoteIds as $rid) 
					{
						if(!empty($rid))
						{			
							$foreign->setRemoteValue($rid);
							$sql = $foreign->getInsertSQL();
							sdb::query($sql);
						}
					}
				}
			}
		}
	}
	
	/* save the hierarchical data */
	public function saveHierarchicalData() {		
		$this->getHierarchicalStructure();
		if(!empty($this->hierarchy) && is_object($this->hierarchy)) { 
			$this->hierarchy->setKey($this->getLastId()); 
			$parentField = $this->hierarchy->getParentField();
			if(!empty($this->fld->$parentField) && is_object($this->fld->$parentField)) {
				$this->hierarchy->setParentId($this->fld->$parentField->getValue()); 
				$sql = $this->hierarchy->getInsertSQL();
				foreach($sql as $s) { 
					if(!sdb::query($s)) echo $this->error = print_r(sdb::errorInfo(),true);
				}
			}
		}		
	}
	
	/* validate and save handling interfaces with view object */
	public function validateAndSave(&$viewObject) { 
	 	
		if(!$this->validateValues()) { 
			$viewObject->setNode("validation", $this->getValidateErrors());
			$viewObject->setAlert("Input Validation Failed!");
			$viewObject->setMethodResult(false);			
			return false; 
			
		} elseif(!$this->save()) {
			$viewObject->setError($this->getError()); 
			$viewObject->setMethodResult(false);
			return false;
			
		} else {
			$viewObject->setNode('id', $this->getId());
			$viewObject->setMethodResult(true);
			//$viewObject->setRedirectParam('id', $this->getId());
			return true;
		}
	}
	
	/* set $this->fields and $this->values for sql use */
	public function getAddFieldVals() {
		if(empty($this->fld)) return false;
		foreach($this->fld as $f) {
			if(is_object($f) && !$f->getAutoIncrement()) {
				
				// drop empty int/date
				if(!$f->getValue()) {
					$this->dropField($f->name);
				} else {
				
					if(!empty($this->fields)) $this->fields .=",";
					
					// postgres
					#if (eregi("postgres", sdb::databaseType)) 
					#	$this->fields .= $f->getName();
					#else (assum mysql for now)
					$this->fields .= "`".$f->getName()."`";
					
					if(!empty($this->values)) $this->values .=",";
					
					if(($f->getType()=='timestamp' || $f->getType()=='datetime' || $f->getType()=='date') && $f->getValue() == 'NOW()') {
						$val = 'NOW()';
						$this->values .= $val;
					} elseif (preg_match('/(int|integer)/',$f->getType()) && is_int($f->getValue()) ) {
						$val = $f->getValue() ? ((int)$f->getValue()) : 0;
						$this->values .= $val;
					} else {
						$this->values .= sdb::quote($f->getValue());
					}
				}				 
			}
		}
	}
}
?>