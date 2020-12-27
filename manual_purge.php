
<?php
require_once 'config.php';
require_once 'database.php';
require_once './CAS/config.php';
require_once 'CAS.php';
$year = isset($_REQUEST['year'])?$_REQUEST['year']:null;
$term = isset($_REQUEST['term'])?$_REQUEST['term']:null;
/*
	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	$username=phpCAS::getUser();
	$justthese = array("cn");
	$ds=ldap_connect(LDAP_HOST);
	$sr=ldap_search($ds,LDAP_SEARCH,"cn=$username");
	$dn = LDAP_SEARCH;
	$f=LDAP_SEARCH_FILTER_ADMINS;
	$filter = "(&(objectClass=Person)(cn=".$username.")(".$f.",o=trinity))";
	$sr=ldap_search($ds, $dn, $filter,$justthese);
	$count = ldap_count_entries($ds, $sr);
	if ($count == 0)
	{
		echo("You are not authorized to access this page");
		exit();
	}
*/

$dbo = DatabaseGateway::getInstance();
	try {
		$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	}
   catch (mysqli_sql_exception $e) {
			throw $e;
	}

	//step 1:  Delete Files Associated with the course
	$sql1 = "SELECT a.filename FROM quiz_logs a, quizzes b, courses c WHERE b.idquizzes = a.fk_quizzes AND b.fk_courses = c.idcourses AND c.term='".$term."' AND c.year='".$year."'";
	$dbo->doQuery($sql1);
	if (!$dbo) {
		die("Query failed");
	}
	
	while ($row = $dbo->loadObjectList()) {
		unlink($row['filename']);
	}
	$dbo->freeResults();
	
	//step 2:  Cascade DB Deletes courses, quizzes, quiz_logs.
	
$sql = 	"DELETE FROM courses WHERE term='".$term."' AND year='".$year."'";
$dbo->doQuery($sql);
if (!$dbo) {
	die("Query to list the table failed");
}

?>