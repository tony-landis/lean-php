Ext.namespace('Ext.cfg');


/** Data Reader Config for Ext.data.Record
 *
 * @usage new Ext.data.Record.create(Ext.cfg.<?= sparam::get('module') ?>DataReader)
 */
Ext.cfg.<?= sparam::get('module') ?>DataReader = [
	<?= $dataReader ?>
];


/** Data Reader Config for Ext.data.JsonReader()
 *
 * @usage reader: new Ext.data.JsonReader(Ext.cfg.<?= sparam::get('module') ?>JsonReader) 
 */
Ext.cfg.<?= sparam::get('module') ?>JsonReader = {
	id: 'id'
	,totalProperty: 'nodes.<?= sparam::get('module') ?>_count'
	,root: 'nodes.<?= sparam::get('module') ?>'
}


/** DataStore Config for Ex
 *
 * @usage new Ext.data.Store(Ext.cfg.<?= sparam::get('module') ?>Store);
 */
Ext.cfg.<?= sparam::get('module') ?>Store = {  
	id: '<?= sparam::get('module') ?>Store'
	,url: '../<?= sparam::get('module') ?>/doSearch.json' 
	,autoLoad: false
	,sortInfo:{field: 'id', direction: 'DESC'}
	,reader: new Ext.data.JsonReader(Ext.cfg.<?= sparam::get('module') ?>JsonReader, new Ext.data.Record.create(Ext.cfg.<?= sparam::get('module') ?>DataReader)) 
	,listeners: {
		'beforeload': function(t){t.removeAll()}
	}
}


/** Add Form Config
 *
 * @usage new Ext.FormPanel(Ext.cfg.<?= sparam::get('module') ?>AddForm).renderTo('???');
 */
Ext.cfg.<?= sparam::get('module') ?>AddForm = {
	id:'<?= sparam::get('module') ?>AddForm' 
	,title:false
	,labelWidth: 100
	//,labelAlign: 'top'
	,defaultType: 'textarea' 
	,autoHeight: true
	,bodyStyle: 'padding:8px;'
	,frame: true
	,items: [<?= $formItems ?>]
	,buttons: [{
		text: 'Save'
		,handler:function(){
			Ext.getCmp("<?= sparam::get('module') ?>AddForm").getForm().submit({ 
				url:'../<?= sparam::get('module') ?>/doAdd.json'
				,params:{}
				,success:globalFormResponseHandler
				,failure:globalFormResponseHandler
			});
		}
	}]
	,onSaveSuccess: function(r,j) {}
}
 

/** Open a window for the Add Form
 *  
 * @usage new Ext.Window(Ext.cfg.<?= sparam::get('module') ?>Window).show(this);
 */
Ext.cfg.<?= sparam::get('module') ?>Window = { 
	id: '<?= sparam::get('module') ?>Window'
	,title:'Add New <?= ucfirst(str_replace("_"," ",sparam::get('module'))) ?>'  
	,layout:'fit'
	,width:400
	,height:'auto'
	,autoHeight: true
	,closeAction:'hide' 
	,shadow: false
	,transparent: false
	,items: new Ext.FormPanel(Ext.cfg.<?= sparam::get('module') ?>Form)
	,listeners: {
		'beforerender': function() { 
			try{
				if(Ext.getCmp('<?= sparam::get('module') ?>Window').rendered == true) return false;   
			}catch(e){}
			return true;  
		}
	}
}


/** Column Module Config for Ext.grid.ColumnModel()
 *
 * @usage cm: new Ext.grid.ColumnModel(Ext.cfg.<?= sparam::get('module') ?>ColumnModel)
 */
Ext.cfg.<?= sparam::get('module') ?>ColumnModel = [
	<?= $ColumnModule ?>
	
];


/** Grid Config for 
 *
 * @usage new Ext.grid.EditorGridPanel(Ext.cfg.<?= sparam::get('module') ?>Grid)
 */
 
Ext.cfg.<?= sparam::get('module') ?>Grid = function (renderTo) {
	
	var cm = new Ext.grid.ColumnModel(Ext.cfg.<?= sparam::get('module') ?>ColumnModel);
	var store = new Ext.data.Store(Ext.cfg.<?= sparam::get('module') ?>Store);
	
	var cfg = {
		id: '<?= sparam::get('module') ?>Grid',
	    title: false
	    //,height: 400
		,autoHeight: true
		,iconCls: 'icon-grid'
	    ,cm: cm
	    ,store: store
		,frame: true 
	    ,loadMask: true
	    ,viewConfig: {forceFit: true} 
	    ,listeners: {'render': function(t) {t.store.load({params:{start:0, limit:15}})} } 
		,clicksToEdit: 1
	    ,buttonAlign: 'center'  
	    
	    // selector
	    ,sm: new Ext.grid.RowSelectionModel({
	    	singleSelect: true
	    })
	    	    
	    // paging
	    ,bbar: new Ext.PagingToolbar({
		  	pageSize: 15
		    ,store: store
		    ,displayInfo: true
		    ,displayMsg: 'Displaying results {0} - {1} of {2}'
		    ,emptyMsg: "No results to display" 
		    ,items: [
		        '-'
		         ,{text:'Reload', pressed:true, handler:function() {store.reload()}}
		         ,'-'
		         
		         // editing buttongs
		         ,new Ext.Button({text:'Save Changes', id: '<?= sparam::get('module') ?>GridBtnSave', disabled:true, handler: function(store){ 
		         	var store = Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore();
		         	store.save(store);
		         }}) 
		         ,new Ext.Button({text:'Cancel Changes', id: '<?= sparam::get('module') ?>GridBtnCncl', disabled:true, handler: function(store){ 
		         	Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore().rejectChanges(); 
		         }})
		         ,'-'
		         
		         // text search
		         ,new Ext.form.TextField({
		         	value: 'search...'
		         	,listeners: {
		         		'change': function(t) {
		         			Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore().load({params:{query:t.getValue(), start:0, limit:15}});
		         		}
		         		,'focus': function(t) {t.setValue()}
		         	}
		         }) 
		         
		         // insert new record
		         ,'-'
		         ,{text:'Add Record', handler:function() {  
		 			var nr = new Ext.data.Record({
		 				<?= $newDataRecord ?>
		 				
		 			});  
					var store = Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore(); 
		 			store.insert(0, nr); 
		 			Ext.getCmp('<?= sparam::get('module') ?>Grid').startEditing(0,0); }
		 		} 	
		 		 
				// delete row
				,'-'
				,{text:'Delete', id:'<?= sparam::get('module') ?>GridDelBtn', disabled:true, handler: function() {
					this.disable();
					Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore().dodelete();
					}
				}		 		
			]
		})
	}
	
	// add listener on the store for inline grid editing
	store.on('update', function(store,record,c) {
		if(c=='commit' || c=='reject') {
			Ext.getCmp('<?= sparam::get('module') ?>GridBtnSave').disable();
			Ext.getCmp('<?= sparam::get('module') ?>GridBtnCncl').disable(); 	
		} else if(c=='edit') {
			Ext.getCmp('<?= sparam::get('module') ?>GridBtnSave').enable();
			Ext.getCmp('<?= sparam::get('module') ?>GridBtnCncl').enable(); 
		}
	});
	
	// add handler to store for inline grid editing
	store.save = function(s) { 
		var da = []; 
		var records = s.getModifiedRecords();
		for(var i = 0; i < records.length; i++) { da.push(Ext.encode( Ext.apply({}, records[i].data) )) } 
		Ext.Ajax.request({	
			url: '../<?= sparam::get('module') ?>/doEditMulti.json', params: {json: '[' + da.join(',') + ']' }, 
			success: function() { s.commitChanges();  }, 
			failure: function() { alert('Save failed') } 
		});
	}
  
	// render the grid
	var grid = new Ext.grid.EditorGridPanel(cfg);
	
	// deletion stuff on grid and store
	store.dodelete = function(rowIdx) {
		var sel = Ext.getCmp('<?= sparam::get('module') ?>Grid').getSelectionModel().getSelected(); 
		Ext.Ajax.request({
			url: '../<?= sparam::get('module') ?>/doDelete.json'
			,params: {id: sel.id}
			,success: function(result, json) {  
				var store = Ext.getCmp('<?= sparam::get('module') ?>Grid').getStore();
				store.remove( store.getById(sel.id) );
			}
			,failure: function() { alert('Deletion failed');  }
		});  
	}	
	grid.getSelectionModel().on('rowselect', function(sm, rowIdx, r) { 
		var button = Ext.getCmp('<?= sparam::get('module') ?>GridDelBtn').enable(); 
	}); 
		
	
	if(renderTo) grid.render(renderTo);	
	return grid;       
}
