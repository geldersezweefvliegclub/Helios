<?php
class Reservering extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_reservering";
		$this->dbView = "reservering_view";
		$this->Naam = "Reservering";
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
				`VLIEGTUIG_ID` mediumint UNSIGNED NOT NULL,
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`INGEVOERD_ID` mediumint UNSIGNED NOT NULL,
				`IS_GEBOEKT` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`OPMERKINGEN` text DEFAULT NULL,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LID_ID`),
					INDEX (`VLIEGTUIG_ID`),
					
				FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
				FOREIGN KEY (VLIEGTUIG_ID) REFERENCES ref_vliegtuigen(ID)	
			)", $this->dbTable);
		parent::DbUitvoeren($query);
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
			`r`.*,
			`l`.`NAAM` AS `NAAM`,
			`l`.`PRIVACY` AS `PRIVACY`,
			`i`.`NAAM` AS `INGEVOERD_DOOR`,

			`v`.`REGISTRATIE`   AS `REGISTRATIE`,
			`v`.`CALLSIGN`      AS `CALLSIGN`,
			CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`
		FROM
			`%s` `r`
			LEFT JOIN `ref_leden` `l` ON (`r`.`LID_ID` = `l`.`ID`)
			LEFT JOIN `ref_leden` `i` ON (`r`.`INGEVOERD_ID` = `i`.`ID`)
			LEFT JOIN `ref_vliegtuigen` `v` ON (`r`.`VLIEGTUIG_ID` = `v`.`ID`)
		WHERE
			`r`.`VERWIJDERD` = %d
		ORDER BY `DATUM`, `VOLGORDE`;";	

		parent::DbUitvoeren("DROP VIEW IF EXISTS reservering_view");							
		parent::DbUitvoeren(sprintf($query, "reservering_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_reservering_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_reservering_view", $this->dbTable, 1));
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetObject($ID)
	{
		$functie = "Reservering.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));	

		if (!isset($ID))
			throw new Exception("406;Geen ID in aanroep;");

		$conditie = array();
		$conditie['ID'] = isINT($ID, "ID");
		
		$obj = parent::GetSingleObject($conditie);
		Debug(__FILE__, __LINE__, print_r($obj, true));

		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");
					
		$obj = $this->RecordToOutput($obj);
		return $obj;				
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetObjectByDetails($datum, $vliegtuigID)
	{
		$functie = "Reservering.GetObjectByDetails";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $datum, $vliegtuigID));	

		if (!isset($datum))
			throw new Exception("406;Geen DATUM in aanroep;");

		if (!isset($vliegtuigID))
			throw new Exception("406;Geen VLIEGTUIG_ID in aanroep;");

		$conditie = array();
		$conditie['DATUM'] = isDate($datum, "Datum");
		$conditie['VLIEGTUIG_ID'] = isINT($vliegtuigID, "vliegtuigID");
		$conditie['VERWIJDERD'] = 0; 
		
		$obj = parent::GetSingleObject($conditie);
		Debug(__FILE__, __LINE__, print_r($obj, true));

		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");
					
		$obj = $this->RecordToOutput($obj);
		return $obj;				
	}

	/*
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;

		$functie = "Reservering.GetObjects";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
		
		$where = ' WHERE 1=1 ';
		$orderby = "";
		$alleenLaatsteAanpassing = false;
		$hash = null;
		$limit = -1;
		$start = -1;
		$velden = "*";
		$in = "";
		$alleenVerwijderd = false;
		$query_params = array();

		$l = MaakObject('Login');
		if (!$l->isClubVlieger() && !$l->isStarttoren())       // alleen clubvliegers en starttoren mogen reservingen zien
		{
			throw new Exception("401;Geen leesrechten;");
		}

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
				case "LAATSTE_AANPASSING" : 
					{
						$alleenLaatsteAanpassing = isBOOL($value, "LAATSTE_AANPASSING");

						$alleenLaatsteAanpassing = ($alleenLaatsteAanpassing === 0) ? false : true;	
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
				case "LID_ID" : 
					{
						$lidID = isINT($value, "LID_ID");
						$where .= " AND LID_ID=?";
						array_push($query_params, $lidID);	
						
						Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
						break;                      
					}
				case "VLIEGTUIG_ID" : 
					{
						$vliegtuigID = isINT($value, "VLIEGTUIG_ID");
						$where .= " AND VLIEGTUIG_ID=?";
						array_push($query_params, $vliegtuigID);	
						
						Debug(__FILE__, __LINE__, sprintf("%s: VLIEGTUIG_ID='%s'", $functie, $vliegtuigID));
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
				`####reservering_view` " . $where . $orderby;
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
	function VerwijderObject($id, $verificatie = true)
	{
		$functie = "Reservering.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");

		isCSV($id, "ID");

		// verwijderen door vlieger of door (DDWV) beheerder
		$l = MaakObject('Login');
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV())
		{
			$reservering = $this->GetObject($id);
			if ($reservering['LID_ID'] != $l->getUserFromSession())
				throw new Exception("401;Geen schrijfrechten;");
		}
		parent::MarkeerAlsVerwijderd($id, $verificatie);
	}		
	
	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($ReserveringsData)
	{
		$functie = "Reservering.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($ReserveringsData, true)));
		
		if ($ReserveringsData == null)
			throw new Exception("406;ReserveringsData data moet ingevuld zijn;");	

		$where = "";
		$nieuw = true;
		if (array_key_exists('ID', $ReserveringsData))
		{
			$id = isINT($ReserveringsData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{		
				$this->GetObject($id, null);	
			}
			catch (Exception $e) {}

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
		}

		if (!array_key_exists('DATUM', $ReserveringsData))
			throw new Exception("406;Datum is verplicht;");
		if (!array_key_exists('VLIEGTUIG_ID', $ReserveringsData))
			throw new Exception("406;VliegtuigID is verplicht;");                

		$datum = isDATE($ReserveringsData['DATUM'], "DATUM");

		// Voorkom dat datum meerdere keren voorkomt in de tabel
		try 	// Als record niet bestaat, krijgen we een exception
		{				
			$this->GetObjectByDetails($ReserveringsData['DATUM'],$ReserveringsData['VLIEGTUIG_ID']);
		}
		catch (Exception $e) {}	

		if (parent::NumRows() > 0)
			throw new Exception("409;Er bestaat al een reservering;");

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($ReserveringsData);
		
		$l = MaakObject('Login');
		$d['INGEVOERD_ID'] = $l->getUserFromSession();
							
		$id = parent::DbToevoegen($d);
		Debug(__FILE__, __LINE__, sprintf("Reservering toegevoegd id=%d", $id));

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
														
		$field = 'LID_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, 'Leden');

		$field = 'VLIEGTUIG_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, "Vliegtuigen");

		if (array_key_exists('OPMERKINGEN', $input))
			$record['OPMERKINGEN'] = $input['OPMERKINGEN']; 

		$field = 'IS_GEBOEKT';
		if (array_key_exists($field, $input))
		{
			$l = MaakObject('Login');
			if (($l->isBeheerder()) || ($l->isBeheerderDDWV())) 
				$record[$field] = isBOOL($input[$field], $field);
			else 
				throw new Exception("405;Geen rechten om IS_GEBOEKT te zetten;");	
		}

		if (array_key_exists('INGEVOERD_ID', $input))
			throw new Exception("405;INGEVOERD_ID kan niet extern gezet worden;");				

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

		if (isset($record['INGEVOERD_ID']))
			$retVal['INGEVOERD_ID']  = $record['INGEVOERD_ID'] * 1;

		if (isset($record['LID_ID']))
			$retVal['LID_ID']  = $record['LID_ID'] * 1;
		
		if (isset($record['VLIEGTUIG_ID']))
			$retVal['VLIEGTUIG_ID']  = $record['VLIEGTUIG_ID'] * 1;
			
		// booleans					
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		if (isset($record['IS_GEBOEKT']))
			$retVal['IS_GEBOEKT']  = $record['IS_GEBOEKT'] == "1" ? true : false;

		if (isset($record['PRIVACY']))
			$retVal['PRIVACY']  = $record['PRIVACY'] == "1" ? true : false;

		// Privacy maskering
		if (isset($retVal['PRIVACY']))
		{
			$l = MaakObject('Login');
			if (($retVal['PRIVACY'] == true) && (!$l->isBeheerder()) && (!$l->isBeheerderDDWV())) 
			{
				$retVal['NAAM']  = "...";
				$retVal['LID_ID']  = -1;
				$retVal['INGEVOERD_ID']  = -1; 
			}
		}
		return $retVal;
	}
}