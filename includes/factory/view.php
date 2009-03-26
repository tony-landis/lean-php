<?php

/**
 * View Factory
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

/**
 * Return an instance of the sview singleton
 *
 * @package View
 * @return Object sview
 */
function View_Factory($view, $module, $method) {
	switch ($view) {
		case 'csv':
			require_once 'includes/adaptor/view_csv.php';
			return sview::instance($module, $method, 'View_Csv');
		case 'pdf':
			require_once 'includes/adaptor/view_pdf.php';
			return sview::instance($module, $method, 'View_Pdf');
		case 'json':
			require_once 'includes/adaptor/view_json.php';
			return sview::instance($module, $method, 'View_JSON');
		case 'txt':
			require_once 'includes/adaptor/view_txt.php';
			return sview::instance($module, $method, 'View_TXT');
		case 'yaml':
			require_once 'includes/adaptor/view_yaml.php';
			return sview::instance($module, $method, 'View_YAML');
		case 'xml':
			require_once 'includes/adaptor/view_xml.php';
			return sview::instance($module, $method, 'View_Xml');
		default:
			require_once 'includes/adaptor/view_php.php';
			return sview::instance($module, $method, 'View_PHP');
		break;
	}
}
?>