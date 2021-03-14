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
include('include/config.php');

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

$postdata = file_get_contents("php://input");
$db_tables = json_decode($postdata, true);

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password); // nodig om sessie data te zetten


// aanmaken tabellen
$retValue['tabel'] = array();
$retValue['view'] = array();

for ($i = 0 ; $i < count($db_tables) ; $i++)
{      
    $table = $db_tables[$i]['class'];          

    $obj = MaakObject($table);
    $obj->CreateViews();    
}

?>