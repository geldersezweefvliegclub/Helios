<?php

// Dit is de base class waar alles van afgeleid wordt
abstract class Helios
{
	public $qParams = array();
	public $Data = array();
	public $dbTable;
	public $Naam;
	
	private $defaultErrorLevel;
	private $HttpGetContext;
	
	abstract function GetObjects($params);

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
	Heeft de ingelogde gebruiker toegang tot de volledige startlijst
	*/
	function heeftDataToegang($datum = null, $instructeurs = true)
	{
		Debug(__FILE__, __LINE__, sprintf("heeftDataToegang(%s, %d)", $datum, $instructeurs));

		$l = MaakObject('Login');
		//Debug(__FILE__, __LINE__, sprintf("isBeheerder = %d", $l->isBeheerder()));
		//Debug(__FILE__, __LINE__, sprintf("isInstaller = %d", $l->isInstaller()));
		//Debug(__FILE__, __LINE__, sprintf("isInstructeur = %d", $l->isInstructeur()));
		//Debug(__FILE__, __LINE__, sprintf("isCIMT = %d", $l->isCIMT()));
		//Debug(__FILE__, __LINE__, sprintf("isStarttoren = %d", $l->isStarttoren()));


		if ($l->isBeheerder() || $l->isInstaller() || ($instructeurs &&  ($l->isInstructeur() || $l->isCIMT())))
		{
			return true;
		}
		else if ($datum == null)
		{
			return false;
		}
		else if ($l->isStarttoren())
		{
			// we  moeten leading 0 plaatsen voor de datum, dan gaat 2020-4-2 ook goed. Dit wordt dan 2020-04-02
			$date = datetime::createfromformat('Y-m-d',$datum);	
			Debug(__FILE__, __LINE__, sprintf("isStarttoren, %s %s", $date->format("Y-m-d"), date("Y-m-d")));

			return ($date->format("Y-m-d") == date("Y-m-d"));
		}
		else if ($l->isBeheerderDDWV())
		{
			return $this->heeftToegangBeheerderDDVW($datum);
		}
		else if ($l->isDDWVCrew())
		{
			return $this->heeftToegangDDWVCrew($datum);
		}
		return false;
	}

	/*
	De beheerder DDWV heeft op DDWV dagen volledig toegang
	*/
	function heeftToegangBeheerderDDVW($datum)
	{
		$l = MaakObject('Login');

		Debug(__FILE__, __LINE__, sprintf("Startlijst.heeftToegangBeheerderDDVW(%s) isBeheerderDDWV(%d) =  %d", $datum,
									$l->getUserFromSession(), 
									$l->isBeheerderDDWV()));

		$r = MaakObject('Rooster');

		try {
			$rooster = $r->GetObject(null, $datum, false); 

			if ($rooster['DDWV'])
			{
				Debug(__FILE__, __LINE__, sprintf("Startlijst.heeftDDWVtoegang %s is een DDWV dag", $datum));

				if ($l->isBeheerderDDWV())
				{
					return true; // Het is een DDWV dag, dus geen beperkingen voor de DDWV beheerder
				}
			}
		}
		catch (Exception $e) {}	
		return false;
	}

	/*
	De DDWV crew heeft toegang op DDWV dagen waar ze zelf dienst hadden
	*/
	function heeftToegangDDWVCrew($datum)
	{
		$l = MaakObject('Login');

		Debug(__FILE__, __LINE__, sprintf("heeftToegangDDWVCrew(%s) isDDWVCrew(%d) =  %d", $datum, 
									$l->getUserFromSession(), 
									$l->isDDWVCrew()));

		$r = MaakObject('Rooster');
		$d = MaakObject('Diensten');

		try {
			$rooster = $r->GetObject(null, $datum, false); 

			if ($rooster['DDWV'])
			{
				Debug(__FILE__, __LINE__, sprintf("heeftToegangDDWVCrew %s is een DDWV dag", $datum));

				$diensten = $d->GetObjects(
					array (
						'LID_ID' => $l->getUserFromSession(),
						'DATUM' => $datum
					));
				
				if ($diensten['totaal'] > 0)
				{
					Debug(__FILE__, __LINE__, sprintf("heeftToegangDDWVCrew %d heeft dienst op %s", $l->getUserFromSession(), $datum));
					return true;
				}
			}
		}
		catch (Exception $e) {}	
		return false;
	}

	/*
	Bestaat de database tabel 
	*/
	function bestaatTabel($tabel = null) 
	{
		$query = sprintf("SHOW TABLES LIKE '%s';", $this->dbTable);
		if (!is_null($tabel))
			$query = sprintf("SHOW TABLES LIKE '%s';", $tabel);
		
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
		Debug(__FILE__, __LINE__, sprintf("Helios.GetSingleObject(%s)", print_r($conditions, true)));

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
		Debug(__FILE__, __LINE__, sprintf("Helios.MarkeerAlsVerwijderd('%s', %s)", $IDs, (($verificatie === false) ? "False" :  $verificatie)));	
		if (is_null($verificatie))
		$verificatie = true;

		$verify = isBOOL($verificatie, "VERIFICATIE");
		$list = array();
	
		if (strpos($IDs, ',') !== false)
			$list = explode(",", $IDs);
		else
			$list[0] = $IDs;

		if ($this->dbTable == "audit") 
		{
			$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 1 WHERE ID IN (%s);", $this->dbTable, $IDs));
		}
		else
		{
			foreach($list as $i)
			{
				//$this->DbOpvraag(sprintf("SELECT * FROM %s WHERE ID = %d", $this->dbView, $i));
				//$org = $this->DbData();
				$org = $this->GetObjects(array('ID' => $i));
				

				if (($this->NumRows() == 0) && (($verify !== 0)))
					throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $i));	

				if (($this->NumRows() > 0))
				{	
					$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 1 WHERE ID IN (%s);", $this->dbTable, $i));

					$audit = MaakObject("Audit");
					$audit->AddObject($this->dbTable, $this->Naam, "Verwijderd", json_encode($org['dataset'][0]), null, null);
				}
			}
		}
	}				
		
	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function HerstelVerwijderd($IDs)
	{
		Debug(__FILE__, __LINE__, sprintf("Helios.HerstelVerwijderd('%s')", $IDs));	
		$list = array();
	
		if (strpos($IDs, ',') !== false)
			$list = explode(",", $IDs);
		else
			$list[0] = $IDs;

		if ($this->dbTable == "audit") 
		{
			$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 0 WHERE ID IN (%s);", $this->dbTable, $IDs));
		}
		else
		{
			foreach($list as $i)
			{
				if ($this->bestaatID($i) == false)
					throw new Exception(sprintf("404;Record met ID=%s niet gevonden;", $i));	

				$this->DbUitvoeren(sprintf("UPDATE `%s` SET `VERWIJDERD`= 0 WHERE ID IN (%s);", $this->dbTable, $i));

				//$this->DbOpvraag(sprintf("SELECT * FROM %s WHERE ID = %d", $this->dbView, $i));
				//$result = $this->DbData();
				$result = $this->GetObjects(array('ID' => $i));

				$audit = MaakObject("Audit");
				$audit->AddObject($this->dbTable, $this->Naam, "Hersteld", null, null, json_encode($result['dataset'][0]));
			}
		}
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
		return $d[0]['totaal'] * 1;		// string to integer
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
		
		if ($this->dbTable == "audit") 
		{
			$lastid = $db->DbToevoegen($this->dbTable, $array);
		} else 
		{
			$lastid = $db->DbToevoegen($this->dbTable, $array);
			//$this->DbOpvraag(sprintf("SELECT * FROM %s WHERE ID = %d", $this->dbView, $lastid));
			//$record = $this->DbData();

			$record = $this->GetObjects(array('ID' => $lastid));

			$audit = MaakObject("Audit");
			$audit->AddObject($this->dbTable, $this->Naam,"Toevoegen", null, json_encode($array), json_encode($record['dataset'][0]));
		}
		return $lastid;
	}
	
	function DbAanpassen($ID, $array)
	{
		global $db, $app_settings;
		
		if ($this->dbTable == "audit") 
		{
			$retVal = $db->DbAanpassen($this->dbTable, $ID, $array);
		}
		else 
		{
			//$this->DbOpvraag(sprintf("SELECT * FROM %s  WHERE ID = %d", $this->dbView, $ID));
			//$org = $this->DbData();
			$org = $this->GetObjects(array('ID' => $ID));

			$retVal = $db->DbAanpassen($this->dbTable, $ID, $array);
			if ($retVal > 0) 		// record is aangepast
			{
				//$this->DbOpvraag(sprintf("SELECT * FROM %s WHERE ID = %d", $this->dbView, $ID));
				//$record = $this->DbData();
				$record = $this->GetObjects(array('ID' => $ID));

				$audit = MaakObject("Audit");
				$audit->AddObject($this->dbTable, $this->Naam, "Aanpassen", json_encode($org['dataset'][0]), json_encode($array), json_encode($record['dataset'][0]));
			}
		}
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