<?php 

/**
 * CSV View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * CSV View Renderer
 *
 * @package View 
 */
class View_Csv extends sview 
{ 
 
	public function __construct() {
		parent::$type = 'csv';
	} 
	   
	/* render the output */
	public static function render() {

		parent::render();
  
		if(!self::getNodes()) {
			if(!self::getMethodResult()) {
				print "result,false";
				return;
			} else {
				print "result,true";
				return;			
			}
		}
		
		/* set csv headers */
   		header("Content-type: application/vnd.ms-excel");
   		header("Content-disposition: csv" . date("Y-m-d") . ".xls");		
		
		/* output nodes to csv */
   		$heading =& self::getNode('heading');
		if(is_array($heading)) { 
			$i=0;
			foreach($heading as $col) { 
					if($i>0) echo ",";
					echo self::csvFieldFormat($col);
					$i++;
			}
			echo "\r\n"; 
		}
		
		/* output actual nodes */
		$nodes =& self::getNodes();
		foreach($nodes as $v=>$node) {
			$r=0;
			if($v!='heading') {
				if(is_array($node))
				{
					foreach($node as $row) {
						$i=0;
						if(is_array($row))
						{
							foreach($row as $col) {			
								if($i>0) echo ",";
								echo self::csvFieldFormat($col);
								$i++;
							}
						}
						echo "\r\n";
						$r++;
					}
				}
			}
		}
	}
	
	/* format for csv */
	public static function & csvFieldFormat($v) {
		if(ereg('"', $v)) return '""'.$v.'""';
		elseif(ereg("\r\n", $v)) return '""'.$v.'""';
		elseif(ereg(',', $v)) return '"'.$v.'"';
		else return $v;
	}
}
?>