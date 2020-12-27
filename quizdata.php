
<?php
require_once 'config.php';
require_once 'database.php';
$coursekey = isset($_POST['coursekey'])?$_POST['coursekey']:null;
$action = isset($_POST['action'])?$_POST['action']:null;
$quizkey = isset($_POST['quizkey'])?$_POST['quizkey']:null;

$dbo = DatabaseGateway::getInstance();
	try {
		$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	}
   catch (mysqli_sql_exception $e) {
			throw $e;
	}
	$sql = "";
	if ($action == "insert")
	{
		if (!empty($_POST))
		{
			$keys = "";
			$values = "";
			$sql = "INSERT INTO quizzes";
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
		$sql1 = "SELECT a.filename FROM quiz_logs a, quizzes b WHERE b.idquizzes = a.fk_quizzes and b.idquizzes = '$quizkey'";
		$dbo->doQuery($sql1);
		if (!$dbo) {
			die("Query failed");
		}
		
		while ($row = $dbo->loadObjectList()) {
			unlink($row['filename']);
		}
		$dbo->freeResults();
		
		//step 2:  Cascade DB Deletes courses, quizzes, quiz_logs.
		$sql = 	"DELETE FROM auto_proctor.quizzes WHERE idquizzes = '$quizkey'";
	}
	elseif ($action == "select")
	{
		$sql = 	"SELECT * From quizzes WHERE fk_courses = $coursekey";
	}	
//echo $sql;	
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
$data[] = array(
		'TotalRows' => "{$dbo->getTotalRows()}",
		'Rows' => $dataResults);

$dbo->freeResults();
	
echo json_encode($data);
}
?>