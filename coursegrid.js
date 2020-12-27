/**
 * 
 */
            $(document).ready(function () {
            // prepare the data
            $("#camera").hide();
            $("#controls").hide();
            var theme = 'classic';
            var row;
            var source = 
            {
                 datatype: "json",
                 datafields: [
					 { name: 'idcourses', type: 'int'},
					 { name: 'course_code', type: 'string'},
					 { name: 'year', type: 'string'},
					 { name: 'term', type: 'string'},
					 { name: 'lastname', type: 'string'}
                ],
				id:  'idcourses',
				type:  'POST',
    			data: {
        			  	action: "select"
    			},
                url: "coursedata.php",
			//    localdata: data,
				cache: false,
				sortcolumn: 'course_code',
			    sortdirection: 'asc',
				filter: function()
				{
					// update the grid and send a request to the server.
					$("#jqxgrid").jqxGrid('updatebounddata', 'filter');
				},
				sort: function()
				{
					// update the grid and send a request to the server.
					$("#jqxgrid").jqxGrid('updatebounddata', 'sort');
				},
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
 //           var dataadapter = new $.jqx.dataAdapter(source);
		    var dataadapter = new $.jqx.dataAdapter(source, {
					loadError: function(xhr, status, error)
					{
						alert(error);
					}
				}
			);

        
            var dataadapter = new $.jqx.dataAdapter(source);
            var editrow = -1;
	
            // initialize jqxGrid
            $("#jqxgrid").jqxGrid(
            {		
            	width: '30%',
                source: dataadapter,
                theme: theme,
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
                      { text: 'ID', datafield: 'idcourses', width: 0, hidden:true },
                      { text: 'Course Code', datafield: 'course_code', width: 120 },
                      { text: 'Year', datafield: 'year', width: 50 },
                      { text: 'Term', datafield: 'term', width: 75 },
                      { text: 'Professor', datafield: 'lastname', width: 175 }
                 ]
			    
            });
            
       });
    