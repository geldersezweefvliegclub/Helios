<?php
class Rooster extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_rooster";
		$this->dbView = "rooster_view";
		$this->Naam = "Rooster";
	}
	
	/*
	Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
	*/
	function CreateTable($FillData = null)
	{
		$l = MaakObject('Login');
		if ($l->isInstaller() == false)
			throw new Exception("401;Geen installer;");
			
		if (parent::bestaatTabel())	
			throw new Exception("405;Tabel bestaat al;");

		$query = sprintf ("
			CREATE TABLE `%s` (
				`ID` mediumint  UNSIGNED NOT NULL AUTO_INCREMENT,
				`DATUM` date NOT NULL,
				`DDWV` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`CLUB_BEDRIJF` tinyint UNSIGNED NOT NULL DEFAULT 1,
				`MIN_SLEEPSTART` tinyint UNSIGNED NOT NULL DEFAULT 3,
				`MIN_LIERSTART` tinyint UNSIGNED NOT NULL DEFAULT 10,
				`OPMERKINGEN` text DEFAULT NULL,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`), 
					INDEX (`DDWV`), 
					INDEX (`VERWIJDERD`)	
			)", $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = array(
				"1, '####-05-01', 1, 0",
				"2, '####-05-02', 1, 0",
				"3, '####-05-03', 1, 0",
				"4, '####-05-04', 1, 0",
				"5, '####-05-05', 1, 0");

			$inject = str_replace("####", strval(date("Y")), $inject);		// rooster in dit jaar
			$i = 0;    

			foreach ($inject as $record)
			{    				
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`DDWV`, 
							`CLUB_BEDRIJF`) 
						VALUES
							(%s);", $this->dbTable, $record);
				$i++;
				parent::DbUitvoeren($query);
			}
		}
	}

	/*
	Maak database views, als view al bestaat wordt deze overschreven
	*/
	function CreateViews()
	{
		$l = MaakObject('Login');
		if ($l->isInstaller() == false)
			throw new Exception("401;Geen installer;");
			
		$query = "CREATE VIEW `%s` AS
			SELECT 
				rooster.*
			FROM
				`%s` AS `rooster`			
			WHERE
				`rooster`.`VERWIJDERD` = %d  
			ORDER BY 
				DATUM;";	
					
		parent::DbUitvoeren("DROP VIEW IF EXISTS rooster_view");							
		parent::DbUitvoeren(sprintf($query, "rooster_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_rooster_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_rooster_view", $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetObject($ID = null, $DATUM = null, $heeftVerwijderd = true)
	{
		$functie = "Rooster.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s,%s,%s)", $functie, $ID, $DATUM, $heeftVerwijderd));	

		if (($ID == null) && ($DATUM == null))
			throw new Exception("406;Geen ID en DATUM in aanroep;");

		if (($ID != null) && (isINT($ID) === false))
			throw new Exception("405;ID moet een integer zijn;");

		if (($DATUM != null) && (isDATE($DATUM) === false))
			throw new Exception("405;DATUM heeft onjuist formaat;");

		$conditie = array();
		if ($ID != null)
		{
			$conditie['ID'] = isINT($ID, "ID");
		}
		else
		{
			$conditie['DATUM'] = isDATE($DATUM, "DATUM");

			if ($heeftVerwijderd == false)
				$conditie['VERWIJDERD'] = 0;		// Dus geen verwijderd record
		}

		$obj = parent::GetSingleObject($conditie);
		$obj = $this->RecordToOutput($obj);
		
		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");
		
		return $obj;	
	}

	/*
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;

		$functie = "Rooster.GetObjects";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
		
		$where = ' WHERE 1=1 ';
		$orderby = "";
		$alleenLaatsteAanpassing = false;
		$hash = null;
		$limit = -1;
		$start = -1;
		$velden = "*";
		$alleenVerwijderd = false;
		$query_params = array();

		foreach ($params as $key => $value)
		{
			switch ($key)
			{
				case "ID" : 
					{
						$id = isINT($value, "ID");
						$where .= " AND ID=?";
						array_push($query_params, $id);

						Debug(__FILE__, __LINE__, sprintf("%s: ID='%s'", $functie, $id));
						break;
					}	
				case "VERWIJDERD" :
					{
						$alleenVerwijderd = isBOOL($value, "VERWIJDERD");
						Debug(__FILE__, __LINE__, sprintf("%s: VERWIJDERD='%s'", $functie, $alleenVerwijderd));
						break;
					}						
				case "LAATSTE_AANPASSING" : 
					{
						$alleenLaatsteAanpassing = isBOOL($value, "LAATSTE_AANPASSING");

						Debug(__FILE__, __LINE__, sprintf("%s: LAATSTE_AANPASSING='%s'", $functie, $alleenLaatsteAanpassing));
						break;
					}
				case "HASH" :
					{
						$hash = $value;
						Debug(__FILE__, __LINE__, sprintf("%s: HASH='%s'", $functie, $hash));
						break;
					}								
				case "VELDEN" : 	
					{
						if (strpos($value,';') !== false)
							throw new Exception("405;VELDEN is onjuist;");

						$velden = $value;
						Debug(__FILE__, __LINE__, sprintf("%s: VELDEN='%s'", $functie, $velden));
						break;
					}											
				case "SORT" : 
					{
						if (strpos(strtoupper($value),'UPDATE') !== false)
							throw new Exception("405;SORT is onjuist;");

						if (strpos(strtoupper($value),'DELETE') !== false)
							throw new Exception("405;SORT is onjuist;");
														
						if (strpos($value,';') !== false)
							throw new Exception("405;SORT is onjuist;");
						
						$orderby = sprintf(" ORDER BY %s ", $value);
						Debug(__FILE__, __LINE__, sprintf("%s: SORT='%s'", $functie, $value));
						break;
					}
				case "START" : 
					{
						$s = isINT($value, "START");
						
						if ($s < 0)
							throw new Exception("405;START groter of gelijk zijn dan 0;");
						
						$start = $s;

						Debug(__FILE__, __LINE__, sprintf("%s: START='%s'", $functie, $start));
						break;
					}	
				case "MAX" : 
					{
						$max = isINT($value, "MAX");

						if ($max > 0)
						{
							$limit = $max;
							Debug(__FILE__, __LINE__, sprintf("%s: LIMIT='%s'", $functie, $limit));
						}
						break;
					}
				case "DATUM" : 
					{
						$datum = isDATE($value, "DATUM");

						$where .= " AND DATE(DATUM) = ? ";
						array_push($query_params, $datum);

						Debug(__FILE__, __LINE__, sprintf("%s: DATUM='%s'", $functie, $datum));
						break;
					}							
				case "BEGIN_DATUM" : 
					{
						$beginDatum = isDATE($value, "BEGIN_DATUM");

						$where .= " AND DATE(DATUM) >= ? ";
						array_push($query_params, $beginDatum);

						Debug(__FILE__, __LINE__, sprintf("%s: BEGIN_DATUM='%s'", $functie, $beginDatum));
						break;
					}
				case "EIND_DATUM" : 
					{
						$eindDatum = isDATE($value, "EIND_DATUM");

						$where .= " AND DATE(DATUM) <= ? ";
						array_push($query_params, $eindDatum);

						Debug(__FILE__, __LINE__, sprintf("%s: EIND_DATUM='%s'", $functie, $eindDatum));
						break;
					}							
				default:
					{
						throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
						break;
					}																																								
			}
		}
			
		$query = "
			SELECT 
				%s
			FROM
				`####rooster_view`" . $where . $orderby;
		$query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : "") , $query);		
		
		$retVal = array();

		$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
		$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);
		Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));	

		if ($alleenLaatsteAanpassing)
		{
			$retVal['dataset'] = null;
			return $retVal;
		}
		else
		{			
			if ($limit > 0)
			{
				if ($start < 0)				// Is niet meegegeven, dus start op 0
					$start = 0;

				$query .= sprintf(" LIMIT %d , %d ", $start, $limit);
			}			
			$rquery = sprintf($query, $velden);
			parent::DbOpvraag($rquery, $query_params);
			$retVal['dataset'] = parent::DbData();

			$retVal['hash'] = hash("crc32", json_encode($retVal));
			Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));	

			if ($retVal['hash'] == $hash)
				throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

			for ($i=0 ; $i < count($retVal['dataset']) ; $i++)
			{
				$retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);
			}
			return $retVal;
		}
		return null;  // Hier komen we nooit :-)
	}	

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function VerwijderObject($id = null, $datum = null, $verificatie = true)
	{
		$functie = "Rooster.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s, %s)", $functie, $id, $datum, ($verificatie ? "true" : "false")));							
		$l = MaakObject('Login');	
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV() && !$l->isRooster())
			throw new Exception("401;Geen schrijfrechten;");

		if (($id == null) && ($datum == null))
			throw new Exception("406;Geen ID en DATUM in aanroep;");
		
		if ($id != null)
		{
			isCSV($id, "ID");
		}
		else
		{
			isDATE($datum, "DATUM");
			$vObj = $this->GetObject(null, $datum);
			$id = $vObj["ID"];

			$verificatie = false;	// we weten zeker dat record bestaat
		}
		
		parent::MarkeerAlsVerwijderd($id, $verificatie);			
	}		

	/*
	Herstel van een verwijderd record
	*/
	function HerstelObject($id)
	{
		$functie = "Rooster.HerstelObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

		$l = MaakObject('Login');
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV() && !$l->isRooster())
			throw new Exception("401;Geen schrijfrechten;");

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");
		parent::HerstelVerwijderd($id);
	}	

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($RoosterData)
	{
		$functie = "Rooster.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($RoosterData, true)));
		
		if ($RoosterData == null)
			throw new Exception("406;Rooster data moet ingevuld zijn;");	

		$where = "";
		$nieuw = true;
		if (array_key_exists('ID', $RoosterData))
		{
			$id = isINT($RoosterData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{		
				$this->GetObject($id, null);	
			}
			catch (Exception $e) {}

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
		}

		if (!array_key_exists('DATUM', $RoosterData))
			throw new Exception("406;Datum is verplicht;");

		$roosterDatum = isDATE($RoosterData['DATUM'], "DATUM");
		$weekday = DateTime::createFromFormat('Y-m-d', $roosterDatum)->format('N');

		// check of het een clubdag is
		$clubBedrijf = false;
		$dateparts = explode('-', $roosterDatum);
		$dateValue = $dateparts[1] * 100 + $dateparts[2]*1; 	// Maak maand & dag nummeric

		Debug(__FILE__, __LINE__, sprintf("%s dateValue=%d", $functie, $dateValue));

		// alleen tussen 1 maart & 1 november is er een clubbedrijf
		if (($dateValue >= 301) && ($dateValue < 1101) && ($weekday >= 6)) 
			$clubBedrijf = true;		

		$ddwv = MaakObject('DDWV');
		$l = MaakObject('Login');
		if ($this->heeftDataToegang(null, false) || $l->isBeheerderDDWV() || $l->isRooster())
		{
			// Beheerders en roostermaker mogen velden vullen
			if (!array_key_exists('CLUB_BEDRIJF', $RoosterData))
				$RoosterData['CLUB_BEDRIJF'] = $clubBedrijf;
				
			if (!array_key_exists('DDWV', $RoosterData))				
				$RoosterData['DDWV'] = $ddwv->dagIsDDWV($roosterDatum);		
		}
		else
		{
			// een gewone gebruiker mag alleen default aanmaken
			$RoosterData['OPMERKINGEN'] = null;
			$RoosterData['DDWV'] = $ddwv->dagIsDDWV($roosterDatum);
			$RoosterData['CLUB_BEDRIJF'] = $clubBedrijf;
		}		

		// Voorkom dat datum meerdere keren voorkomt in de tabel
		try 	// Als record niet bestaat, krijgen we een exception
		{				
			$this->GetObject(null, $roosterDatum, false);
		}
		catch (Exception $e) {}	

		if (parent::NumRows() > 0)
			throw new Exception("409;Datum bestaat al;");

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($RoosterData);
							
		$id = parent::DbToevoegen($d);
		Debug(__FILE__, __LINE__, sprintf("Daginfo toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($RoosterData)
	{
		$functie = "Rooster.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($RoosterData, true)));
		
		$l = MaakObject('Login');
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV() && !$l->isRooster() && !$l->isCIMT())
			throw new Exception("401;Geen schrijfrechten;");

		if ($RoosterData == null)
			throw new Exception("406;Rooster data moet ingevuld zijn;");	

		if (!array_key_exists('ID', $RoosterData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$id = isINT($RoosterData['ID'], "ID");

		// Voorkom dat datum meerdere keren voorkomt in de tabel
		if (array_key_exists('DATUM', $RoosterData))
		{
			$roosterDatum = isDATE($RoosterData['DATUM'], "DATUM");

			try 	// Als record niet bestaat, krijgen we een exception
			{
				$di = $this->GetObject(null, $roosterDatum, false);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
			{
				if ($RoosterData['ID'] != $di['ID'])
					throw new Exception("409;Datum bestaat reeds;");
			}	
		}

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($RoosterData);            

		parent::DbAanpassen($id, $d);
		if (parent::NumRows() === 0)
			throw new Exception("404;Record niet gevonden;");				
		
		return $this->GetObject($id);
	}


	/*
	Copieer data van request naar velden van het record 
	*/
	function RequestToRecord($input)
	{
		$record = array();

		$field = 'ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field);

		$field = 'DATUM';
		if (array_key_exists($field, $input))
			$record[$field] = isDATE($input[$field], $field);
														
		$field = 'DDWV';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);

		$field = 'CLUB_BEDRIJF';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);

		$field = 'MIN_SLEEPSTART';		// minimaal aantal aanmeldingen voordat we gaan slepen (alleen DDWV)
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field);

		$field = 'MIN_LIERSTART';		// minimaal aantal aanmeldingen voordat we gaan lieren (alleen DDWV)
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field);

		if (array_key_exists('OPMERKINGEN', $input))
			$record['OPMERKINGEN'] = $input['OPMERKINGEN']; 

		return $record;
	}

	/*
	Converteer integers en booleans voor correcte output 
	*/
	function RecordToOutput($record)
	{
		$retVal = $record;

		// vermengvuldigen met 1 converteer naar integer
		if (isset($record['ID']))
			$retVal['ID']  = $record['ID'] * 1;		

		if (isset($record['MIN_SLEEPSTART']))
			$retVal['MIN_SLEEPSTART']  = $record['MIN_SLEEPSTART'] * 1;

		if (isset($record['MIN_LIERSTART']))
			$retVal['MIN_LIERSTART']  = $record['MIN_LIERSTART'] * 1;

		// booleans				
		if (isset($record['CLUB_BEDRIJF']))
			$retVal['CLUB_BEDRIJF']  = $record['CLUB_BEDRIJF'] == "1" ? true : false;	

		if (isset($record['DDWV']))
			$retVal['DDWV']  = $record['DDWV'] == "1" ? true : false;				

		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}	
}
