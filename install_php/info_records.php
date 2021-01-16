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

if (($username != $installer_account['username']) || (sha1($password) != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}

$postdata = file_get_contents("php://input");
$db_tables = json_decode($postdata, true);

if (!is_array($db_tables))
{
    header('HTTP/1.0 406 Not Acceptable');
    die();    
}

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password); // nodig om sessie data te zetten


$retValue = array();

for ($i = 0 ; $i < count($db_tables) ; $i++)
{      
    $table = $db_tables[$i]['class'];          

    $args = array('LAATSTE_AANPASSING' => true);
    switch(strtoupper($table))
    {
        case "AANWEZIGLEDEN" :
        case "AANWEZIGVLIEGTUIGEN" :
        case "STARTLIJST" :
        {
            $args['BEGIN_DATUM'] = "1990-01-01";
            $args['EIND_DATUM'] = "2090-01-01";
            break;
        }
    }

    $obj = MaakObject($table);
    $gobjects = $obj->GetObjects($args);   
    
    $obj_info = array();
    $obj_info['class'] = $table;
    $obj_info['totaal'] = $gobjects['totaal'];
    $obj_info['laatste_aanpassing'] = $gobjects['laatste_aanpassing'];
   
    array_push($retValue, $obj_info);
}
echo json_encode($retValue);

?>