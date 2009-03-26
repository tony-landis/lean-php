<?php

include_once LEAN_PHP_DIR . 'includes/class/adodb/adodb.inc.php';

class DB_Dictionary 
{	   
	public static $db;
	public static $setup;
	public static $construct;
	public static $dd; /* data dictionary object */
	
	public $errors=array();
	public $table;
	public $tableExists;
	public $tableOptions;
	
	public $fields=array();
	public $data=array();
	
	public $indexAdd=array(); 
	public $indexDrop=array();
	
	public $procedureAdd=array();
	public $procedureDrop=array();
	
	
	/**
	 * init
	 */
	public function __construct($table, $class, $setup, &$db) {
		$this->setup=&$setup;
		$this->db=&$db;
		
		$this->table=$table;
		$this->class=$class;
		$this->setup=$setup; 
		$this->dd = NewDataDictionary($this->db);
		 
		// construct object setter
		$xml = PROJECT_DIR . 'modules/' . $class . "/" . $class . ".xml"; 
		if(!is_file($xml) || !is_readable($xml)) {
			echo "$xml: no such construct!";
			return false;
		}
		$doc = new DOMDocument();
		$doc->load($xml); 
		$this->construct =& $doc;   
		
		$this->tableOptions['constraints']='';
		
		// drop 
		$this->dd->ExecuteSQLArray( $this->dd->DropTableSQL($this->table));
		
		// table exists
		$rs=$db->query("select count(*) from $table");
		if(!$rs) {
			$this->tableExists=false;
			
			// check for field changes
			$this->getConstructFields();
			
			// check for indexes to add
 
		} else {
			$this->tableExists=true;
			// build field list
			
			$this->getConstructFields();
		}
	}
	 
	/**
	 * Save current schema
	 */
	public function save() {
		
		// start transaction
		sdb::StartTrans();
		
 		// create table 
		$sql = $this->dd->ChangeTableSQL($this->table, $this->fields, $this->tableOptions);
		
		sdb::debug=true; 
		$this->dd->ExecuteSQLArray($sql);
		#sdb::debug=false; 
		
		#foreach($sql as $i) echo $i . ";<br>";
		#echo "<br>";
		 
		// insert data
		if(!$this->tableExists) {
			 
			// check for data to insert
			$this->getSqlData();  
			if($this->data) $this->dd->ExecuteSQLArray($this->data);
		} 
				
		// finish transaction
		sdb::CompleteTrans(); 
	}
	
	/**
	 * Get fields from construct
	 */
	public function getConstructFields() {
		
		$fields = $this->construct->getElementsByTagName("column");
		foreach($fields as $field) {   
			$options=array(); 
			
			// column
			$col = $field->getAttribute("name");
			
			// size
			$size = $field->getAttribute("maxLength");
			
			// field type
			$type =  $field->getAttribute("type");
			switch($type) {
				case 'tinyint': $type = 'I1'; break;
				case 'double': $type = 'N'; break;	
				case 'longtext': $type = 'XL'; break;	
				case 'datetime': $type = 'T'; break;			
			}
			
			// no size
			if($type == 'int' || $type=='tinyint') $size = false; 
			
			// autoincrement
			$key = false;
			if($field->getAttribute("primaryKey")) $key = 'KEY';
			
			// autoincrement
			$auto = false;
			if($field->getAttribute("autoIncrement")) $auto = 'AUTOINCREMENT';
			
			// not null
			$notnull = false;
			if($field->getAttribute("required")) $notnull = 'NOTNULL';
			
			// default
			$def = false;
			if($field->getAttribute("default")) $def = "'DEFAULT' => ". $field->getAttribute("default");
	  
			// enum  
			if(eregi('enum', $type)) {
				$enum = ereg_replace('enum', '', $type);								
				if($this->dd->databaseType == 'postgres') {
					if(!empty($enum)) $this->tableOptions['constraints'] .= ", CHECK ($col in $enum)";
					$type = 'C';					
				} elseif($this->dd->databaseType == 'oci8') {
					if(!empty($enum)) $this->tableOptions['constraints'] .= ", CONSTRAINT cons_{$this->table}_$col CHECK ('$col' in $enum)";
					$type = 'C';					
				} elseif($this->dd->databaseType == 'mysql') {
					#$this->tableOptions = array('constraints' => ", CHECK ($col in $enum)");
					// fine in 'ENUM('')' format...
				}
			}  
			array_push($this->fields, array("`$col`", $type, $size, $key, $auto, $notnull, $def));					
		}
	}	
  
	/**
	 * Get data
	 */
	public function getSqlData() { 
		// construct object setter
		$xml = PROJECT_DIR . 'modules/' . $this->class . "/data.xml"; 
		if(!is_file($xml) || !is_readable($xml)) return false; 
		$doc = new DOMDocument();
		$doc->load($xml);   
		$data = $doc->getElementsByTagName("sql");
		foreach($data as $sql) array_push($this->data, $sql->nodeValue); 
	}
	
	/**
	 * Get indexes
	 */
	public function getIndexes() {
		
	}
	
	/**
	 * Add Index
	 */
	public function addIndex() {
		
	}
	
	/**
	 * Drop Index
	 */
	public function dropIndex() {
		
	}
	
	
	
	/**
	 * Add procedure
	 */
	public function addProcedure() {
		
	}
	
	/**
	 * Drop procedure
	 */
	public function dropProceedure() {
		
	} 	 
}
?>