/**
 * 
 */
 //	selectedrowindex = $("#jqxgrid").jqxGrid('getselectedrowindex');
//	var id = $("#jqxgrid").jqxGrid('getrowid', selectedrowindex);
var args;
var quizid;
var expiration;
$(document).ready(function () {
	$("#jqxgrid").bind('rowselect', function (event) {
	    var selectedRowIndex = event.args.rowindex;  
	    var id = $("#jqxgrid").jqxGrid('getcellvalue', selectedRowIndex, "idcourses");
        getListbox(id);
        
	});
	$("#jqxgrid2").bind('rowselect', function (event) {
	    var selectedRowIndex = event.args.rowindex;  
	    quizid = $("#jqxgrid2").jqxGrid('getcellvalue', selectedRowIndex, "idquizzes");
	    expiration = $("#jqxgrid2").jqxGrid('getcellvalue', selectedRowIndex, "duration");
	    //    alert (quizid.label + ": " + quizid.value);
    $("#camera").show();
    $("#controls").show();
	});
});	
	
        function getListbox (id) {
            // prepare the data
              var row;
              var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'idquizzes', type: 'int'},
                    { name: 'quiz_name', type: 'string'},
                    { name: 'duration', type: 'int'},
                    { name: 'fk_courses', type: 'int'}
                    
                ],
                id:  'idquizzes',
				type:  'POST',
    			data: {
        			coursekey: id,
                	action: "select"
    			},
                url: 'quizdata.php',
				root: 'Rows',
				beforeprocessing: function(data)
				{		
					if (data != null)
					{
						source.totalrecords = data[0].TotalRows;
				//		source.totalrecords = 11;					
					}
				},
            };

            var dataAdapter = new $.jqx.dataAdapter(source);
			
			$("#jqxgrid2").jqxGrid(
            {
              	width: '15%',
                source: dataAdapter,
                theme: 'classic',
				filterable: false,
				sortable: true,
				autoheight: true,
				pageable: false,
				virtualmode: true,
				rendergridrows: function(obj)
				{
					 return obj.data;    
				},
			    columns: [
                      { text: 'ID', datafield: 'idquizzes', width: 0, hidden:true },
                      { text: 'Quiz', datafield: 'quiz_name', width: 120 },
                      { text: 'Duration', datafield: 'duration', width: 70 },
                      { text: 'fk_courses', datafield: 'fk_courses', width: 0, hidden:true }
                ]
            });        
        };