<?php

/**
 * XML View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * XML View Renderer
 *
 * @package View 
 */
class View_Xml extends sview 
{  
	
	public function __construct() {
		parent::$type = 'xml';
	} 
  
	/** 
	 * Render XML
	 */
	public static function render() 
	{ 
		parent::render();
		
		$nodes =& self::getNodes();
 
		// no nodes set
		if(!$nodes) {
			if(!self::getMethodResult()) {
				print "<data result=\"false\">\r\n".
				  	  "	<result>false</result>\r\n".
				 	  "</data>";
				return;
			} else {
				print "<data result=\"true\">\r\n".
				  	  "	<result>true</result>\r\n".
				 	  "</data>";
				return;			
			}
		}
		  
		header("Content-type: application/xhtml+xml"); 
	   	echo '<'. '?xml version="1.0" encoding="ISO-8859-1"?'. '>';
	   	echo "<data>\r\n" . View_Xml::array_to_xml($nodes) .  '</data>';	   
	}
	 
	 
	function array_to_xml(&$array, $level=1) {
	    $xml = ''; 
	    foreach ($array as $key=>$value) {
	        $key = strtolower($key);
	        if (is_object($value)) {$value=get_object_vars($value);}// convert object to array
	        
	        if (is_array($value)) {
	            $multi_tags = false;
	            foreach($value as $key2=>$value2) {
	             if (is_object($value2)) {$value2=get_object_vars($value2);} // convert object to array
	                if (is_array($value2)) {
	                    $xml .= str_repeat("\t",$level)."<$key>\n";
	                    $xml .= View_Xml::array_to_xml($value2, $level+1);
	                    $xml .= str_repeat("\t",$level)."</$key>\n";
	                    $multi_tags = true;
	                } else {
	                    if (trim($value2)!='') {
	                        if (htmlspecialchars($value2)!=$value2) {
	                            $xml .= str_repeat("\t",$level).
	                                    "<$key2><![CDATA[$value2]]>". // changed $key to $key2... didn't work otherwise.
	                                    "</$key2>\n";
	                        } else {
	                            $xml .= str_repeat("\t",$level).
	                                    "<$key2>$value2</$key2>\n"; // changed $key to $key2
	                        }
	                    }
	                    $multi_tags = true;
	                }
	            }
	            if (!$multi_tags and count($value)>0) {
	                $xml .= str_repeat("\t",$level)."<$key>\n";
	                $xml .= View_Xml::array_to_xml($value, $level+1);
	                $xml .= str_repeat("\t",$level)."</$key>\n";
	            }
	         } else {
	            if (trim($value)!='') { 
	                if (htmlspecialchars($value)!=$value) {
	                    $xml .= str_repeat("\t",$level)."<$key>".
	                            "<![CDATA[$value]]></$key>\n";
	                } else {
	                    $xml .= str_repeat("\t",$level).
	                            "<$key>$value</$key>\n";
	                }
	            }
	        }
	    }
	    return $xml;
	}
} 
?>