<?php

/**
 * PDF View
 *  
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package View
 */

require_once 'includes/singleton/view.php';
require_once ssetup::get('dompdf_dir') . 'dompdf_config.inc.php';

/**
 * PDF View Renderer - converts standard html view into PDF stream
 *
 * @package View 
 */
class View_Pdf extends sview
{
	public function __construct() {
		parent::$type = 'pdf';
	}
  
	/* render the output */
	public static function render($internal=false) {
		    
		parent::render();
		
		/* redirect */
		if(isset(self::$redirect)) return;
				 
		/* make nodes accessible */
		$nodes =& self::getNodes();  
		if(!empty($nodes)) {
			foreach($nodes as $key => $n) { 
				$$key = $n;
			}
		}  
		  
		$module =& self::getModule();
		$method =& self::getMethod();
		 
		/* capture raw html */
		ob_end_clean();
		ob_start();

		print '<html><head>';
		print '<link rel="stylesheet" type="text/css" href="' . ssetup::get('theme_url')  .'print.css">';
		print '</head><body>';
		include (PROJECT_DIR . 'views/php/' . $module . '/' . $method . '.php');
		print '</body></html>';
	  
		$html = ob_get_contents();
		ob_end_clean();
		ob_start();
		  
		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->render();
		
		if($internal)
			print $dompdf->output();
		else
			$dompdf->stream("{$module}-{$method}.pdf");	 	  
	}
}
?>