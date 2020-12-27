<?php
require_once 'config.php';
require_once 'database.php';

$imageData = isset($_POST['snapshot'])?$_POST['snapshot']:null;
$studentid = isset($_POST['studentid'])?$_POST['studentid']:null;
$quizid = isset($_POST['quizid'])?$_POST['quizid']:null;
$id_res = isset($_POST['id_res'])?$_POST['id_res']:null;
// Remove the headers (data:,) part.
// A real application should use them according to needs such as to check image type
$filteredData=substr($imageData, strpos($imageData, ",")+1);

// Need to decode before saving since the data we received is already base64 encoded
$unencodedData=base64_decode($filteredData);


// Save file. This example uses a hard coded filename for testing,
// but a real application can specify filename in POST variable
list($usec,$sec) = explode(' ', microtime());  /* usec will contain hundredths of a second */
$usec = round($usec, 3);
$usec=str_replace("0.","_",$usec);
date_default_timezone_set('America/Chicago');
$objDateTime = new DateTime('NOW');
$filename = IMAGE_PATH."image_".$objDateTime->format('Y_m_d_His').$usec.".png";
$fp = fopen( $filename, "wb" );
fwrite( $fp, $unencodedData);
fclose( $fp );
if (!$id_res) {
	$img = imagecreatefrompng($filename);
	imagejpeg($img,$filename,IMAGE_COMPRESSION);
	imagedestroy($img);
}


$dbo = DatabaseGateway::getInstance();
		try {
			$dbo->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}
	   catch (mysqli_sql_exception $e) {
				throw $e;
		}
	$sql = 	"INSERT INTO `auto_proctor`.`quiz_logs`
(`fk_quizzes`,
`fk_students`,
`snaptime`,
`filename`)
VALUES
('$quizid',
 '$studentid',
  now(),
'$filename')";
	
	$dbo->doQuery($sql);
		
?>

