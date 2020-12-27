<?php


// Load the settings from the central config file
require_once './CAS/config.php';
// Load the CAS lib
//require_once $phpcas_path . '/CAS.php';
require_once 'CAS.php';
require_once 'config.php';
$usertype = isset($_REQUEST['usertype'])?$_REQUEST['usertype']:null;
// Enable debugging
phpCAS::setDebug();


// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
//phpCAS::proxy(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
//phpCAS::handleLogoutRequests(true,array("antioch.tiu.edu"));
// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
//phpCAS::setCasServerCACert('/etc/ssl.crt/tiu.edu.crt');

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();


 $username=phpCAS::getUser();
 //$username='sgeggie';
 $ds=ldap_connect(LDAP_HOST);
 $sr=ldap_search($ds,LDAP_SEARCH,"cn=$username");
 $info=ldap_get_entries($ds,$sr);
 $firstname = $info[0][LDAP_FN][0];
 $lastname = $info[0][LDAP_LN][0];
 $email =  $info[0][LDAP_EMAIL][0];
 

 ?>
 <script type="text/javascript">
 	var ajax = new XMLHttpRequest();
 	var fn = '<?php echo $firstname ?>';
 	var ln = '<?php echo $lastname ?>';
 	var usr = '<?php echo $username ?>';
 	var email = '<?php echo $email ?>';
 	var usrtype = '<?php echo $usertype ?>';
 	

    ajax.open("POST", "registeruser.php", false);
 	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    postData = "&usertype="+usrtype+"&firstname="+fn+"&lastname="+ln+"&email="+email+"&username="+usr;
    ajax.send(postData);
 </script>


// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().
<?php 
// logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}

// for this test, simply print that the authentication was successful
if ($usertype == "student") {
	$target = "record_event";
}
elseif ($usertype == 'faculty'){
	$justthese = array("cn");
	$dn = LDAP_SEARCH;
	$f=LDAP_SEARCH_FILTER_FACULTY;
	$filter = "(&(objectClass=Person)(cn=".$username.")(".$f.",o=trinity))";
	$sr=ldap_search($ds, $dn, $filter,$justthese);
	$count = ldap_count_entries($ds, $sr);
	if ($count > 0)
		$target = "review";
	else 
		$target = "auth_err";	
}

?>


<html>
  <head>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>


    <title>AutoProctor</title>
  </head>
  <body>

  <form action="<?php echo $target ?>.php" method="POST" id="form">
  <input type="hidden" name="username" value="<?php echo $username; ?>"><br>
  <input type="hidden" name="usertype" value="<?php echo $usertype; ?>"><br>

</form>
	 <script type="text/javascript">
       	$("#form").submit();
        </script>
    </body>
</html>