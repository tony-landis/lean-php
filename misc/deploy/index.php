<?php

ob_start();
error_reporting(E_ALL);

/* Init Setup Object Singleton (ssetup::) */
#$setupObj =& apc_fetch('config-acr-php');
#if(!is_object($setupObj)) {
	include_once '../svn/lean-php/includes/singleton/setup.php'; 
	require_once 'config.php'; 
	$setupObj =& ssetup::instance('TravelSetup'); 
	#apc_delete('config-acr-php');
	#apc_store('config-acr-php', $setupObj , 86400);
#}

/* Init Params Object Singleton (sparam::) */
require_once 'includes/singleton/param.php';
$paramObj =& sparam::instance(); 

/* Init PDO Instance Singleton (sdb::) */
require_once 'includes/singleton/pdo.php';
$dbObj = new sdb($setupObj->get('db_driver') . ':host='. $setupObj->get('db_host') . ';dbname='. $setupObj->get('db_name'), 
	$setupObj->get('db_user'), 
	$setupObj->get('db_pass'), 
	array(
		PDO::ATTR_PERSISTENT => false, 
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true
	)
);
sdb::debug(1);
 
/* Init User State Object Singleton (sstate::) */
include_once 'includes/singleton/state.php';
new sstate();

/* include module */
$view   = !empty($paramObj->view)  ? $paramObj->view : $setupObj->default_view;
$module = $paramObj->module;
$method = $paramObj->method;
try {
	if(!@include_once 'modules/' . $module .'/'.  $module .'.php') 
		throw new Exception('Invalid Resource Called');	 
} catch (Exception $e) {
	if(in_array($view, array('htm','html','php'))) {
		header("Location: {$setupObj->url}/{$setupObj->default_module}/{$setupObj->default_method}.{$setupObj->default_view}", TRUE, 301);
	} else {
		header("HTTP/1.0 404 Invalid Resource Called/Not Found"); 
	}
	die("Invalid Resource Called.");
}

/* init the view (sview::) */
include_once 'includes/factory/view.php';
View_Factory($view, $module, $method);

/* Init the module */ 
$obj = new $module(); 
$obj->callMethod($method);  

/* Render the View */
sview::instance()->render(); 

ob_end_flush();
?>