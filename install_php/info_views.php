<?php

chdir(".."); // terug naar de home directory

// Wachtwoord om te installeren
if(!file_exists('installer_account.php'))
{
    header('HTTP/1.0 401 Unauthorized');
    die();    
}


include('installer_account.php');
include('include/config.php');
include('include/functions.php');
include('include/helios.php');

if (InitGedaan() == false)
{
    header('HTTP/1.0 404 Not found');
    die();
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.0 400 Wrong request');
    die();
} 

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

if (($username != $installer_account['username']) || (sha1($password) != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}

$postdata = file_get_contents("php://input");
$db_tables = json_decode($postdata, true);

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password); // nodig om sessie data te zetten

$retValue = array();

for ($i = 0 ; $i < count($db_tables) ; $i++)
{      
    $table = $db_tables[$i]['class'];          
    $obj = MaakObject($table);
    
    $obj_info = array();
    $obj_info['class'] = $table;

    $source_code = file_get_contents('lib/class.' . $table . '.inc.php');
    preg_match_all("/DROP VIEW IF EXISTS.*/", $source_code, $view_matches);

    foreach ($view_matches[0] as $view)
    {
        $heliosView = explode(" ", $view)[4];
        $pos = strpos($heliosView, '"');
        $heliosView = trim(substr($heliosView,0,$pos));

        if (strpos($heliosView, 'verwijderd_') === false)
            $obj_info['bestaat'] = $obj->bestaatTabel($heliosView);
        else
            $obj_info['verwijderd'] = $obj->bestaatTabel($heliosView);
        
    }
    array_push($retValue, $obj_info);
}
echo json_encode($retValue);

?>