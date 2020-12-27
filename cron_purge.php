
<?php
require_once 'config.php';
require_once 'database.php';


$dbo = DatabaseGateway::getInstance();
	try {
		$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	}
   catch (mysqli_sql_exception $e) {
			throw $e;
	}

	//step 1:  Delete Files Associated with the course
	$sql1 = "SELECT a.filename FROM quiz_logs a, quizzes b WHERE b.idquizzes = a.fk_quizzes and date(b.purge_date) < curdate()";
	$dbo->doQuery($sql1);
	if (!$dbo) {
		die("Query failed");
	}
	
	while ($row = $dbo->loadObjectList()) {
		unlink($row['filename']);
	}
	$dbo->freeResults();
	
	//step 2:  Cascade DB Deletes courses, quizzes, quiz_logs.
	
$sql = 	"DELETE FROM quizzes where date(purge_date) < curdate()";
$dbo->doQuery($sql);
if (!$dbo) {
	die("Query to list the table failed");
}

?>