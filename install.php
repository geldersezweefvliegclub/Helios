<?php

$length = 10;
$username = "helios";
$password = isset($_POST['password']) ? $_POST['password'] : substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))),1, $length);

$retValue = array();

if (isset($_GET['sha1']))
{
	echo sha1($_GET['sha1']);
	exit;
}

if(!file_exists('installer_account.php'))
{
	$file_content = sprintf(
		"<?php

		\$installer_account = array(
			'id' => 0,
			'username' => '%s',
			'password' => '%s'
		);
		
		?>", $username, sha1($password));

	$file = fopen("installer_account.php", "w") or die("Unable to open installer_account.php file!");
	fwrite($file, $file_content);
	fclose($file);

	$retValue['password'] = $password;
}

include('include/config.php');
include('include/functions.php');
include('include/startadmin.php');

$maak_database = isset($_POST['maak_database']) ? $_POST['maak_database'] : "true";

if (strtoupper($maak_database) == "TRUE")
{
	$l = MaakObject('Login');
	$l->verkrijgToegang($username, $password);		// het stopt hier als de gebruiker niet bevoegd is

	// aanmaken database
	global $db_info;

	$conn = new mysqli($db_info['dbHost'], $db_info['dbUser'], $db_info['dbPassword']);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	// Create database
	$sql = "CREATE DATABASE " . $db_info['dbName'];
	if ($conn->query($sql) === TRUE) {
		echo "Database created successfully";
	} else {
		echo "Error creating database: " . $conn->error;
	}

	$conn->close();


	// aanmaken tabellen
	$classes = [ "Types", "Competenties", "DagInfo", "Vliegtuigen", "Leden", "Rooster", "AanwezigLeden", "AanwezigVliegtuigen", "Startlijst", "Tracks", "Progressie" ];

	$retValue['tabel'] = array();
	$retValue['view'] = array();
	
	foreach ($classes as $class)
	{
		$obj = MaakObject($class);

		$obj->CreateTable(false);
		array_push($retValue['tabel'], $class);

		$obj->CreateViews();
		array_push($retValue['view'], $class);
	}
	$l->Logout();
}

echo json_encode($retValue)
?>