<?php
// Database Constants
define("IMAGE_PATH","./snapshots/");
define("LDAP_HOST", "antioch.tiu.edu");
define("LDAP_SEARCH", "o=trinity");
define("LDAP_FN", "givenname");
define("LDAP_LN","sn");
define("LDAP_EMAIL", "mail");
define("LDAP_SEARCH_FILTER_FACULTY", "groupMembership=cn=dotcms_employees,ou=groups");
define("LDAP_SEARCH_FILTER_STUDENTS", "groupMembership=cn=dotcms_employees,ou=groups");
define("LDAP_SEARCH_FILTER_ADMINS", "groupMembership=cn=AutoProctor Administrators,ou=groups");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "auto_proctor");

define("IMAGE_COMPRESSION", 85);   /* % of total resolution for optimizing space for images */
define("MIN_INTERVAL", 7);
define("MAX_INTERVAL", 13);

?>