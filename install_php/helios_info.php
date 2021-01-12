<?php

chdir(".."); // terug naar de home directory

include('include/functions.php');
include('include/helios.php');

$retValue = array();

$retValue['db_info'] = InitGedaan();
$retValue['installer_account'] = file_exists("installer_account.php");

echo json_encode($retValue);
?>