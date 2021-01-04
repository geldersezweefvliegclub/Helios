<?php

chdir(".."); // terug naar de home directory

include('include/functions.php');
include('include/helios.php');

if (InitGedaan() == true)
{
    header('HTTP/1.0 409 Conflict');
    die();
}

// inloggen mag niet te vaak, om brute force attacks te voorkomen
if (magInloggen() == false)
{
    laatsteInlogPoging();
    header('HTTP/1.0 401 Unauthorized');
    die();
}

$retVal = array();

$postdata = file_get_contents("php://input");
$obj = json_decode($postdata, true);

$dbHost = $obj["databaseHost"];
$dbNaam = $obj["databaseNaam"];
$dbUser=  $obj["databaseGebruiker"];
$dbPassword = $obj["databaseWachtwoord"];

// kunnen we verbinding maken naar de database engine
$conn = new mysqli($dbHost, $dbUser, $dbPassword);
if ($conn->connect_error) 
{
    laatsteInlogPoging();

    $retVal["dbError"] = true;
    die(json_encode($retVal));
}
$retVal["dbError"] = false;

// bestaat de database
// @ om warning msg te onderukken als datbase niet bestaat
if (@mysqli_connect($dbHost, $dbUser, $dbPassword, $dbNaam) == false)
{
    $retVal["dbBestaat"] = false;
    die(json_encode($retVal));
}
$retVal["dbBestaat"] = true;

echo json_encode($retVal);

function laatsteInlogPoging()
{
    $file_content = date(DATE_ISO8601, time());

    $file = fopen("install_error_db.txt", "w") or die("Unable to write file!");
    fwrite($file, $file_content);
    fclose($file);   
}

function magInloggen()
{
    if (!file_exists("install_error_db.txt"))
        return true;

    $nu = time();    
    $timestamp = strtotime(file_get_contents("install_error_db.txt", "r"));
    $diff = $nu - $timestamp;

    if ($diff > 15)
        return true;

    return false;    
}

?>