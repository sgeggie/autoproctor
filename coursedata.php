
<?php
require_once 'config.php';
require_once 'database.php';

$userid = isset($_POST['userid'])?$_POST['userid']:null;
$action = isset($_POST['action'])?$_POST['action']:null;
$idcourses = isset($_POST['idcourses'])?$_POST['idcourses']:null;
$usertype = isset($_POST['usertype'])?$_POST['usertype']:null;

$dbo = DatabaseGateway::getInstance();
	try {
		$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	}
   catch (mysqli_sql_exception $e) {
			throw $e;
	}
if ($action == "insert")
	{
		$keys = "";
		$values = "";
		if (!empty($_POST))
		{
			$sql = "INSERT INTO courses";
			foreach ($_POST as $key=>$value)
			{
				if ($key != "Save" && $key !="Cancel" && $key != "action")
				{
					$keys .= "{$key},";
					$values .= "'{$value}',";
				}
				}
				$sql .= "(".substr($keys, 0, -1).") Values (".substr($values, 0, -1).")";
		}
	}
elseif ($action == "delete")
   {
		//step 1:  Delete Files Associated with the course
   	    $sql1 = "SELECT a.filename FROM quiz_logs a, quizzes b, courses c WHERE  c.idcourses = b.fk_courses AND b.idquizzes = a.fk_quizzes and c.idcourses = '$idcourses'";
   	    $dbo->doQuery($sql1);
   	    if (!$dbo) {
   	    	die("Query failed");
   	    }

   	    while ($row = $dbo->loadObjectList()) {
			unlink($row['filename']);
   	    }
   	    $dbo->freeResults();
   	    
		//step 2:  Cascade DB Deletes courses, quizzes, quiz_logs.   	
   		$sql = 	"DELETE FROM auto_proctor.courses WHERE idcourses = '$idcourses'";
		if ($userid) {
			$sql .= " AND a.fk_user = '$userid'";
		}
	}
elseif ($action == "select")
	{	
		if ($usertype == "admin")
		{
			$sql = 	"SELECT a.idcourses, a.course_code, a.year, a.term, b.lastname FROM auto_proctor.courses a, auto_proctor.users b WHERE a.fk_user = b.idusers";
		}
		else 
		{		
			$sql = 	"SELECT a.idcourses, a.course_code, a.year, a.term, b.lastname FROM auto_proctor.courses a, auto_proctor.users b WHERE a.fk_user = b.idusers";
			if ($userid) {
				$sql .= " AND a.fk_user = '$userid'";
			}
		}
	}
$dbo->doQuery($sql);
if (!$dbo) {
	die("Query to list the table failed");
}

if ($action == 'select')
{	
	$i=0;
	while ($row = $dbo->loadObjectList()) {
		$dataResults[$i] = $row;
		$i++;
	}
	$dbo->freeResults();
		
	$data[] = array(
			'TotalRows' => "{$dbo->getTotalRows()}",
			'Rows' => $dataResults
	);

echo json_encode($data);
}
?>
