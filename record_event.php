<!DOCTYPE html>
<?php 
require_once 'config.php';
require_once 'database.php';
require_once './CAS/config.php';
require_once 'CAS.php';

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
$f=LDAP_SEARCH_FILTER_STUDENTS;
$filter = "(&(objectClass=Person)(cn=".$username.")(".$f.",o=trinity))";
$sr=ldap_search($ds, $dn, $filter,$justthese);
$count = ldap_count_entries($ds, $sr);
if ($count == 0)
{
	header("Location: auth_err.php?username=".$username."&usertype=student");
	exit();
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
  
    <script src="coursegrid.js" type="text/javascript"></script>
   
    <script src="listbox.js" type="text/javascript"></script>
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
	</head>
	<body>
	      <div id="example">
        <article>
        <img src="sample.png" height=240 width=320></img>

        <p>Hello <?php echo $username ?></p>
        <p>Welcome to Auto-Proctor.  During your exam, you will be recorded.</p>
        <hr>
        <p>Please select your course and quiz.  </p>
        <p>To verify your identify please present your license or student id, then click the "capture" button.</p>
         <hr>
        <p>Do not close this browser tab.  Simply click on the appropriate tab to get back to your course and begin your exam.</p>
        </article>
        </div>
	    <div id="camera">
		<p id="info">Please allow access to your camera!</p>
		<canvas id="output" style="border:1px solid #000000; display:block;"></canvas>
        <br/>
        </div>
        <div id="controls">
        <input id="button" type="button" value="Capture" onclick="btnCapture()" />
        <br />
        </div>
         <div align="center" id="jqxWidget">
          
      		<div align="center" id="jqxgrid" style="margin-top: 30px; float:  left;" ></div>
      	 	<div align="center" id="jqxgrid2" style="margin-top: 30px; float:  left; margin-left:  10px" ></div>
         </div>
 <!-- 
        <script src="HTML5Webcam.js" type="text/javascript"></script>
 -->
 	<?php 
		include 'HTML5Webcam.php';
	?>
	</body>
</html>