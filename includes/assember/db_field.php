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
 * Object based field structure
 *
 * @package Scaffold 
 */
class DB_Field_Assembly
{
	/* include in statements */
	public $ignore; 
	/* sql */
	public $name;
	public $value;
	public $type;
	public $condition='=';
	public $primaryKey;
	public $autoIncrement; 
	/* validation */
	public $required;
	public $validate;
	public $unique;
	public $minLength;
	public $maxLenth; 
	/* conversion */
	public $convert; 
	/* validation error */
	public $validationError;
	/* foreign keys */
	public $foreignTable;
	public $foreignAsTable;
	public $foreignId;
	public $foreignColumn;
	public $foreignAs;
	/* foreign keys stored in asso tables */ 
	public $foreignMulti=false;
	public $foreignAssoTable;
	public $foreignAssoLocalColumn;
	public $foreignAssoForeignColumn;
	
	/* ignore this field */
	public function ignore($ignore=true) {
		$this->ignore=$ignore;
	}
	
	/* value setter */
	public function setValue($value) {
		$this->value=$value;
	}
	
	/* name setter */
	public function setName($name) {
		$this->name=$name;
	}
	
	/* type setter */
	public function setType($type) {
		$this->type = $type;
	}
	
	/* condition setter */
	public function setCondition($condition) {
		$this->condition=$condition;
	}
	
	/* required setter */
	public function setRequired($required=true) {
		$this->required=$required;
	}
	
	/* validation type setter */
	public function setValidate($validate) {
		$this->validate=$validate;
	}
	
	/* min length setter */
	public function setMinLength($min) {
		$this->minLength=$min;
	}
	
	/* max length setter */
	public function setMaxLength($max) {
		$this->maxLenth=$max;
	}
	
	/* conversion setter */
	public function setConvert($convert) {
		$this->convert=$convert;
	}
	
	/* PK setter  */
	public function setPrimaryKey($pk) {
		$this->primaryKey=$pk;
	}
	
	/* unique setter  */
	public function setUnique($u) {
		$this->unique=$u;
	}	
	
	/* set the autoincrement */
	public function setAutoIncrement($ai) {
		$this->autoIncrement=$ai;
	}
	
	/* set validation error message */
	public function setValidationError($er) {
		$this->validationError=$er;
	}
	
	/* foreign key setter */
	public function setForeignKey($table, $foreign, $column, $as=false, $asTable=false) {
		$this->foreignTable=$table;
		$this->foreignAsTable=($asTable)?$asTable:$table;
		$this->foreignId=$foreign;
		$this->foreignColumn=$column;
		$this->foreignAs=$as; 
	}	
	 
	/* do validation */
	public function doValidate() {
		return true;
	}
	
	/* do conversion */
	public function doConvert() {
		return true;
	}
	 
	/* value getter */
	public function getValue() {
		if($this->condition == 'LIKE' && ($this->value !== 0 && !empty($this->value))) return "{$this->value}%";
		return $this->value;
	}
	 	
	/* name getter */
	public function getName() {
		return $this->name;
	}
	
	/* type getter */
	public function getType() {
		return $this->type;
	}
	
	/* condition getter */
	public function getCondition() {
		return $this->condition;
	} 
	
	/* get autoincrement */
	public function getAutoIncrement() {
		return $this->autoIncrement;
	}
	
	/* is multi foreign key */
	public function getForeignMulti() {
		return $this->foreignMulti;
	}
		
	/* get foreign key table */
	public function getForeignTable() {
		return $this->foreignTable;
	}
		
	/* get foreign key table */
	public function getForeignAsTable() {
		return $this->foreignAsTable;
	}
	
	/* get foriegn key column */
	public function getForeignId() {
		return $this->foreignId;
	}
	
	/* get foreign key column */
	public function getForeignColumn() {
		return $this->foreignColumn;
	}
	
	/* get foreign key select as column */
	public function getForeignAs() {
		return $this->foreignAs;
	}
}
?>