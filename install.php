<?php

$dbHost     = "mariadb";            // server waar de database staat
$dbNaam     = "heliostest";         // naam van de database
$dbUser     = "root";               // database gebruiker
$dbPassword = "rootroot";           // wachtwoord van de database gebruiker

$fillData = true;                   // vullen database met voorbeeld records

/*
===========================================================================================================
Hieronder geen wijzingen aanbrengen !!
===========================================================================================================
*/


$classes = array("Types", 
                 "Vliegtuigen", 
                 "Leden", 
                 "Rooster", 
                 "Diensten", 
                 "Daginfo", 
                 "Startlijst", 
                 "AanwezigVliegtuigen", 
                 "AanwezigLeden", 
                 "Competenties", 
                 "Progressie", 
                 "Tracks",
                 "Reservering",
                 "Audit");

include('include/functions.php');
include('include/helios.php');

if (!isset($_SERVER))
{
    die ("aanroepen script via browser en niet via CLI");
}

/*
-----------------------------------------------------------------------------------------------------------
Aanmaken installer account
-----------------------------------------------------------------------------------------------------------
*/

// als installer account nog niet bestaat, gebruiken we huidige credentials
$username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
$password = isset($_SERVER['PHP_AUTH_PW'])   ? $_SERVER['PHP_AUTH_PW'] : null;  

$toonCredentials = false;

if (!file_exists("installer_account.php"))
{
    if ($username == null) 
    {
        $ascii = "AaBbCcDeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
        $username = substr(str_shuffle($_SERVER['SERVER_ADDR'] . $ascii), 0, 10);
        $password = substr(str_shuffle($_SERVER['REMOTE_ADDR'] . $ascii), 0, 15);

        $toonCredentials = true;
    }
    
    $key = sha1(strtolower ($username) . $password);
    
    $file_content = sprintf(
        "<?php
    
        \$installer_account = array(
            'id' => 0,
            'username' => '%s',
            'password' => '%s'
        );
        
        ?>", $username, $key);
    
    $file = fopen("installer_account.php", "w") or die("Unable to open installer_account.php file!");
    fwrite($file, $file_content);
    fclose($file);

    if ($toonCredentials) 
    {
        $output = array ('username' => $username , 'password' => $password);
        echo json_encode($output);
    }
       
}

include("installer_account.php");

/*
-----------------------------------------------------------------------------------------------------------
Inloggen
-----------------------------------------------------------------------------------------------------------
*/

$key = sha1(strtolower ($username) . $password);

if (($username != $installer_account['username']) || ($key != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}


/*
-----------------------------------------------------------------------------------------------------------
Laden config file als deze bestaat. Kan eerder aangemaakt zijn
-----------------------------------------------------------------------------------------------------------
*/
if (file_exists("include/config.php"))
{
    include("include/config.php");

    $dbHost     = $db_info['dbHost'];
    $dbNaam     = $db_info['dbName'];
    $dbUser     = $db_info['dbUser'];
    $dbPassword = $db_info['dbPassword'];
}

/*
-----------------------------------------------------------------------------------------------------------
Kunnen we verbinding maken naar de database engine
-----------------------------------------------------------------------------------------------------------
*/

$conn = new mysqli($dbHost, $dbUser, $dbPassword);
if ($conn->connect_error) 
{
    die("database connection error");
}

/*
-----------------------------------------------------------------------------------------------------------
Bestaat de database ?
-----------------------------------------------------------------------------------------------------------
*/

$dbBestaat = true;
// @ om warning msg te onderukken als datbase niet bestaat
if (@mysqli_connect($dbHost, $dbUser, $dbPassword, $dbNaam) == false)
    $dbBestaat = false;

/*
-----------------------------------------------------------------------------------------------------------
Als de database niet bestaat, aanmaken database
-----------------------------------------------------------------------------------------------------------
*/

if (!$dbBestaat)
{
    // @ om warning msg te onderukken als datbase niet bestaat
    if (@mysqli_connect($dbHost, $dbUser, $dbPassword, $dbNaam) == false)
    {
        // de database bestaat niet, aanmaken
        $sql = "CREATE DATABASE " . $dbNaam;
        if ($conn->query($sql) !== TRUE) 
        {
            header('HTTP/1.0 417 Expectation Failed');
            die();  
        }
    }
}

/*
-----------------------------------------------------------------------------------------------------------
Aanmaken van config bestand
-----------------------------------------------------------------------------------------------------------
*/

if (!file_exists("include/config.php"))
{
    // nu config file genereren vanuit de template
    $template = file_get_contents("include/config_empty.php");

    $pattern = "/'dbHost'.*=>.*null/";
    $template = preg_replace($pattern, sprintf("'dbHost' => '%s'", $dbHost), $template);

    $pattern = "/'dbName'.*=>.*null/";
    $template = preg_replace($pattern, sprintf("'dbName' => '%s'", $dbNaam), $template);

    $pattern = "/'dbUser'.*=>.*null/";
    $template = preg_replace($pattern, sprintf("'dbUser' => '%s'", $dbUser), $template);

    $pattern = "/'dbPassword'.*=>.*null/";
    $template = preg_replace($pattern, sprintf("'dbPassword' => '%s'", $dbPassword), $template);

    $file = fopen("include/config.php", "w") or die("Unable to open config.php file!");
    fwrite($file, $template);
    fclose($file);
}

include("include/config.php");

/*
-----------------------------------------------------------------------------------------------------------
Nu tabellen aanmaken
-----------------------------------------------------------------------------------------------------------
*/

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password);	

foreach ($classes as $c)
{
    try 
    {
        $obj = MaakObject($c);
        $obj->CreateTable($fillData);
    }
    catch(Exception $e) {}
}


/*
-----------------------------------------------------------------------------------------------------------
Maak de views
-----------------------------------------------------------------------------------------------------------
*/

foreach ($classes as $c)
{
    $obj = MaakObject($c);
    $obj->CreateViews();
}

?>