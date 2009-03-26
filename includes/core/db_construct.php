<?php

/**
 * Database CRUD Scaffolding
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Scaffold
 */

/**
 * Base database construct class
 *
 * @package Scaffold 
 */ 
class DB_Construct_Base
{  
	/* xml object */
	static $construct;
	/* field list object - field names, values, validation req, convert types, etc */ 
	static $fld;	 
	/* foreign field (multi-values) */
	static $foreign;
	/* hierarchical data */
	static $hierarchy;
	
	/* db table name */
	var $table;
	/* current/last record id */
	var $id; 
	/* current action (determines logic of validation) */
	var $action;
	/* current module */
	var $module; 
	/* error message */
	var $error;
	  
	/* init */
	function __construct($module=false, &$db=false, &$setup=false, &$values, $autoloadConstruct=true) 
	{	 
		/* define module */
		if($module) { $this->setConstruct($module);	$this->module=$module; } 		
		/* autoload fields */
		if($autoloadConstruct) $this->getFieldsConstruct();		
		/* define values from params */
		$this->setValues($values);		 
	}
	
	/* db table name setter */
	public function setTable($table) {
		$this->table=$table;
	}
	/* db table name getter */
	public function getTable() {
		return $this->table;
	}
	
	/* autoincrement type setter */
	public function setAutoIncrement($is) {
		$this->autoincrement=$is;
	}		
	
	/* record id setter */
	public function setId($id) {
		$this->id=$id;
	}	
	
	/* record id getter */
	public function getId() {
		return $this->id;
	}
	
	/* get last insert id */
	public function getLastId() {
		$id = 0;
		if($this->autoincrement) $id=sdb::lastInsertId(); 
		if($id) return $id;
		
		// postgres
		#if (eregi("postgres", sdb::databaseType))
		#	return sdb::query("select currval('".$this->table."_".$this->getPKFieldName()."_seq')")->fetchColumn(0); 
	}
	
	/* get sql for counting all rows */
	public function getSqlCount() {
		/* if (sdb::getAttribute(PDO::ATTR_DRIVER_NAME))
		* todo: no quercus support
		*/
		 return ' SQL_CALC_FOUND_ROWS ';
	}
	   
	/* value of fields setter (from object or array) */
	public function setValues(&$values)
    {
		if(empty($values)) return;
		foreach($values as $field=>$value) 
		{
			if(isset($this->fld->$field) && is_object($this->fld->$field)) 
				$this->fld->$field->setValue($value);
			elseif($field=='limit' && is_a($this, 'DB_Select'))
				$this->setLimit($value);
			elseif($field=='start' && is_a($this, 'DB_Select'))
				$this->setOffset($value);
		}
	}

    /**
     * Get the current value of a field
     *
     * @param string $field
     * @return mixed
     */
    public function getFieldValue($field)
    {
        if(!isset($this->fld->$field)) return false;
        return $this->fld->$field->getValue();
    }

    /**
     * Get the current value of a field
     *
     * @param string $field
     * @return mixed
     */
    public function setFieldValue($field, $value)
    {
        if(!isset($this->fld->$field)) return false;
        $this->fld->$field->setValue($value);
        return true;
    }

	/* validate the values */
	public function validateValues() {
		if(!is_object($this->fld)) { 
			echo $this->error='empty fieldset';
			return false;
		} 
		$validated=true;
		foreach($this->fld as $f) {
			if(is_object($f) && !$f->getAutoIncrement() && !$f->ignore) { 
				/*  required? */
				if($f->required && empty($f->value)) { 
					$f->setValidationError('field is required');
					$validated=false;
				/* min length */
				}  elseif($f->minLength && (empty($f->value) || strlen($f->value) < $f->minLength)) {
					$f->setValidationError('field length must be greater than ' . ($f->minLength-1));
					$validated=false;
				/* max length */
				}  elseif(@$f->maxLength && (strlen($f->value) > $f->maxLength)) {
					$f->setValidationError('field length must be smaller than ' . ($f->minLength+1));
					$validated=false;
				/* check uniqueness */
				} elseif(@$f->unique && !empty($f->value)) {
					$rs=sdb::query("select " . $this->getPKFieldName() . " from {$this->field} where {$f->field} = ". sdb::quote($f->value));
					if($rs && $rs->rowCount()) {
						$f->setValidationError('field value is not unique, try another value');
						$validated=false;
					}				 
				/* custom validation */
				} elseif($f->validate) {
					require_once LEAN_PHP_DIR . 'includes/utility/validate.php';				
					$validate=new Validate($f->validate, $f->value);
					if(!$validate->getStatus()) {
						$f->setValidationError($validate->getError());
						$validated=false;
					} 					
				}	  
			}
		} 		
		return $validated;
	}
	
	/* get class error */
	public function getError() {
		return $this->error;
	}
	
	/* get validation errors */
	public function getValidateErrors() {
		$e=array();
		if($this->error) return $this->error;
		$f=array();
		if(is_object($this->fld)) 
			foreach ($this->fld as $f) 
				if(!empty($f->validationError)) 
					array_push($e, array('id' => $f->name, 'msg' => $f->validationError)); 
		return $e;
	}
	
	/* fields setter from array */
	public function setField($fld) { 
		$this->fld->$fld = new DB_Field_Assembly; 
		$this->fld->$fld->setName($fld);
	}
	
	/* fields setter from construct */
	public function getFieldsConstruct() {  
		include_once LEAN_PHP_DIR . 'includes/assember/db_field.php';
		$fields = $this->construct->getElementsByTagName("column");
		foreach($fields as $field) {  
			$obj = new DB_Field_Assembly; 
			$fieldname = $field->getAttribute("name");  
			$obj->setName($fieldname);
			$obj->setRequired($field->getAttribute("required"));
			$obj->setType($field->getAttribute("type"));
			$obj->setValidate($field->getAttribute("validate")); 
			$obj->setMinLength($field->getAttribute("minLenth")); 
			$obj->setMaxLength($field->getAttribute("size")); 
			$obj->setConvert($field->getAttribute("convert")); 
			if(!empty($this->getDefaultValues)) $obj->setValue($field->getAttribute("default"));  
			$obj->setUnique($field->getAttribute("unique"));  
			
			/* is field PK */
			if($field->getAttribute("primaryKey")) {
				$obj->setPrimaryKey($field->getAttribute(true));
				$this->setPKFieldName($fieldname);

				/* is field auto increment */
				if($field->getAttribute("autoIncrement")) {
					$this->setAutoIncrement(true);
					$obj->setAutoIncrement(true);  
				} 
			} 
			
			$this->fld->$fieldname =& $obj;
			unset($obj); 
		}
	}

	/* foreign keys setter from construct */
	public function getForeignConstruct() { 
		include_once LEAN_PHP_DIR . 'includes/assember/db_field.php';		
		$foreignkey = $this->construct->getElementsByTagName("foreign-key"); 
		foreach($foreignkey as $field) { 
			$fieldname = $field->getAttribute("local");
			if(isset($this->fld->$fieldname) && is_object($this->fld->$fieldname))
				$this->fld->$fieldname->setForeignKey($field->getAttribute("foreignTable"), $field->getAttribute("foreign"), $field->getAttribute("column"), $field->getAttribute("as"), $field->getAttribute("asTable")); 
		}
	}
	
	/* foreign keys setter (asso) from construct */
	public function getForeignAssoConstruct() {
		include_once LEAN_PHP_DIR . 'includes/assember/db_foreign.php'; 		
		$foreignkeys = $this->construct->getElementsByTagName("foreign-key-asso");
		foreach($foreignkeys as $field) {			
			$obj = new DB_Foreign_Assembly; 
			$fieldname = $field->getAttribute("assoTable");  
			$obj->setName($fieldname);
			$obj->setLocal($field->getAttribute("local"));
			$obj->setTable($field->getAttribute("foreignTable"));
			$obj->setAssoTable($field->getAttribute("assoTable"));
			$obj->setAssoRemoteId($field->getAttribute("assoLocal"));
			$obj->setAssoLocalId($field->getAttribute("assoForeign")); 
			$obj->setOnDelete($field->getAttribute("onDelete"));
			$this->foreign->$fieldname =& $obj; 
			unset($obj); 
		} 							  		
	}
	
	/* foreign keys setter (asso) from construct */
	public function getHierarchicalStructure() {
		include_once LEAN_PHP_DIR . 'includes/assember/db_hierarchical.php'; 		
		$hierarchy = $this->construct->getElementsByTagName("hierarchical-structure");
		foreach($hierarchy as $i) {		 	
			$obj = new DB_Hierarchical();			
			$obj->setTable($this->table);
			$obj->setKeyField($i->getAttribute("key"));
			$obj->setParentField($i->getAttribute("parent"));
			$obj->setRankField($i->getAttribute("rank"));
			$obj->setIndentField($i->getAttribute("indent")); 
			$this->hierarchy =& $obj;
			unset($obj); 
		} 							  		
	}	
	 	
	/* fields getter */
	public function getFields() {
		if(is_object($this->fld)) return $this->fld; else return false;
	}
	
	/* drop fields from arrays */
	public function dropFields($field_array) {
		if(!is_array($field_array)) {
			$this->dropField($field_array); return;
		}
		foreach($field_array as $f) $this->dropField($f);		
	}
	
	/* drop fields except specified */
	public function dropFieldsExcept($field_array) {
		if(!is_object($this->fld) || !is_array($field_array)) return false;
		foreach($this->fld as $f) 
			if(!in_array($f->getName(), $field_array))
				$this->dropField($f->getName());				
	}
	
	/**
	 * Drop all fields with empty values
	 */
	public function dropFieldsEmpty() {
		if(!is_object($this->fld)) return false;
		foreach($this->fld as $f) { 
			$v=$f->getValue();
			if(empty($v) && $v != '0') $this->dropField($f->getName());			
		}
	}
	
	/* drop single field from value/condition inclusion */
	public function dropField($field) {
		if(is_object($this->fld->$field)) unset($this->fld->$field);
	}
		  
	/* primary key name setter */ 
	public function setPKFieldName($f) {
		$this->PKFieldName=$f;
	}
	/* primary key name getter */
	public function getPKFieldName() {
		if($this->PKFieldName) return $this->PKFieldName; 
		return false;
	}
	 
	/* construct object setter */
	public function setConstruct($module) {
		if(!$this->table) $table=$module;
		$this->setTable($table); 
		$xml = PROJECT_DIR . 'modules/' . $module . "/" . $module . ".xml";
		if(!is_file($xml) || !is_readable($xml)) {
			echo "$xml: no such construct!";
			return false;
		}
		$doc = new DOMDocument();
		$doc->load($xml); 
		$this->construct =& $doc;  
	}
	
	/* set the construct from an existing object */
	public function setConstructObj(&$construct) {
		$this->construct =& $construct;
	}
	 
	/* set the table construct from an existing object */
	public function getConstruct() {
		return $this->construct;
	} 
}
?>