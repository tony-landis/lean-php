<?php

class dev_handler
{ 
	public static $action;
	public static $module;
	public static $actions = array('xml_generate','ext_generate');
	
	static function doAction($action, $module)
	{ 
		if(!$action) { 
			include 'includes/utility/dev-index.php';
			exit;
		}
		
		self::$module = $module;
		if(in_array($action, self::$actions)) {
			self::$action();		
			exit;
		} else {
			die("Action '$action' not permitted here.");		
		}
	}
	
	/**
	 * Determine form field xtype for ext form
	 */
	private static function getExtExtype($type) 
	{
		switch($type) { 
			case 'text': return 'textfield';
				break;
			case 'number': return 'numberfield';
				break;
			case 'money': return 'textfield';
				break;
			case 'textarea':  return 'textarea';
				break;
			case 'date': return 'datefield';
				break;
			case 'datetime': return 'datefield';
				break;
			case 'time': return 'timefield';
				break;
			case 'bool': return 'checkbox';
				break;
			case 'menu': return 'combo';
				break;
			default:  return 'textfield';
				break;
		}		
	}
	
	/**
	 * Generate JSON for Ext Components
	 */
	private static function ext_generate()
	{
		// get xml construct
		$xml = PROJECT_DIR . 'modules/' . sparam::get('module') . "/" . sparam::get('module') . ".xml"; 
		$doc = new DOMDocument();
		$doc->load($xml);  
		$fields = $doc->getElementsByTagName("column"); 
			 
		// params passed in
		$fieldSetup = sparam::get('fieldSetup');
		$construct  = sparam::get('module');
		$template   = sparam::get('template');
		
		// defaults
		$dataReader = '';  
		$ColumnModule = '';
		$AddDefaults = '';
		$newDataRecord = '';
		
		// form start 
		$formItems = '';
		
		
		foreach($fields as $field) 
		{ 
			$name = $field->getAttribute("name");    
			$setup = $fieldSetup["$name"];
			
			// excluded entirely?
			if(!isset($setup['excluded'])) {
				
				// new record writer
				if(!empty($newDataRecord)) $newDataRecord .= "\r\n						,";
				$newDataRecord .= "{$name}: ''";

				if(!empty($AddDefaults)) $AddDefaults .=", ";
				$AddDefaults .= $name . ":''";
				
				$dataReaderType = false;
				
				// hidden from grid entirely?
				if(!isset($setup['hidden'])) {
				
					if(!empty($ColumnModule)) $ColumnModule .= "\r\n	,";
					$ColumnModule .= "{".
						"header: '". $setup['title'] ."', ".
						"dataIndex: '{$name}',".
						"width: 75, ";
					
					// form start
					if(!empty($formItems)) $formItems .= ",";
					$formItems .= "{\r\n" .
						"		fieldLabel: '" . $setup['title'] . "'\r\n" . 
						"		,name: '{$name}'\r\n" .
						"		,allowBlank: ". ($field->getAttribute("required") ? 'true':'false') . "\r\n".
						"		,xtype: '". self::getExtExtype($setup['type']) ."'\r\n";
					
					// form checkbox extra:
					if(self::getExtExtype($setup['type']) == 'checkbox') 
					{
						$formItems .= "		,inputValue: '1'\r\n";
					}					
					
					// combobox / multiselect extras:
					if(self::getExtExtype($setup['type']) == 'combo') 
					{
						$formItems .= "		,hiddenName: '" . $name . "'\r\n";
					}
					
					// form end
					$formItems .= "	}";

					
					
								  
					// sortable
					if($setup['sort']) $ColumnModule .= "sortable:true, ";
					
					if($setup["edit"]) {
						
						$ColumnModule .="selectOnFocus:true, ";
						 
						switch($setup['type']) {
							case 'text':
								$ColumnModule .= "editor:new Ext.form.TextField()";
								$dataReaderType = 'type: \'string\'';
								break;
								
							case 'number':
								$ColumnModule .= "editor:new Ext.form.NumberField()";
								$dataReaderType = 'type: \'int\'';
								break;
							
							case 'money':
								$ColumnModule .= "renderer:'usMoney', editor:new Ext.form.NumberField({allowBlank:true, allowNegative:false})";
								$dataReaderType = 'type: \'float\'';
								break;	
							
							case 'textarea': 
								$ColumnModule .= "editor: new Ext.form.TextArea({autoHeight:true,grow:true})";
								$dataReaderType = 'type: \'string\'';
								break;
								
							case 'date':
								$ColumnModule .= "editor: new Ext.form.DateField({format:'Y-m-d'})\r\n" .
												 ",renderer: Ext.util.Format.dateRenderer('Y-m-d')";
								
								$dataReaderType = 'type: \'date\', dateFormat:\'Y-m-d\'';
								break;

							case 'datetime':
								$ColumnModule .= "editor: new Ext.form.DateField({format:'Y-m-d H:i:s'})\r\n" .
												 ",renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')";
								
								$dataReaderType = 'type: \'date\', dateFormat:\'Y-m-d H:i:s\'';
								break;

							case 'time':
								$ColumnModule .= "\r\neditor: new Ext.form.DateField({format:'H:i:s'})\r\n" .
												 ",renderer: Ext.util.Format.dateRenderer('H:i:s')";
								$dataReaderType = 'type: \'date\', dateFormat:\'H:i:s\'';
								break;
								
							case 'bool':
								$ColumnModule .= "editor: new Ext.form.Checkbox({value:'1'})";
								$dataReaderType = 'type: \'boolean\'';
								break;
								
							case 'menu':
								$ColumnModule .= "editor: new Ext.form.ComboBox({typeAhead:true, mode:'local', ".
									"triggerAction:'all', selectOnFocus:true, listClass:'x-combo-list-small', ".
									"store: new Ext.data.SimpleStore({fields:['id','name'], ".
									"data:[['changea','changea'],['changeab','changeb']] }), displayField:'name'})";
								break; 
						}
									
					}
					$ColumnModule .= "}";		
						
				 					
				} elseif($name!='id') {
					
					// hidden field!
					if(!empty($formItems)) $formItems .= ",";
					$formItems .= "{\r\n" . 
						"		name: '{$name}'\r\n" . 
						"		,xtype: 'hidden'\r\n".
						"	}";					
				}
				
				
				
				// data reader config
				if(!empty($dataReader)) $dataReader .= "	,";
				$dataReader .= '{name: \''.$name.'\'';
				if(!empty($dataReaderType)) $dataReader .= ", $dataReaderType"; 
				$dataReader .= "}\r\n"; 				
			}
		}
		
	 
		echo "<pre>"; 
		include_once 'includes/utility/dev-gen-ext.php'; 
		echo "</pre>";
			 
	}
	
	
	
	
	/**
	 * Look up the requested table in the current db and create the construct XML from the db schema 
	 */
	private static function xml_generate() 
	{
		$table = (string) preg_replace("/\"\'/","", sparam::get('table'));
		$save  = (bool) sparam::get('save');
		 
		// look up this table in the database
		$columns = sdb::all("explain $table"); 
		$cols = array();
		foreach($columns as $col) 
		{			
			extract($col);
			
			$xml = array(); 
			$xml['name'] = $Field;
			 
		 	
			if(preg_match("/^(set|enum)/", $Type)) {
				$xml['type'] = $Type;
			} else {
				if(preg_match("/([a-z]{3,})\(([0-9]{1,})\)/", $Type, $regs)) {
					$xml['type'] = $regs[1];
					$xml['maxLength'] = $regs[2]; 
				} else {
					$xml['type'] = $Type;
				}
			}
			
			// required?
			if($Null === 'NO')
				$xml['required'] = "1";
				
			// primary key?
			if($Key === 'PRI')
				$xml['primaryKey'] = "1";
				
			// autoInc
			if($Extra === 'auto_increment')
				$xml['autoIncrement'] = "1";
		 
			// push xml settings to cols
			array_push($cols, $xml);			
		}		
		
		// string the XML together
		$xml ='<'.'?xml version="1.0" encoding="UTF-8"?>';
		$xml .="\r\n". '<table id="'.$table.'">'; 
		foreach($cols as $col) 
		{
			$xml .= "\r\n  <column";
			foreach($col as $key => $val) $xml .= " ". $key . '="' . $val . '"'; 
			$xml .= ' />';
		} 
		$xml .= "\r\n</table>";
		 
		 
		// get existing xml
		$path = PROJECT_DIR . 'modules/' . $table . '/' .$table .'.xml' ;
		if(is_file($path)) 
			$oldXml = file_get_contents($path);

		// try to save
		if($save && $xml) {
			if(file_put_contents($path, $xml)) {
				echo '<b><span style="margin:10px;padding:5px;border:1px solid Red; background:#ffffcc;">Saved to '.$path.'</span></b>';
			} else {
				echo "<b>Unable to save to $path</b>";
			}
		}
 
		print '<h3>Generated XML</h3>'; 
		print '<pre style="background:#eee; padding:5px; margin:5px; border:1px dashed #ccc; font-size:12px;">';
		print htmlspecialchars($xml); 
		print '</pre>';
		
		if(isset($oldXml) && $xml != $oldXml) {
			print '<h2>Previous XML</h2>';
			print '<font color="Red">[changed]</font>';   
			print '<pre style="background:#eee; padding:5px; margin:5px; border:1px dashed #ccc; font-size:12px;">';
			print htmlspecialchars($oldXml); 
			print '</pre>';
		} else {
			print 'File unchanged or not already created.';	 
		}
	}	
}
?>
