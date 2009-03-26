<?PHP

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
 * Scaffolding for Database SELECT commands
 *
 * @package Scaffold 
 */
class DB_Select extends DB_Construct_Base 
{
	/* pull in default values from construct file */
	var $getDefaultValues=false; 
	var $getMultiKeys=false;
	
	var $distinct=false;
	var $fields=array();
	var $tables=array();
	var $joins=array();
	var $conditions=array();
	var $conditionSql=array(); // raw sql to appent to where
	var $orderBy=array();
	var $order='desc';
	var $groupBy=array();
	var $limit=0;
	var $offset=0;
	var $cache;
	var $rowCount=0;
	var $dropForiegnKeys;
	var $dropForiegnKeysAsso;
	 
	/* set a cache */
	public function setCache($seconds) {
		$this->cache = $seconds;
	}
	
	/* drop foriegn keys from inclusion in query */
	public function dropForiegnKeys() {
		$this->dropForiegnKeys=true;
	}
	
	/* drop forien asso keys from inclusion in query */
	public function dropForiegnKeysAsso() {
		$this->dropForiegnKeysAsso=true;
	}
	
	/* process for view */
	public function processForView(&$view) {
		$rs =& $this->process();		
	 	if($rs && $rs->rowCount()>0) {   
	 		if(isset($this->foreign) && is_object($this->foreign)) { 
	 			$results=array();
	 			foreach($rs->fetchAll(PDO::FETCH_ASSOC) as $row) array_push($results, $this->getForeignRow($row)); 	// row-by-row remote asso key processing
	 			$view->setNode($this->module, $results); 
	 		} else {
	 			$view->setNode($this->module, $rs->fetchAll(PDO::FETCH_ASSOC));	// standard processing
	 		} 
	 		$view->setNode($this->module.'_count', $this->rowCount);
	 		$view->setMethodResult(true);
		} else {
			$view->setMethodResult(false);
			$view->setAlert('No results found');
		}  	
	}
		
	/* get foriegn asso keys for each row */
	public function & getForeignRow(&$row) {  
		foreach($this->foreign as $foreign) {
			$fieldname=$foreign->getName();				
			$row["$fieldname"]='';
			$foreign->setValueFromRow($row); 
			$row["$fieldname"] = $foreign->getResultsString( sdb::query($foreign->getSelectSQL()) );				 
		} 
		return $row;
	}
	
	/**
	 *  Generate the SQL Statement and send the query
	 * 
	 * @return PDOStatement 
	 */	
	public function process() {
		
		// needed: fields convert
		if(!is_object($this->construct)) {
			$this->error = "Construct not defined";
			return false;
		}		
		if(empty($this->fld)) {
			$this->error = "No fields to select!";
			return false;
		}
		
		/* get standard foreign keys */
		if(!$this->dropForiegnKeys) $this->getForeignConstruct();
		
		/* get asso foreign keys */
		if(!$this->dropForiegnKeysAsso) $this->getForeignAssoConstruct();
			 
		/* build select sql */
		if(empty($this->fields) || empty($this->tables)) $this->getTablesFields();		
		$this->sql = "SELECT".
			$this->getSqlCount(). 
			$this->getDistinct().
			$this->getFields().
			$this->getTables().
			$this->getJoins().
			$this->getConditions().
			$this->getGroupBy().
			$this->getOrder().
			$this->getLimit(). 
			$this->getOffset();
			   
			$this->sql;
			
		/* execute sql */
        $rs =& sdb::instance()->query($this->sql);
		if (!$rs) {
			/* set errors */
			$this->error = print_r(sdb::errorInfo(),true);
			$this->rowCount=0;
			return false;
		} elseif($rs->rowCount()) { 
			/* get total count */ 
            $this->rowCount=$this->getTotalMatches($rs->rowCount());
		}
		return $rs;  
	}
	
	/* get total matches */
	private function getTotalMatches($rows) {
		if($this->limit == 0 && $this->offset == 0) return $rows; 
		return sdb::instance()->query("SELECT FOUND_ROWS()")->fetchColumn(0);
	}
	
	/* set the search limit */
	public function setLimit($limit) {
		$this->limit=(integer)$limit;
	} 
	/* get limit sql */
	public function getLimit() {
		if($this->limit > 0) return " LIMIT $this->limit ";
	}
	
	/* set the search offset */
	public function setOffset($offset) {
		$this->offset=(integer)$offset;
	} 
	/* get offset sql */
	public function getOffset() {
		if($this->offset > 0) return " OFFSET $this->offset ";
	}	
		
	/* set this query as distinct */
	public function setDistinct($distinct) {
		$this->distinct=$distinct;
	}
	/* get distinct */
	public function getDistinct() {
		if($this->distinct) return " DISTINCT ";
	}
	 
	/* add table */
	public function pushTable($table) {		
		if(!in_array($table, $this->tables)) $this->tables["$table"] = $this->setTablePh(); 
	}
	/* remove table */
	public function popTable($table) {
	}
	/* set next table placeholder */
	public function setTablePh() {
		$a=array('a','b','c','d','e','f','g','h','i','j','k','l');
		$c=count($this->tables);
		return $a[$c];
	}
	/* get table placeholder for specific tablename */
	public function getTablePh($table) {
		if(!empty($this->tables[$table])) return $this->tables[$table];
		return false;
	}
	/* get tables for sql */
	public function getTables() {
		$sql="";
		foreach($this->tables as $table=>$ph) {
			if(!empty($sql)) $sql.=",";
			$sql.= "$table as $ph";
		}
		return " FROM $sql ";
	}
	
	
	/* add field */
	public function pushField($table,$field) {
		$this->fields["$table"][] = $field;
	}
	/* remove field */
	public function popField($table,$field) {
		foreach($this->fields["$table"] as $k=>$v) if($v==$field) { unset($this->fields["$table"][$k]); return; }
	} 	
	/* get fields for sql */
	public function getFields() {
		$sql='';
		foreach($this->fields as $table=>$fields) {
			$tbl = $this->getTablePh($table);
			foreach($fields as $field) {
				if(!empty($sql)) $sql.=",";
				if(!empty($tbl)) $sql.=$tbl.".";
				$sql.=$field;
			}
		}
		return " $sql ";
	}
	 
	/* add condition */
	public function pushCondition($table,$field,$c,$val) { 
		$this->conditions["$table"]["$field"][] = array($c,$val); 
	}
	
	/* add raw sql statement conditions for where */
	public function pushConditionSql($statement) {
		array_push($this->conditionSql, $statement);
	}	
	
	/* remove field */
	public function popCondition($table,$field,$c,$val) {
		foreach($this->fields["$table"]["$field"] as $k=>$v) if($val[0]==$c && $val[1]==$val) { unset($this->fields["$table"]["$field"][$k]); return; }
	}
	/* get conditions for sql */
	public function getConditions() {
		$sql='';
		foreach($this->conditions as $table=>$fields) {
			
			$tbl = $this->getTablePh($table);
			if(empty($tbl)) $tbl = $table;
			if(!empty($tbl)) $tbl .= ".";
			
			foreach($fields as $field=>$conditions) {
				foreach($conditions as $condition) {
					if(!empty($sql)) $sql.=" AND ";
					if($condition[0] == 'IN') $cond = $condition[1]; else $cond = sdb::quote($condition[1]);
					$sql.="{$tbl}{$field} {$condition[0]} {$cond}";
				}
			}
		}
		
		foreach($this->conditionSql as $statement) {
			if(!empty($sql)) $sql .= " AND ";
			$sql .= " $statement ";
		}
		
		if($sql) return " WHERE ($sql) ";		
	}
	
	/* add group conditions */
	public function pushGroupBy($groupBy){
		$this->groupBy = $groupBy;
	}
	
	/* add join */
	public function pushJoin($sql) {
		$this->joins[] = $sql;
	}
	/* del join */
	public function popJoin() {
		return false;
	}
	/* get joins for sql */
	public function getJoins() {
		$sql="";
		foreach($this->joins as $join) {
			if(!empty($sql)) $sql.=" ";
			$sql.=$join;
		}
		return " $sql ";
	}
		
	/* set order by */
	public function setOrder($column, $direction='asc') {
		$this->orderBy=$column;
		$this->order=$direction;
	} 	
	/* get order by for sql */
	public function getOrder() {
		if($this->orderBy) return " ORDER BY $this->orderBy " . strtoupper($this->order);
	}
	
	/* add group by */
	public function setGroupBy($gb) {
		$this->groupBy=$gb;
	}
	/* get groups by for sql */
	public function getGroupBy() { 
		if(!empty($this->groupBy)) return " $this->groupBy ";
	} 
	
	/* set the fields, tables, and conditions */
	public function getTablesFields() {
		if(empty($this->fld)) return false;
		
		/* add table to query */
		$this->pushTable($this->table);
	  
		foreach($this->fld as $f) {			 
			if(is_object($f)) {
				
				/* field name */
				$field_name = $f->getName();
				$asTable = $f->getForeignAsTable();
				
				/* add field to query */ 
				$this->pushField($this->table, $field_name);
				  
				/* add value to conditions */
				$value = $f->getValue();
				if(!empty($value) || $value === '0') $this->pushCondition($this->table, $field_name, $f->getCondition(), $value); 
				 	 
				/* do joins on remote key */
				$foreignMulti = $f->getForeignMulti();
				$foreignTable = $f->getForeignTable();
				if(!empty($foreignTable) && !$foreignMulti ) 
				{					
					$foreignId = $f->getForeignId();
					$th = $this->getTablePh($this->table);
					   
					// add this join
					$sql = "LEFT JOIN $foreignTable as $asTable ON ($asTable.$foreignId = $th.$field_name)";
					$this->pushJoin($sql);
					
					// add this field
					$this->pushField('', "$asTable.". $f->getForeignColumn() . " AS " . $f->getForeignAs() ); 
				}
			}
		}
	}
} 
?>