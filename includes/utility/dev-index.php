<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html> 
<head> 
<title>
	<?= sparam::get('module') ?> - Lean PHP Development Tools
</title>  
  	<script type="text/javascript" src="<?php echo ssetup::get('ext_url') ?>adapter/jquery/jquery.js"></script>  
</head>
<body>  


<!-- DATABASE -->
<h2>DATA DICTIONARY TOOL</h2>
<p>Convert existing db table to construct XML file</p>
<form id="dd-form">
 	<input type="hidden" name="action" value="xml_generate">
 	Table: <input type="text" name="table" value="<?= sparam::get('module') ?>">
 	Save? <input type="checkbox" name="save" value="1">
 	<input type="submit" value="Generate XML">
</form>




<!-- JAVASCRIPT VIEW -->
<h2>EXT JS GENERATION TOOLS</h2>

<?php

// get the columns from the xml construct
$fields = false;
$xml = PROJECT_DIR . 'modules/' . sparam::get('module') . "/" . sparam::get('module') . ".xml"; 	
if(!is_file($xml) || !is_readable($xml)) {
	print "<p>Construct XML file does not exist for this module.</p>"; 
} else { 
	$doc = new DOMDocument();
	$doc->load($xml);  
	$columns = $doc->getElementsByTagName("column"); 
	$fields = array();
	foreach($columns as $col) { 
		$name = $col->getAttribute("name");  
		$type = $col->getAttribute("type");		
		array_push($fields, array('name'=> $col->getAttribute("name"), 'type'=> $col->getAttribute("type")));				
	}		
}			
?>

 
<?php if($fields): ?>
<form action="dev.htm" method="POST">
<table border=1 cellpadding=3>	
	<tr> 
		<th>Field Title</th>
		<th>Field Type</th>
		<th>Hidden</th>
		<th>Excluded</th>
		<th>Sortable</th>
		<th>Editable</th> 
	</tr>
	<?php foreach($fields as $f) { ?>
	<tr>
		<td><input type="text" name="fieldSetup[<?php echo $f['name'] ?>][title]" value="<?php echo ucwords(str_replace("_"," ", $f['name'])) ?>"></td>
		<td>
			<select name="fieldSetup[<?php echo $f['name'] ?>][type]">
				<option value="text" <?php if(in_array($f['type'], array('varchar'))) echo "selected"; ?>>Text</option>
				<option value="bool" <?php if(in_array($f['type'], array('tinyint'))) echo "selected"; ?>>Boolean</option>
				<option value="number" <?php if(in_array($f['type'], array('int','integer'))) echo "selected"; ?>>Number</option>
				<option value="money" <?php if(in_array($f['type'], array('float', 'double', 'decimal'))) echo "selected"; ?>>Money</option>
				<option value="textarea" <?php if(in_array($f['type'], array('text'))) echo "selected"; ?>>Text Area</option>
				<option value="date" <?php if(in_array($f['type'], array('date','time','timestamp'))) echo "selected"; ?>>Date</option>
				<option value="time" <?php if(in_array($f['type'], array('time'))) echo "selected"; ?>>Time</option>
				<option value="menu" <?php if(eregi("(enum|set)", $f['type']) || in_array($f['type'], array('enum','set'))) echo "selected"; ?>>Menu</option>				
			</select>
		</td>
		<td><input type="checkbox" name="fieldSetup[<?php echo $f['name'] ?>][hidden]" value="1" <?php if($f['name']=='id') echo "checked"; ?>></td>
		<td><input type="checkbox" name="fieldSetup[<?php echo $f['name'] ?>][excluded]" value="1"></td>
		<td><input type="checkbox" name="fieldSetup[<?php echo $f['name'] ?>][sort]" value="1" checked=true></td>
		<td><input type="checkbox" name="fieldSetup[<?php echo $f['name'] ?>][edit]" value="1" checked=true></td>
	</tr>	
	<?php } ?>
</table> 
<p>Template: 
<select name="template">
	<option value="simple-grid-edit-add.js">simple-grid-edit-add.js (EXT)</option>
</select>
</p>
<input type="hidden" name="action" value="ext_generate">
<input type="submit" value="Generate Code">
</form>
<?php endif; ?>


</body>
</html>

 