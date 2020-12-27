<?php
require_once 'config.php';
require_once 'database.php';

$studentid= isset($_REQUEST['studentid'])?$_REQUEST['studentid']:null;
$quizid = isset($_REQUEST['quizid'])?$_REQUEST['quizid']:null;
$imagesArr	= array();
$i		= 0;
if ($studentid && $quizid) { 
	
		$dbo = DatabaseGateway::getInstance();
			try {
				$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			}
		   catch (mysqli_sql_exception $e) {
					throw $e;
			}
		$sql = 	"SELECT filename, snaptime FROM auto_proctor.quiz_logs WHERE fk_students = $studentid AND fk_quizzes=$quizid ORDER BY snaptime ASC";
					
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
			
		
		
		echo json_encode($dataResults, JSON_UNESCAPED_SLASHES);
}	
?>