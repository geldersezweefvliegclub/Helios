<?php

chdir(".."); // terug naar de home directory

// Wachtwoord om te installeren
if(!file_exists('installer_account.php'))
{
    header('HTTP/1.0 401 Unauthorized');
    die();    
}


include('installer_account.php');
include('include/functions.php');
include('include/helios.php');

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.0 400 Wrong request');
    die();
} 

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$key = sha1(strtolower ($username) . $password);

if (($username != $installer_account['username']) || ($key != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}
?>