<?php
$username = isset($_REQUEST['username'])?$_REQUEST['username']:null;
$usertype = isset($_REQUEST['usertype'])?$_REQUEST['usertype']:null;
?>
<html>
 	<head>
 	</head>
 	<body>
 	<h2>Authorization Error</h2>
 	<p>
 	Your username (<?php echo $username ?>) is not a member of the <?php echo $usertype ?> group.  Please contact the I.T. Help Desk.
 	</p>
 	</body>
 </html>	