<?php
$db_hostname='98.130.0.118';
$db_database='dvirji_mygann';
$db_username='dvirji_mygann';
$db_password='Murtaza1';



$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());



?>