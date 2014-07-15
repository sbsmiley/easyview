 /**
 * * Easyview primary file to load
 * *
 * * @package easyview report
 * * @copyright 2014 UC Regents
 * * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
 * */

	///////////////////////////////////////////////////
        //// define static part of columns for the grid////
        ///////////////////////////////////////////////////
        var COLUMNS = [  
			{ header: 'Name (filter) all students',         locked: true, width:150,dataIndex:'last', resizable:true,
                                renderer: function(v, cellValues, rec) {
                                        return rec.get('last') + ', ' + rec.get('first');
                                },
                                editor: {
                                        xtype: 'textfield'
                                },
                                items: {
                                        xtype: 'textfield',
                                        flex : 1,
                                        margin: 2,
                                        enableKeyEvents: true,
                                        listeners: {
                                                keyup: function() {
                                                        var store = this.up('tablepanel').store;
                                                        store.clearFilter();
							var group_filter_select = Ext.ComponentQuery.query('#group_filter_select')[0];
							group_filter_select.clearValue();
                                                        if (this.value) {
                                                                store.filter({property: 'name', value: this.value, anyMatch: true, caseSensitive: false});
                                                        }
                                                },
                                                buffer: 500
                                        }
                                },
				
                                summaryRenderer: function(value, summaryData, dataIndex) {
                                        return "Averages"; 
                                },itemId:"info1"
                        }, 
                        { header: 'Perm',       dataIndex: 'perm',      locked: true,   hidden: hide_details, width:85,  itemId:"info2"}, 
                        { header: 'Email',      dataIndex: 'email',     locked: true,   hidden: hide_details, itemId:"info3" }, 
                        { header: 'Group(s)',      dataIndex: 'group',     locked: true,   hidden:hide_details,itemId:"info4" }, 
                        { header:'',      xtype:'actioncolumn',   locked: true,   width:40,       align:'center', 
                                items:[{        
                                        icon: 'resources/table.png',
                                        tooltip: 'Grade Report',
                                        handler: function(grid, rowIndex, colIndex) {
                                                var url =WROOT+"/grade/report/user/index.php?userid="+grid.panel.store.data.items[rowIndex].data.userid+"&id="+COURSEIDPASSEDIN;
                                                var win = window.open(url, 'easygradeuserreport');
                                                win.focus();
                                        }
                                }],itemId:"info5",
                        },
        ];
 	///////////////////////////////////////////////////
        //// define static part of model for the grid//////
        ///////////////////////////////////////////////////
        var MODEL = [   { name:'first',         type:'string'}, 
                        { name: 'last',         type: 'string' },
                        { name: 'name',         type: 'string' },
                        { name: 'perm',         type: 'int' },
                        { name: 'courseid',     type: 'int' },
                        { name: 'userid',       type: 'int' },
                        { name: 'email',        type: 'string' },
                        { name: 'group',        type: 'string' },
        ];
	var MODEL_FIELDS = ['first','last','name','perm','courseid','userid','email','group'];
        //////////////////////////////////////////////////////
        // iterating through grade items and//////////////////
        //pushing on dynamic parts of column and model////////
        //////////////////////////////////////////////////////
        
        for (var i = 0; i < grade_items.length; i++){
                
                grade_items[i]['id'] = String(grade_items[i]['id']);
		if(grade_items[i]['type']=='category' && hide_categories){
			var hidden_var = true;
		}else{
			var hidden_var = false;
		}
                //column array points a gradeitem id to its readable name
                COLUMNS.push({header: grade_items[i]['name'], dataIndex: grade_items[i]['gid'], width:100,
				hidden:hidden_var,
				summaryType:'average', 
                                tooltip: grade_items[i]['name'], 
				locked:grade_items[i]['locked'],//locked set in grade_grade_items function
				itemId:(grade_items[i]['type']+i+grade_items[i]['cat_name']).replace(/["'()%\[\]{}\\=+\s&#@\^,\.!\$\*-]/g,''),
                                /*renderer:function(value, metaData, record, rowIdx, colIdx, store, view){ 
                                        var column = view.getHeaderAtIndex(colIdx);
                                        var dataIndex = column.dataIndex;
                                        var feedback  = dataIndex+'feedback';
                                        if (record.get(feedback)!=""){
                                                metaData.tdAttr = 'data-qtip="'+record.get(feedback).replace(/<(.*?)>/g,"")+'"'; 
                                        }
                                        return value;
                                },*/
                                summaryRenderer: function(value, summaryData, dataIndex) {
                                        return parseFloat(value).toFixed(2); 
                                }
				
                }); 
                //model array defines which grade item ids each student should have a score for (specifying it as an int for now)
                MODEL.push({name: grade_items[i]['gid'], type:'float'});
                MODEL.push({name: grade_items[i]['feedback'], type:'string'});
		MODEL_FIELDS.push(grade_items[i]['gid']);
		MODEL_FIELDS.push(grade_items[i]['feedback']);
                
        }
        COLUMNS.push({header:"",width:1,itemId:'empty'});//add empty column to fix bug where avg gets overwritten by first student score
        FEWCOLUMNS = COLUMNS.slice(0,1).concat(COLUMNS.slice(4));
