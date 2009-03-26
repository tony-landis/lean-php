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
 * Object based multi result keys
 * @package Scaffold 
 */
class DB_Foreign_Assembly 
{ 
	public $sql;
	public $ids;
	public $name; 		/* name of local field for output */
	public $table;		/* table to search for associated records against */
	public $local;		/* local fieldname to retrieve value from */
	public $remoteId;
	public $localId;
	public $value;
	public $assoTable;
	public $assoRemoteId;
	public $assoLocalId;
	public $onDelete;
	
	/* set the name */
	public function setName($name) {
		$this->name=$name;
	}	
	/* get the name */
	public function getName() {
		return $this->name;
	}
	
	/* set the local name */
	public function setLocal($local) {
		$this->local=$local;
	}
	/* get the local field name */
	public function getLocal() {
		return $this->local;
	}
	
	/* set the remote table */
	public function setTable($table) {
		$this->table=$table;
	}
	
	/* set the remote field id */
	public function setRemoteId($remoteId) {
		$this->remoteId=$remoteId;
	}	
	
	/* set the local field id */
	public function setLocalId($localId) {
		$this->setLocalId=$localId;
	}	
	
	/* set cascade delete */
	public function setOnDelete($ond) {
		$this->onDelete=$ond;
	}
	/* get cascade delete */
	public function getOnDelete() {
		return $this->onDelete;
	}
	
	/* set value of this field */
	public function setValue($value) {
		$this->value=$value;
	}
	/* set value of the remote field */
	public function setRemoteValue($value) {
		$this->remoteValue=$value;
	} 
	/* set value from current row */
	public function setValueFromRow(&$row) {
		$this->setValue($row["{$this->local}"]);
	}
	
	/* set the asso table */
	public function setAssoTable($tbl) {
		$this->assoTable=$tbl;
	}
	/* set the asso remote id */
	public function setAssoRemoteId($id) {
		$this->assoRemoteId=$id;
	}
	/* set the asso local id */
	public function setAssoLocalId($id) {
		$this->assoLocalId=$id;
	}
	
	/* generate the sql to run for this field */
	public function getSelectSQL() {
		return $this->sql = "SELECT $this->assoLocalId AS ids FROM $this->assoTable WHERE $this->assoRemoteId = $this->value";
	}	
	/* generate the sql to run for inserting new records */
	public function getInsertSQL() {
		return $this->sql = "INSERT INTO $this->assoTable ($this->assoLocalId,$this->assoRemoteId) VALUES ($this->remoteValue,$this->value)";
	}	
	/* generate the sql to run for deleting existing records */
	public function getDeleteSQL() {
		return $this->sql = "DELETE FROM $this->assoTable WHERE $this->assoRemoteId = $this->value";
	}

	/* get foreign key results as array */
	public function getResultsArray(&$rs) {
		$this->ids=array();
		if($rs) foreach($rs as $row) array_push($this->ids, $row['ids']);
		return $this->ids;		 
	}
	
	/* get foriegn key results as comma delimited string */
	public function getResultsString(&$rs) {
		return implode(",", $this->getResultsArray($rs));
	} 
}
?>