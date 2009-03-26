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

/**
 * Object based hierarchical data 
 *
 * @package Scaffold 
 */ 
class DB_Hierarchical
{  
	private $table;
	private $key;
	private $parentId;
	
	private $keyField;
	private $parentField;
	private $indentField;
	private $rankField;
		
	public function setTable($tableName) {
		$this->table=$tableName;
	}
	public function setKeyField($fieldName) {
		$this->keyField=$fieldName;
	}
	public function setParentField($fieldName) {
		$this->parentField=$fieldName;
	}
	public function setIndentField($fieldName) {
		$this->indentField=$fieldName;
	}
	public function setRankField($fieldName) {
		$this->rankField=$fieldName;
	}
	
	public function getParentField() {
		return $this->parentField;
	}
	   
	/**
	 * Set the current record key
	 *
	 * @param int $id
	 */
	public function setKey($id) {
		$this->key=$id;
	}
	
	/**
	 * Set the current record parentid
	 *
	 * @param int $parentId
	 */
	public function setParentId($parentId) {
		$this->parentId=$parentId;
	}
	 	
	/**
	 * Generate the sql to run for updating the record after new insert
	 *
	 * @return string
	 */
	public function getInsertSQL() {
		$sql = array();
		
		
		if($this->parentId > 0)
		{
			// create tmp table
			array_push($sql, "create table tmp_up select * from $this->table where $this->parentField = '$this->parentId' OR $this->keyField = '$this->parentId'");
		
			// increment all other ranks
			array_push($sql, "UPDATE $this->table SET $this->rankField = 
				($this->rankField + 1) 
				where $this->rankField >= (select ($this->rankField + 1) as rank from tmp_up where $this->keyField=$this->parentId)");
			  
			// set the rank for the new record
			array_push($sql, "UPDATE $this->table SET 
				$this->rankField = (select ($this->rankField + 1) as rank from tmp_up where $this->keyField=$this->parentId) 
				where $this->keyField=$this->key");
		
			// set the indent level for the new record
			array_push($sql, "UPDATE $this->table SET $this->indentField = ((select ifnull($this->indentField,0) from tmp_up where $this->keyField = '$this->parentId')+1) where $this->keyField=$this->key");
			
			// drop temp table
			array_push($sql, "drop table tmp_up");				
		
		} else {
			
			// create tmp table
			array_push($sql, "create table tmp_up select * from $this->table order by $this->rankField desc limit 1");
		
			// get highest current rank 
			array_push($sql, "UPDATE $this->table SET 
				$this->rankField = (select ($this->rankField + 1) as counter from tmp_up order by $this->rankField desc limit 1)
				where $this->keyField=$this->key");

			// drop temp table
			array_push($sql, "drop table tmp_up");							
		}
			
		return $sql;
	}
}
?>