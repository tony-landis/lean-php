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
 * Scaffolding for Database DELETE commands
 *
 * @package Scaffold 
 */ 
class DB_Delete extends DB_Construct_Base 
{	   
	public static $conditions=array();
	
	/* save the current record */
	public function delete() {
		
		/* todo: validation  */
		if(!is_object($this->construct)) {
			$this->error = "Construct not defined";
			return false;
		}
		 
		/* generate sql */ 
		$this->sql = "DELETE FROM $this->table WHERE (".$this->getConditions().")";
		
		/* execute */
		$this->result = sdb::exec($this->sql); 
		
		/* error */
		if (!$this->result) {
			$this->error = print_r(sdb::errorInfo(),true);	
			return false;
		} else {
			/* save foreign keys */
			#$this->setId($id);
			$this->deleteForeign();		
		}
		unset($this->conditions);	
		return true; 
	}
	
	
	/* delete by primary key */
	public function deleteByPK($id) {
		$this->deleteByCol($this->getPKFieldName(), $id);
		return $this->delete();
	}
	
	/* add conditions */
	public function deleteByCol($column, $value) {
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
		
	/* save the foreign keys */
	public function deleteForeign() {  
		$this->getForeignAssoConstruct();
		if(!empty($this->foreign) && is_object($this->foreign)) {		 
			foreach($this->foreign as $foreign) {
				/* find delete cascade foreign keys */
				$fieldname = $foreign->getName(); 
				$cascade = $foreign->getOnDelete();
				if($cascade=='CASCADE') { 
					$foreign->setValue($this->id); 
					$sql = $foreign->getDeleteSQL();
					sdb::query($sql); 
				}
			}
		}
	}
}