<?php
class ProjectSetup extends ssetup 
{ 
	/* Misc settings */
	var $apc			= false;
	var $timezone		= 'America/California';
	var $date_text_format = 'l dS, F Y';
	var $cookie_name 	= 'projectcookie'; 
 
	/* Base URIs for Project */
	var $url			= 'http://localhost/project';
	var $ssl_url 		= 'http://localhost/project';
	
	/* Javascript and CSS include URIs, must be exposed to the the web */
	var $theme_url 		= 'http://localhost/project/views/themes/default/';
	 
	/* Base paths */
	var $lean_php_dir	= '/path/to/lean-php/';	// Base dir for Lean PHP /includes dir
	var $project_dir	= '/path/to/project/';	// Base dir for this projects module/view dirs  
    var $theme_dir      = '/path/to/project/views/themes/default/';				// Theme dir to load header/footer files from
	var $dompdf_dir		= '/path/to/dompdf/'; // Base dir of the DomPDF Class for PDF views 
 
	/* Default module/method and view to use if unspecified in request */
	var $default_module = 'def';
	var $default_method = 'index';
	var $default_view   = 'htm';
	 
    /* db config */
    var $db_driver 		= 'mysql';
    var $db_host		= 'localhost';
    var $db_name 		= '';
    var $db_user 		= '';
    var $db_pass 		= '';
	
	/** mail config */
	var $mail_from 		= "info@domain.com";
	var $mail_name 		= "Widgits, Inc";  
	var $mail_smtp 		= true;
	var $mail_smtp_host = 'mail.domain.com';
	var $mail_smtp_user = 'username';
	var $mail_smtp_pass = 'password';
	
	/* acl for global access to modules */
	var $acl_modules	= array
	(
	  'block' => false, /* '([a-zA-Z]{1,})'*/
	  'allow' => '(.+)'
	);
	
	/* acl for user access to methods */
	var $acl_methods = array
	(	
		'User' => array(
			'allow' => array(
				array('module','*'),
				array('(module1|module2)','(method1|method2)'),
			)
		)
		
		,'Admin' => array(
			'allow' => array(
				array('*', '*')
			)
		)
	);
}
?>