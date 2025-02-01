<?php

$db_info = array(
	'dbType' => 'mysql',
	'dbHost' => null,
	'dbName' => null, 
	'dbUser' => null,
	'dbPassword' => null
);


$smtp_settings = array (
	 'smtpuser' => 'user@gmail.com', 
	 'smtppass' => '<<password >>',
	 'smtphost' => 'smtp.gmail.com',
	 'smtpsecure' => 'tls',
	 'smtpport' => '587',
	 'from' => 'from@gmail.com',
	 'name' => 'Uw systeem beheerder'
);


$app_settings = array(
	'DbLogging' => true,			// Log database queries naar logfile
	'DbError' => true,				// Log errors naar logfile
	'Debug' => true,				// Debug informatie naar logfile, uitzetten voor productie
	'BaseDir' => '',				// Basis directory op de server
	'LogDir' => '/tmp/log/helios/',	// Locatie waar log bestanden geschreven worden
	'Vereniging' => "GeZC",
	'DemoMode' => true,
	'ApiKeySMS' => 'API key here',	// api key voor messagebird
	'KeyJWT' => '480f4p*%ghouiEWf*DXKz22Vy7RDzFeaBlw329zMyHh*o',	// versleutel JWT token, AANPASSEN voor productie !!!
	'dataNotModified' => 304,
	'2Factor' => true,				// 2Factor authenticatie aan (true) of uit (false)
	'bypassToken' => 'jgHoTYuic3oesKQ8B6_rzDNPvrL8Fzy6L*3.KxYQ',		// AANPASSEN voor productie
	'PaxBevoegdheid' => 271			// CompetentieID van pax. Let op !!  CreateViews aanroepen, na wijzigen. Dit zit namelijk in database views
);


// dataNotModified heeft uitleg nodig. Wanneer client data opvraagt van server met een hash, dan wordt gekeken of de hash van de opgehaalde dataset
// hetzelfde is. Zo ja, dan heeft de client al de laatste data en hoeft de data niet nogmaals naar de client gestuurd te worden. 
// Dat scheelt data overdracht en dat is fijn voor mobiele verbindingen
// In het HTTP protocol is daarover nagedacht en is de status code 304 aanwezig (zie https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304)
// 
// Maar nu komt het, apache heeft een vervelende bug. Zie https://bz.apache.org/bugzilla/show_bug.cgi?id=61820 Deze bug is opgelost in versie 2.4.48 
// Wat is de bug? Bij een status code van 304 worden alle headers van de http response verwijderd door apache, en dat is een probleem.
// Indien je CORS gebruikt in je website om helios aan te roepen, gaat je browser klagen dat er geen Access-Control-Allow-Origin header zijn
// Zie run-flarm.php voor de implementatie in helios.

// Er zijn twee oplossingen:
// 		0) Upgrade apache
// 		1) Maak geen gebruik van CORS, bijvoorbeeld om website en helios in dezelfde subdomain te zetten of maak gebruik van een proxy
//      2) Vervang 304 voor een andere http status code

// Optie 0 moet worden gedaan door je internet provider, optie 2 is niet altijd mogelijk of wenselijk, blijft over optie 2
// Omdat (op termijn) optie 0 van toepassing kan zijn, is de status code 304 configureerbaar. Bijvoorbeeld 704 als custom error status

$strippen = array(
	0 => array(
		'STRIPPEN' => 12,
		'BEDRAG' => 48,
		'KOSTEN' => 1,
        'OMSCHRIJVING' => '12 strippen, € 48.00 plus € 1.00 transactiekosten'
    ),
    1 => array(
		'STRIPPEN' => 25,
		'BEDRAG' => 100,
		'KOSTEN' => 1,
        'OMSCHRIJVING' => '25 strippen, € 100.00 plus € 1.00 transactiekosten'
    ),
	2 => array(
		'STRIPPEN' => 50,
		'BEDRAG' => 200,
		'KOSTEN' => 1,
        'OMSCHRIJVING' => '50 strippen, € 200.00 plus € 1.00 transactiekosten'
    ),
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

