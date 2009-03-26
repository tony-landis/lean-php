<?php

/**
 * Factory for plugins
 *
 * @param int $id The id of the plugin in the database
 * @param string $type
 * @param string $name
 * @param static object $setup
 * @param static object $db
 * @param static object $user
 * @return object
 */
function Plugin_Factory($id, $type=false, $name=false, &$setup, &$db, &$user) { 
	if(!$type || !$name) {
		if(empty($id)) return false;
		$rs=$db->query("select plugin.name,plugin.type from plugin where id=$id");
		if(!$rs || !$rs->rowCount()) return false;
		$name = $rs->fields['name'];
		$type = $rs->fields['type'];		
	}		
	$path = strtolower($setup->include_dir."plugins/$type/$name.php");
	if(!is_file($path)) return false;
	include_once "$path";
	$objName = strtolower($type."_".$name); 
	$obj =& new $objName($id, $type, $name);
	$obj->setDB($db);
	$obj->setSetup($setup);
	$obj->setUser($user);
	return $obj;
}
?>