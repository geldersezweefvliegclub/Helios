<?php

$db_info = array(
	'dbType' => 'mysql',
	'dbHost' => null,
	'dbName' => null, 
	'dbUser' => null,
	'dbPassword' => null
);

/* Voor de toekomst
$smtp_settings = array (
	 'smtpuser' => 'user@gmail.com', 
	 'smtppass' => '<<password >>',
	 'smtphost' => 'smtp.gmail.com',
	 'smtpsecure' => 'tls',
	 'smtpport' => '587',
	 'from' => 'from@gmail.com'
);
*/

$app_settings = array(
	'DbLogging' => false,			// Log database queries naar logfile
	'DbError' => false,				// Log errors naar logfile
	'Debug' => false,				// Debug informatie naar logfile, uitzetten voor productie
	'LogDir' => '/tmp/log/helios/',	// Locatie waar log bestanden geschreven worden
	'Vereniging' => "GeZC",
	'ApiKeySMS' => 'API key here'	// api key voor messagebird
);


// Wachtwoord om te installeren
if(file_exists('installer_account.php'))
	include 'installer_account.php';

// In include/database.inc.php staat de MariaDB / MySQL implementatie
// Bij gebruik van een andere database moet deze implementatie aangepast worden
if (!IsSet($GLOBALS['DBCONFIG_PHP_INCLUDED']))
{
	include('include/database.inc.php');
	$GLOBALS['DBCONFIG_PHP_INCLUDED'] = 1;	
	
	global $db;
	$db = new DB();
	try 
	{
		$db->Connect();
	}
	catch (Exception $exception) {}
}

?>
