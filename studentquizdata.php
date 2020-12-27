
<?php
require_once 'config.php';
require_once 'database.php';

$quizid = isset($_POST['quizid'])?$_POST['quizid']:null;

$dbo = DatabaseGateway::getInstance();
	try {
		$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	}
   catch (mysqli_sql_exception $e) {
			throw $e;
	}
$sql = 	"SELECT distinct b.lastname, b.firstname, a.fk_quizzes, a.fk_students FROM auto_proctor.quiz_logs a, auto_proctor.users b WHERE a.fk_students = b.idusers";
if ($quizid) {
	$sql .= "   AND a.fk_quizzes =  '$quizid'";
}
$dbo->doQuery($sql);
if (!$dbo) {
	die("Query to list the table failed");
}


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

?>