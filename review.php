<!DOCTYPE html>

<?php 
require_once './CAS/config.php';
require_once 'CAS.php';
require_once 'config.php';
require_once 'database.php';
$username = isset($_POST['username'])?$_POST['username']:null;
if (!$username) {
	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	$username=phpCAS::getUser();
		
}
$justthese = array("cn");
$ds=ldap_connect(LDAP_HOST);
$sr=ldap_search($ds,LDAP_SEARCH,"cn=$username");
$dn = LDAP_SEARCH;
$f=LDAP_SEARCH_FILTER_FACULTY;
$filter = "(&(objectClass=Person)(cn=".$username.")(".$f.",o=trinity))";
$sr=ldap_search($ds, $dn, $filter,$justthese);
$count = ldap_count_entries($ds, $sr);
$usertype = null;
if ($count == 0)
{
	header("Location: auth_err.php?username=".$username."&usertype=faculty");
	exit();
}
else {
	$f=LDAP_SEARCH_FILTER_ADMINS;
	$filter = "(&(objectClass=Person)(cn=".$username.")(".$f.",o=trinity))";
	$sr=ldap_search($ds, $dn, $filter,$justthese);
	$count = ldap_count_entries($ds, $sr);
	if ($count == 0) {
		$usertype = "faculty";
	} else {
		$usertype = "admin";
	}
}
		
$dbo = DatabaseGateway::getInstance();
try {
	$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}
catch (mysqli_sql_exception $e) {
	throw $e;
}
$sql = 	"SELECT * FROM users WHERE username = '$username'";
$dbo->doQuery($sql);
if (!$dbo) {
	die("Query to list the table failed");
}

$row = $dbo->loadObjectList();
$userid = $row["idusers"];
$dbo->freeResults();
 
?>
<html>
	<head>
	<link rel="stylesheet" href="./jqwidgets/styles/jqx.base.css" type="text/css" />        
    <link rel="stylesheet" href="./jqwidgets/styles/jqx.classic.css" type="text/css" />
 <!--    <script type="text/javascript" src="./scripts/jquery-1.10.2.min.js"></script>  -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxdata.js"></script> 
    <script type="text/javascript" src="./jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxscrollbar.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxmenu.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxgrid.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxgrid.pager.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxgrid.selection.js"></script> 
    <script type="text/javascript" src="./jqwidgets/jqxwindow.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxlistbox.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxdropdownlist.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="./jqwidgets/jqxgrid.filter.js"></script>	
	<script type="text/javascript" src="./jqwidgets/jqxgrid.sort.js"></script>	
 	 <link rel="stylesheet" href="css/style.css" type="text/css" media="screen"/>
    <script type="text/javascript">
    /**
     * 
     */
                $(document).ready(function () {
                // prepare the data
                
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
            			userid: "<?php echo $userid ?>",
                    	action: "select",
                    	usertype: "<?php echo $usertype ?>" 
        			},    	
                    url: 'coursedata.php',
    			//    localdata: data,
    				cache: false,
    				sortcolumn: 'course_code',
    			    sortdirection: 'asc',
                    deleterow: function (rowid, commit) {
    			        // synchronize with the server - send update command
                        var data = "action=delete&idcourses="+rowid;
    					        					
    					$.ajax({
    						dataType: 'html',
    						url: 'coursedata.php',
    						type: 'POST',
    						data: data,
    						success: function (data, status, xhr) {
    							// update command is executed.
    							commit(true);
    						}
    					})
    				},
       	            addrow: function (rowid, rowdata, position, commit) {
                        // synchronize with the server - send insert command
    					 var data = "action=insert&course_code=" + rowdata.course_code + "&year=" + rowdata.year + "&term=" + rowdata.term + "&fk_user=" + <?php echo $userid ?>;
    					
    					$.ajax({
    						dataType: 'html',
    						url: 'coursedata.php',
    						type: 'POST',
    						data: data,
    						success: function (data, status, xhr) {
    							// update command is executed.
    							commit(true);
    						}
    					})
       	            },	
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

            
                var dataAdapter = new $.jqx.dataAdapter(source);
                var editrow = -1;
    	
                // initialize jqxGrid
                $("#jqxgrid").jqxGrid(
                {		
                	width: '400px',
                    source: dataAdapter,
                    pagesize:  '5',
                    theme: theme,
    				filterable: false,
    				sortable: false,
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
                          { text: 'Professor', datafield: 'lastname', width: 155 }
                     ]
    			    
                });
                      

                var args;
                var quizid;
                var studentid;
                	$("#jqxgrid").bind('rowselect', function (event) {
                	    var selectedRowIndex = event.args.rowindex;  
                	    var id = $("#jqxgrid").jqxGrid('getcellvalue', selectedRowIndex, "idcourses");
                   	    $("#jqxgrid2").show();
                   	 	$("#displayed").hide();
                   	 	$("#thumbsContainer").hide();
                   	 	$("#jqxgrid3").hide();
                   	    $("#addrowbutton2").show();
                   	    $("#deleterowbutton2").show();

                        getListbox(id);
                        $("#jqxgrid2").jqxGrid('unselectrow', 1);                   
                	});
                	$("#jqxgrid2").bind('rowselect', function (event) {
                	    var selectedRowIndex = event.args.rowindex;  
                	    quizid = $("#jqxgrid2").jqxGrid('getcellvalue', selectedRowIndex, "idquizzes");
                	    $("#jqxgrid3").show();
                   	 	$("#displayed").hide();
                   	 	$("#thumbsContainer").hide();             	    
                	    getstudentgrid(quizid);
                	    $("#jqxgrid3").jqxGrid('unselectrow', 1);
                	});

                	$("#jqxgrid3").bind('rowselect', getStudents);

    				function getStudents(event) {

    	           	    var selectedRowIndex = event.args.rowindex;  
    	        	    studentid = $("#jqxgrid3").jqxGrid('getcellvalue', selectedRowIndex, "fk_students");
    	        	    quizid = $("#jqxgrid3").jqxGrid('getcellvalue', selectedRowIndex, "fk_quizzes");
    	           	 	$("#displayed").show();
    	           	 	$("#thumbsContainer").show();
    	         	    getImages(studentid, quizid);

    	        	};
    	        		
                    $("#addrowbutton").jqxButton({ theme: theme, width: 180, height: 30 });
                    $("#deleterowbutton").jqxButton({ theme: theme, width: 180, height: 30 });
          	         $("#addrowbutton").bind('click', function () {  
       	                var rowscount = $("#jqxgrid").jqxGrid('getdatainformation').rowscount;
       	                var offset = $("#jqxgrid").offset();
       		            $("#popupWindow").jqxWindow({ position: { x: parseInt(offset.left) + 60, y: parseInt(offset.top) + 60 } });
       		
       		            // get the clicked row's data and initialize the input fields.
       		//            $("#jqxgrid").jqxGrid('addrow', null, rowscount+1);
       		            $("#course_code").val("");
       		            $("#year").val("");
       		            $("#term").val("");
       		         
       		            // show the popup window.
       		            $("#popupWindow").jqxWindow('open');
       	                
       	            });
     	            $("#deleterowbutton").bind('click', function () {
    	                var selectedrowindex = $("#jqxgrid").jqxGrid('getselectedrowindex');
    	                var rowscount = $("#jqxgrid").jqxGrid('getdatainformation').rowscount;
    	                if (selectedrowindex >= 0 && selectedrowindex < rowscount) {
    	                    var id = $("#jqxgrid").jqxGrid('getrowid', selectedrowindex);
    	                    $("#jqxgrid").jqxGrid('deleterow', id);
                        	$("#jqxgrid").jqxGrid('updatebounddata');   
                        	$("#jqxgrid").jqxGrid('refresh');  
                       	    $("#jqxgrid2").hide();
                       	    $("#addrowbutton2").hide();
                    	    $("#deleterowbutton2").hide();
                       	    $("#jqxgrid3").hide();
      	                    
    	                }
    	            });    
                    // sg popup - initialize the popup window and buttons.
                    $("#popupWindow").jqxWindow({
                        width: 280, resizable: false, isModal: true, autoOpen: false, cancelButton: $("#Cancel"), modalOpacity: 0.01           
                    });

                    $("#popupWindow").on('open', function () {
                        $("#course_code").jqxInput('selectAll');
                    });
                 
                    $("#Cancel").jqxButton();
                    $("#Save").jqxButton();

                    // update the edited row when the user clicks the 'Save' button.
                    $("#Save").bind("click", function () {
                      
                        if (editrow >= 0) {
							radio_val = $('input:radio[name=trm]:checked').val();
                            var row = { course_code: $("#course_code").val(), year: $("#year").val(), term: radio_val};
                            var rowID = $('#jqxgrid').jqxGrid('getrowid', editrow);
                            $('#jqxgrid').jqxGrid('updaterow', rowID, row);
                            $('#jqxgrid').jqxGrid('updatebounddata');
                            $("#popupWindow").jqxWindow('close');
                        }
                        else {
                        	var newrow={}; //REINITIALISE OTHERWISE SUBSEQUENT POPUPS WILL HAVE PREVIOUS VALUE.
                        	radio_val = $('input:radio[name=trm]:checked').val();
                        	var newrow = { course_code: $("#course_code").val(), year: $("#year").val(), term: radio_val};
                        	$("#jqxgrid").jqxGrid('addrow', null, newrow);     
                        	$("#jqxgrid").jqxGrid('updatebounddata');   
                        	$("#jqxgrid").jqxGrid('refresh');    
                        	$("#popupWindow").jqxWindow('close');
                        }   
                        $("#Save").die();
                    });       
                       var args;
                       var quizid;
                       var studentid;
                       	$("#jqxgrid").bind('rowselect', function (event) {
                       	    var selectedRowIndex = event.args.rowindex;  
                       	    var id = $("#jqxgrid").jqxGrid('getcellvalue', selectedRowIndex, "idcourses");
                       	    $("#quiz_label").show();
                       	    $("#quiz_section").show();
                       	    $("#addrowbutton2").show();
            	                 getListbox(id);
                               
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
                            { name: 'fk_courses', type: 'int'},
                            { name: 'purge_date', type: 'datetime'},
                            
                        ],
                        id:  'idquizzes',
        				type:  'POST',
            			data: {
                			coursekey: id,
                        	action: "select"
            			},
                        url: 'quizdata.php',
        				root: 'Rows',
                        deleterow: function (rowid, commit) {
        			        // synchronize with the server - send update command
                            var data = "action=delete&quizkey="+rowid;
        					        					
        					$.ajax({
        						dataType: 'html',
        						url: 'quizdata.php',
        						type: 'POST',
        						data: data,
        						success: function (data, status, xhr) {
        							// update command is executed.
        							commit(true);
        						}
        					})
        				},
         	            addrow: function (rowid, rowdata, position, commit) {
                            // synchronize with the server - send insert command
        					 var data = "action=insert&quiz_name=" + rowdata.quiz_name + "&duration=" + rowdata.duration + "&purge_date=" + rowdata.purge_date +"&fk_courses=" + id;
        					
        					$.ajax({
        						dataType: 'html',
        						url: 'quizdata.php',
        						type: 'POST',
        						data: data,
        						success: function (data, status, xhr) {
        							// update command is executed.
        							commit(true);
        						}
        					})
                        },
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
                    var editrow = -1;
        			
        			$("#jqxgrid2").jqxGrid(
                    {
                      	width: '190px',
                        source: dataAdapter,
                        theme: 'classic',
        				filterable: false,
        				sortable: false,
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
                              { text: 'Time', datafield: 'duration', width: 70 },
                              { text: 'fk_courses', datafield: 'fk_courses', width: 0, hidden:true },
                              { text: 'purge_date', datafield: 'purge_date', width: 100}
                        ]
                    }); 
                     $("#addrowbutton2").jqxButton({ theme: 'classic', width: 180, height: 30 });
                     $("#deleterowbutton2").jqxButton({ theme: 'classic', width: 180, height: 30 });
         	         $("#addrowbutton2").bind('click', function () {  
      	                var rowscount = $("#jqxgrid2").jqxGrid('getdatainformation').rowscount;
      	                var offset = $("#jqxgrid2").offset();
      		            $("#popupWindow2").jqxWindow({ position: { x: parseInt(offset.left) + 60, y: parseInt(offset.top) + 60 } });
      		
      		            // get the clicked row's data and initialize the input fields.
      		//            $("#jqxgrid").jqxGrid('addrow', null, rowscount+1);
      		            $("#quiz_name").val("");
      		            $("#duration").val("");
      		          	$("#purge_date").val("");
      		                       		         
      		            // show the popup window.
      		            $("#popupWindow2").jqxWindow('open');
      	                
      	            });
     	            $("#deleterowbutton2").bind('click', function () {
    	                var selectedrowindex = $("#jqxgrid2").jqxGrid('getselectedrowindex');
    	                var rowscount = $("#jqxgrid2").jqxGrid('getdatainformation').rowscount;
    	                if (selectedrowindex >= 0 && selectedrowindex < rowscount) {
    	                    var id = $("#jqxgrid2").jqxGrid('getrowid', selectedrowindex);
    	                    $("#jqxgrid2").jqxGrid('deleterow', id);
                        	$("#jqxgrid2").jqxGrid('updatebounddata');   
                        	$("#jqxgrid2").jqxGrid('refresh');  
                       	    $("#jqxgrid3").hide();
    	                }
    	            });  
                   // sg popup - initialize the popup window and buttons.
                   $("#popupWindow2").jqxWindow({
                       width: 280, height: 175, resizable: false, isModal: true, autoOpen: false, cancelButton: $("#Cancel2"), modalOpacity: 0.01           
                   });

                   $("#popupWindow2").on('open', function () {
                       $("#quiz_name").jqxInput('selectAll');
                   });
                
                   $("#Cancel2").jqxButton();
                   $("#Save2").jqxButton();

                   // update the edited row when the user clicks the 'Save' button.
  //                
                   $("#Save2").bind("click",function () {
  //              	   $(this).unbind('click');

                       if (editrow >= 0) {
                           var row = { quiz_name: $("#quiz_name").val(), duration: $("#duration").val()};
                           var rowID = $('#jqxgrid2').jqxGrid('getrowid', editrow);
                           $('#jqxgrid2').jqxGrid('updaterow', rowID, row);
                           $("#popupWindow2").jqxWindow('close');

                       }
                       else {
	                       	var newrow={}; //REINITIALISE OTHERWISE SUBSEQUENT POPUPS WILL HAVE PREVIOUS VALUE.
	                        	var newrow = { quiz_name: $("#quiz_name").val(), duration: $("#duration").val(), purge_date:$("#purge_date").val()};
	                       	$("#jqxgrid2").jqxGrid('addrow', null, newrow);     
	                      	$("#jqxgrid2").jqxGrid('updatebounddata');   
	                      	$("#jqxgrid2").jqxGrid('refresh');    
	                       	$("#popupWindow2").jqxWindow('close');
	                         
                       }   
                      $("#Save2").die();
                   });            
                };

                
                function getstudentgrid (id) {
                    // prepare the data
                         var source3 = 
                {
                     datatype: "json",
                     datafields: [
   //          			 { name: 'idquiz_logs', type: 'int'},
                         { name: 'lastname', type: 'string'},
    					 { name: 'firstname', type: 'string'},
    					 { name: 'fk_quizzes', type: 'int'},
    					 { name: 'fk_students', type: 'int'}
                    ],
   			//		id:  'lastname',
   			    	type:  'POST',
        			data: {
            			quizid: id
        			},
                    url: "studentquizdata.php",
    			//    localdata: data,
    				cache: false,
    				sortcolumn: 'lastname',
    			    sortdirection: 'asc',
    				filter: function()
    				{
    					// update the grid and send a request to the server.
    					$("#jqxgrid3").jqxGrid('updatebounddata', 'filter');
    				},
    				sort: function()
    				{
    					// update the grid and send a request to the server.
    					$("#jqxgrid3").jqxGrid('updatebounddata', 'sort');
    				},
    				root: 'Rows',
    				beforeprocessing: function(data)
    				{		
    					if (data != null)
    					{
    						source3.totalrecords = data[0].TotalRows;
    				//		source.totalrecords = 11;					
    					}
    				},
                };		
     //           var dataadapter = new $.jqx.dataAdapter(source);
    		    var dataadapter3 = new $.jqx.dataAdapter(source3, {
    					loadError: function(xhr, status, error)
    					{
    						alert(error);
    					}
    				}
    			);

            
                var dataadapter3 = new $.jqx.dataAdapter(source3);
                var editrow3 = -1;
    	
                // initialize jqxGrid
                $("#jqxgrid3").jqxGrid(
                {		
                	width: '275px',
                    source: dataadapter3,
                    theme: 'classic',
    				filterable: false,
    				sortable: false,
    				autoheight: true,
    				pageable: false,
    				virtualmode: true,
    				rendergridrows: function(obj)
    				{
    					 return obj.data;    
    				},
    			    columns: [
		//				  { text: 'idquiz_logs', datafield: 'idquiz_logs', width: 0,  hidden:true },
                          { text: 'Last Name', datafield: 'lastname', width: 155 },
                          { text: 'First Name', datafield: 'firstname', width: 120 },
                          { text: 'fk_quizzes', datafield: 'fk_quizzes', width: 0,  hidden:true },
                          { text: 'fk_students', datafield: 'fk_students', width: 0,  hidden:true }
                     ]
    			    
                }); 
           };   
    </script>
   
    
		<meta charset="utf-8">
		<title>Auto Proctor</title>
        <style>
            body {
	            font-family: sans-serif;
	            font-size: 17px;
	            line-height: 24px;
	            width: 100%;
	            height: 100%;
	            margin: 20px;
	            margin-right:  20px;
	            text-align: center;
	            background:  black;
            }

            #info {
	            width: 100%;
	            height: 30px;
	            top: 50%;
                color:red;
	            margin-top: 115px;
            }

            #output {
	            width: auto;
	            height: 60%;
	            background: black;
	            /*-webkit-transform: scale(-1, 1);*/   /*Flip horizontally */
            }
             #example {
	           float:  right;
	           width:  25%;
	           font-size: 14px;
	           line-height: 17px;
	           background:  white;
	           text-align: left;
	           margin-right: 60px;
	           padding:  20px;
            }
             #lookups {
	           
	           font-size: 14px;
	           line-height: 17px;
	           color:  white;
	           text-align: center;
	           margin-top:  100px;
            }
		</style>
		<script type="text/javascript" src="jquery.gallery.js"></script>
	</head>
	<body>
		 <div align="center" id="jqxWidget">  
          
      		<div align="right" id="jqxgrid" style="margin-top: 10px; float:  left; margin-left:  20px;" ></div>
      		  		  <div style="margin-top: 10px; float:  left;">
             <div id="cellbegineditevent"></div>
                <div style="margin-left: 20px;">
                   <input id="addrowbutton" type="button" value="Add Course" />
               </div>
           <div style="margin-top: 5px;margin-left: 20px;">
                <input id="deleterowbutton" type="button" value="Delete Course" />
            </div>
            
        	</div>
      	 	<div align="right" id="jqxgrid2" style="margin-top: 10px;  float:  left; margin-left:  20px;" ></div>
      	 	 		   <div style="margin-top: 10px; float:  left;margin-left:  10px">
             <div id="cellbegineditevent"></div>
                <div style="margin-top: 0px;">
                   <input id="addrowbutton2" type="button" value="Add Quiz" style="display:none" />
               </div>
               <div style="margin-top: 5px;">
                   <input id="deleterowbutton2" type="button" value="Delete Quiz" style="display:none" />
               </div>
            </div>	
       	 	<div align="right" id="jqxgrid3" style="margin-top: 10px; float:  left; margin-left:  20px;"></div>
            <div id="popupWindow">
            <div>Course</div>
            <div style="overflow: hidden;">
                <table>
                    <tr>
                        <td align="right">Course Code:</td>
                        <td align="left"><input id="course_code" /></td>
                    </tr>
                    <tr>
                        <td align="right">Year: (YYYY)</td>
                        <td align="left"><input id="year" /></td>
                    </tr>
                    <tr>
                        <td align="right">Term: </td>
                        <td align="left">
                        <input type=radio name="trm" value="FA" checked="checked"/> FA
                        <input type=radio name="trm" value="SP"/> SP
                        <input type=radio name="trm" value="SU"/> SU
                        </td>
                    </tr>
					<tr>
                        <td align="right"></td>
                        <td style="padding-top: 10px;" align="right"><input style="margin-right: 5px;" type="button" id="Save" value="Save" /><input id="Cancel" type="button" value="Cancel" /></td>
                    </tr>          	
                </table>
            </div>
        </div>
 	    <div id="popupWindow2">
            <div id=quiz_label style="display:none;">Quiz</div>
            <div id=quiz_section style="overflow: hidden; display:none;">
                <table>
                    <tr>
                        <td align="right">Quiz Name:</td>
                        <td align="left"><input id="quiz_name" /></td>
                    </tr>
                    <tr>
                        <td align="right">Duration: (minutes)</td>
                        <td align="left"><input id="duration" /></td>
                    </tr>
                    <tr>
                        <td align="right">Purge Date: (yyyy-mm-dd)</td>
                        <td align="left"><input id="purge_date" /></td>
                    </tr>
					<tr>
                        <td align="right"></td>
                        <td style="padding-top: 10px;" align="right"><input style="margin-right: 5px;" type="button" id="Save2" value="Save" /><input id="Cancel2" type="button" value="Cancel" /></td>
                    </tr>          	
                </table>
            </div>
      	  </div>
   		</div>  
           <div id="loading"></div>
        
        <div id="preview">
            <div id="imageWrapper"> 

          	</div>  
		</div>
        <div id="thumbsWrapper">
        </div>
 	</body>
</html>