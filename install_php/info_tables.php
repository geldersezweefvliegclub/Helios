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

$key = sha1(strtolower ($username) . $password);

if (($username != $installer_account['username']) || ($key != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}

$retValue = array(); 

$heliosObjecten = scandir("lib");
foreach ($heliosObjecten as $heliosObj)
{
    if (strpos($heliosObj, '.php') !== false)
    {
        $source_code = file_get_contents("lib/" . $heliosObj, "r");
        
        preg_match("/class .*extends.*Helios/", $source_code, $class_matches);
        if (count($class_matches) > 0)
        {
            preg_match("/this->dbTable.*=.*/", $source_code, $tabel_matches);
            if (count($tabel_matches) > 0)
            {
                $heliosClass = explode(" ", $class_matches[0]);

                $obj_info = array();
                $obj_info['class'] = $heliosClass[1];
               
                $obj = MaakObject($heliosClass[1]);
                $obj_info['bestaat'] = $obj->bestaatTabel();
                array_push($retValue, $obj_info);
            }
        }
    }
}
echo json_encode($retValue);
?>