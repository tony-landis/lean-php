<?php

/**
 * TXT View
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';

/**
 * TXT View Renderer
 *
 * Attempts to render arrays/objects to a text table
 *
 * @package View
 */
class View_TXT extends sview
{

	public function __construct() {
		parent::$type = 'txt';
	}

	/* render the output */
	public static function render() {

		parent::render();

        $nodes =& self::getNodes();

		if(!$nodes) {
			if(!self::getMethodResult()) {
				print "result:false";
				return;
			} else {
				print "result:true";
				return;
			}
		}
        
		/* output actual nodes */
        require_once 'includes/utility/array_to_texttable.php'; 
        foreach($nodes as &$node)
        {
            if(is_array($node))
            {
                print "<pre>\n";
                ArrayToTextTable::setData($node, true);
                ArrayToTextTable::printText();
                print "\n</pre>\n";
            }
        }
	}
}
?>