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

if (InitGedaan())
{
    header('HTTP/1.0 409 Conflict');
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
    header('HTTP/1.0 404 Database not found');
    die();
}


// bestaat de database
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

?>