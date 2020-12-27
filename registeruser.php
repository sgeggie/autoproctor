<?php
require_once 'config.php';
require_once 'database.php';

$lastname = isset($_REQUEST['lastname'])?$_REQUEST['lastname']:null;
$firstname = isset($_REQUEST['firstname'])?$_REQUEST['firstname']:null;
$email = isset($_REQUEST['email'])?$_REQUEST['email']:null;
$username = isset($_REQUEST['username'])?$_REQUEST['username']:null;
$usertype = isset($_REQUEST['usertype'])?$_REQUEST['usertype']:null;

if ($usertype == null) {
	$usertype="faculty";
}
	$dbo = DatabaseGateway::getInstance();
		try {
			$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}
	   catch (mysqli_sql_exception $e) {
				throw $e;
		}
	$sql = 	"INSERT INTO `auto_proctor`.`users` (`firstname`,`lastname`,`username`,`email`,`usertype`) VALUES ('$firstname','$lastname','$username','$email','$usertype')";
	
	$dbo->doQuery($sql);
	
?>