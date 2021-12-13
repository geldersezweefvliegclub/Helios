<?php

if (!function_exists('InitGedaan'))
{
	// Bevat db_info (uit include/config.php) de info voor de database
	function InitGedaan()
	{
		return file_exists("include/config.php");
	}
}

// Instanteer een class, maar laad het php bestand eerst
if (!function_exists('MaakObject'))
{
	function MaakObject($className)
	{
		$includedChk = sprintf('%s_PHP_INCLUDED', strtoupper($className));
		if (!IsSet($GLOBALS[$includedChk]))
		{
			include_once('lib/class.' . $className . '.inc.php');
			$GLOBALS[$includedChk] = 1;
		}
		$obj = new $className;
		return $obj;
	}
}

// In welke sub directory staat onze software
if (!function_exists('url_base'))
{
	function url_base() 
	{
		$path = explode('/', $_SERVER['PHP_SELF']);
		$path[count($path)-1] = "";
		return implode('/', $path);
	}
}

// De debug functie, schrijft niets als de globale setting UIT staat
if (!function_exists('Debug'))
{
	function Debug($file, $line, $text)
	{
		global $app_settings;
					
		if ($app_settings['Debug'])
		{
			$arrStr = explode("/", $file); 
			$arrStr = array_reverse($arrStr );
			$arrStr = explode("\\", $arrStr[0]);
			$arrStr = array_reverse($arrStr );

			$toLog = sprintf("%s: %s (%d), %s\n", date("Y-m-d H:i:s"), $arrStr[0], $line, $text);

			if ($app_settings['LogDir'] == "syslog")
			{
				error_log($toLog);
			}
			else
			{	
				error_log($toLog, 3, $app_settings['LogDir'] . "debug.txt");
			}
		}
	}
}

// Is de waarde een CSV string met integers
function isCSV($value, $veld = false, $nullToegestaan = false)
{
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (strpos($value, ',') !== false)
	{
		foreach (explode(",", $value) as $field)
		{
			if (filter_var($field, FILTER_VALIDATE_INT) === false)
			{
				if ($veld !== false)
					throw new Exception(sprintf("405;%s CSV waarde moet bestaan uit integers;", $veld));

				return false;
			}
		}
	}
	else
	{
		if (filter_var($value, FILTER_VALIDATE_INT) === false)
		{
			if ($veld !== false)
				throw new Exception(sprintf("405;%s CSV waarde moet bestaan uit integers;", $veld));

			return false;
		}
	}
	return $value;
}

// Is de waarde een integer
function isINT($value, $veld = false, $nullToegestaan = false, $checkTable = null)
{
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}

	if (filter_var($value, FILTER_VALIDATE_INT) === false)
	{
		if ($veld !== false)
			throw new Exception(sprintf("405;%s moet een integer zijn;", $veld));

		return false;
	}

	$waarde = intval($value);

	if (!is_null($checkTable))
	{
		$refTable = MaakObject($checkTable);
		if ($refTable->bestaatID($waarde) == false)
			throw new Exception(sprintf("404;%s, referentie naar %s bestaat niet;", $veld, $checkTable));	
	}
	return $waarde;
}

// Is de waarde een datetime
function isDATETIME($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}

	try 
	{
		$datetime = new DateTime($value);
	}
	catch (Exception $ex) 
	{ 
		if ($veld !== false)
			throw new Exception(sprintf("405;%s moet een datum-tijd (php DateTime) zijn;", $veld));

		return false; 
	}
		
	$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam'));  
	return $datetime;
}

// Is de waarde een date
function isDATE($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;	

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}		
	
	if (!preg_match("/^[1-2][0-9]{3}-([1-9]|0[1-9]|1[0-2])-([1-9]|0[1-9]|[1-2][0-9]|3[0-1])$/", $value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("405;%s moet een datum (yyyy-mm-dd) zijn;", $veld));

		return false;
	}
	$checkdate = explode('-', $value);

	if (checkdate($checkdate[1], $checkdate[2], $checkdate[0]) === false)
	{
		if ($veld !== false)
			throw new Exception(sprintf("405;%s moet een geldige datum zijn;", $veld));

		return false;
	}
	return sprintf("%d-%02d-%02d", $checkdate[0], $checkdate[1], $checkdate[2]);
}

// Is de waarde een time
function isTIME($value, $veld = false, $nullToegestaan = false)
{			
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}			

	if (!preg_match("/^([0-9]|[01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value))
	{
		if (!preg_match("/^([0-9]|[01][0-9]|2[0-3]):([0-5][0-9])$/", $value))
		{
			if ($veld !== false)
				throw new Exception(sprintf("405;%s moet een tijd (hh:mm of hh:mm:ss) zijn;", $veld));

			return false;
		}
		$value .= ":00";
	}
	
	return $value;
}

// Is de waarde een boolean
function isBOOL($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  BIJZONDER VOOR BOOLEAN, maar dit is de tristate (null, false , true) mogelijkheid
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}

	if (($value !== 0) && ($value !== 1) && 				// integer
		($value !== "0") && ($value !== "1") &&				// string
		($value !== "false") && ($value !== "true") && 		// string
		($value !== false) && ($value !== true))			// boolean
	{
		if ($veld !== false)
			throw new Exception(sprintf("405;%s moet een boolean zijn;", $veld));

		return false;
	}
	
	if (($value === 0) || ($value === "0") || ($value === "false") || ($value === false))
		return 0; 
	else
		return 1; 
}

// Is de waarde een decimale waarde
function isNUM($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}		

	if (is_numeric($value) == true)
		return $value;

	if ($veld !== false)
		throw new Exception(sprintf("405;%s moet een nummerieke waarde hebben;", $veld));

	return false;
}	

// Is de waarde een latitude
function isLAT($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}		

	isNUM($value, $veld);

	if (($value >= -90) && ($value <= 90))
		return $value;

	if ($veld !== false)
		throw new Exception(sprintf("405;%s latitude moet tussen -90 en 90 graden zijn;", $veld));

	return false;
}		

// Is de waarde een longitude
function isLON($value, $veld = false, $nullToegestaan = false)
{		
	// resetten van het veld  
	if (($value === "") || (is_null($value)) && ($nullToegestaan))
		return null;

	if (is_null($value))
	{
		if ($veld !== false)
			throw new Exception(sprintf("406;%s is een verplicht veld;", $veld));

		return false;				
	}

	isNUM($value, $veld);

	if (($value >= -180) && ($value <= 180))
		return $value;

	if ($veld !== false)
		throw new Exception(sprintf("405;%s longitude moet tussen -180 en 180 graden zijn;", $veld));

	return false;
}

?>