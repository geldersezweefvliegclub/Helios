<?php

// Dit is de base class waar alles van afgeleid wordt
abstract class StartAdmin
{
	public $qParams = array();
	public $Data = array();
	public $dbTable;
	
	private $defaultErrorLevel;
	private $HttpGetContext;
	
	// de constructor
	public function __construct() 
	{
		$defaultErrorLevel = error_reporting();
		$HttpGetContext = stream_context_create(array('http'=>
			array(
					'timeout' => 0.2, // 0.2 seconde
				  )
			));	
	}

	/*
	Bestaat de database tabel 
	*/
	function bestaatTabel() 
	{
		$query = sprintf("SHOW TABLES LIKE '%s';", $this->dbTable);
		
		$this->DbOpvraag($query);
		if ($this->NumRows() == 0)
			return false;

		return true;
	}

	/*
	Bestaat de ID 
	*/
	function bestaatID($id) 
	{
		$query = sprintf("SELECT ID FROM `%s` WHERE ID=?;", $this->dbTable);
		$query_params = [ $id ];
		
		$this->DbOpvraag($query, $query_params);
		if ($this->NumRows() == 0)
			return false;

		return true;
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetSingleObject($conditions = null)
	{
		Debug(__FILE__, __LINE__, sprintf("StartAdmin.GetSingleObject(%s)", print_r($conditions, true)));

		$params = array();
		$where = "1=1";

		foreach ($conditions as $key => $value)
		{
			$where .= " AND $key = :$key";
			$params[":$key"] = $value;
		}

		$query = sprintf("
			SELECT
				*
			FROM
				%s
			WHERE
				%s", $this->dbTable, $where);
					
		$this->DbOpvraag($query, $params);
		if ($this->NumRows() == 0)
			return null;
			
		return $this->DbData()[0];
	}

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function MarkeerAlsVerwijderd($IDs, $verificatie = true)
	{
		Debug(__FILE__, __LINE__, sprintf("StartAdmin.MarkeerAlsVerwijderd('%s', %s)", $IDs, (($verificatie === false) ? "False" :  $verificatie)));	
		
		if (is_null($verificatie))
			$verificatie = true;

		$verify = isBOOL($verificatie, "VERIFICATIE");

		if ($verify !== 0)
		{
			if (strpos($IDs, ',') !== false)
			{
				$list = explode(",", $ID);
				foreach($list as $i)
				{
					if ($this->bestaatID($i) == false)
						throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $i));	 	
				}
			}	
			else
			{
				if ($this->bestaatID($IDs) == false)
					throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $IDs));	
			}		
		}
		$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 1 WHERE ID IN (%s);", $this->dbTable, $IDs));
	}		
		

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function HerstelVerwijderd($IDs)
	{
		Debug(__FILE__, __LINE__, sprintf("StartAdmin.HerstelVerwijderd('%s')", $IDs));	
	
		if (strpos($IDs, ',') !== false)
		{
			$list = explode(",", $ID);
			foreach($list as $i)
			{
				if ($this->bestaatID($i) == false)
					throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $i));	 	
			}
		}	
		else
		{
			if ($this->bestaatID($IDs) == false)
				throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $IDs));	
		}		
		
		$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 0 WHERE ID IN (%s);", $this->dbTable, $IDs));
	}			
	
	// Functie voor slim laden van datastores in web applicatie
	function Count($query, $conditions = null)
	{
		$rquery = sprintf($query, "COUNT(*) AS totaal");

		$this->DbOpvraag($rquery, $conditions);	
		$d = $this->DbData();	
		if (count($d) == 0)
		{
			return 0;
		}
		return $d[0]['totaal'];
	}	
	
	// Functie voor slim laden van datastores in web applicatie
	function LaatsteAanpassing($query, $conditions = null, $veld = null)
	{
		$lquery  = sprintf($query, "MAX(LAATSTE_AANPASSING) AS LAATSTE_AANPASSING");

		if ($veld != null)
		{
			$l = "MAX($veld) AS LAATSTE_AANPASSING";
			$lquery  = sprintf($query, $l);
		}

		$this->DbOpvraag($lquery, $conditions);	
		$d = $this->DbData();	
		if (count($d) == 0)
			return "";

		return (isset($d[0]['LAATSTE_AANPASSING'])) ? $d[0]['LAATSTE_AANPASSING'] : "0000-00-00 00:00:00";
	}
		
	///-------------------------------------------------------------------------------------------------------------------------------------
	/// Hier regelen we database interactie. We hebben de mogelijk nu, om een externe trigger te starten wanneer er data in de database
	/// aangepast wordt.
	///-------------------------------------------------------------------------------------------------------------------------------------	
	function DbToevoegen($array)
	{
		global $db, $app_settings;
		
		$lastid = $db->DbToevoegen($this->dbTable, $array);
		return $lastid;
	}
	
	function DbAanpassen($ID, $array)
	{
		global $db, $app_settings;
		
		$retVal = $db->DbAanpassen($this->dbTable, $ID, $array);
		return $retVal;	
	}
	
	function DbUitvoeren($query)
	{
		global $db;
		
		return $db->DbUitvoeren($query);
	}
	
	function DbData()
	{
		global $db;
		
		return $db->data_retrieved;
	}
	
	function DbOpvraag($query, $params = null)
	{
		global $db;	
		
		return $db->DbOpvraag($query, $params);
	}
	
	function NumRows()
	{
		global $db;	
		
		return $db->rows;
	}

	function fakeText()
	{
		return "Dit is een faketekst. Alles wat hier staat is slechts om een indruk te geven van het grafische effect van tekst op deze plek. Wat u hier leest is een voorbeeldtekst. Deze wordt later vervangen door de uiteindelijke tekst, die nu nog niet bekend is. De faketekst is dus een tekst die eigenlijk nergens over gaat. Het grappige is, dat mensen deze toch vaak lezen. Zelfs als men weet dat het om een faketekst gaat, lezen ze toch door.";
	}
}

?>