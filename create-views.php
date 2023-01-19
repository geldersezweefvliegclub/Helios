<?php

/*
===========================================================================================================
Hieronder geen wijzingen aanbrengen !!
===========================================================================================================
*/


$classes = array("TypesGroepen",
                 "Types",
                 "Documenten",
                 "Competenties",
                 "Vliegtuigen",
                 "Gasten", 
                 "Leden", 
                 "Rooster", 
                 "Diensten", 
                 "Daginfo",
                 "DagRapporten",
                 "Startlijst", 
                 "AanwezigVliegtuigen", 
                 "AanwezigLeden", 
                 "Progressie", 
                 "Tracks",
                 "Reservering",
                 "Transacties",
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


if (!file_exists("installer_account.php"))
{
    die ("Eerst installer uitvoeren");       
}
if (!file_exists("include/config.php"))
{
    die ("Config bestand ontbreekt");       
}

include("installer_account.php");
include("include/config.php");

/*
-----------------------------------------------------------------------------------------------------------
Inloggen
-----------------------------------------------------------------------------------------------------------
*/

// Als we ID als parameter meegegeven, dan gebruiken we die om username & wachtwoord te bepalen
if (isset($_GET["id"])) 
{
    $decodedString = base64_decode($_GET["id"], true);

    if ($decodedString !== false) 
    {
        $parts = explode(',', $decodedString); 

        if (count($parts) == 2)
        {
            $username = $parts[0];
            $password = $parts[1];
        }
    }
}

$key = sha1(strtolower ($username) . $password);

if (($username != $installer_account['username']) || ($key != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    echo "Geen toegang";
    die();    
}


/*
-----------------------------------------------------------------------------------------------------------
Maak de views
-----------------------------------------------------------------------------------------------------------
*/

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password);	

foreach ($classes as $c)
{
    echo "$c" . "</br>";
    $obj = MaakObject($c);
    $obj->CreateViews();
}
?>
