<?php
class Daginfo extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_daginfo";
		$this->dbView = "daginfo_view";
		$this->Naam = "Daginfo";
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
				`VELD_ID` mediumint UNSIGNED DEFAULT NULL,
				`BAAN_ID` mediumint UNSIGNED DEFAULT NULL,
				`STARTMETHODE_ID` mediumint UNSIGNED DEFAULT NULL,				
				`VELD_ID2` mediumint UNSIGNED DEFAULT NULL,
				`BAAN_ID2` mediumint UNSIGNED DEFAULT NULL,
				`STARTMETHODE_ID2` mediumint UNSIGNED DEFAULT NULL,
				
				`DDWV` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`CLUB_BEDRIJF` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`), 
					INDEX (`VERWIJDERD`),
					
				FOREIGN KEY (VELD_ID) REFERENCES ref_types(ID),	
				FOREIGN KEY (BAAN_ID) REFERENCES ref_types(ID),	
				FOREIGN KEY (STARTMETHODE_ID) REFERENCES ref_types(ID),
				FOREIGN KEY (VELD_ID2) REFERENCES ref_types(ID),	
				FOREIGN KEY (BAAN_ID2) REFERENCES ref_types(ID),	
				FOREIGN KEY (STARTMETHODE_ID2) REFERENCES ref_types(ID)
			)", $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = array(
				"1, '####-04-28', 901, NULL, 1,0",
				"2, '####-04-29', 901, NULL, 1,1",
				"3, '####-04-30', 901, NULL, 0,1",
				"4, '####-05-01', 901, NULL, 1,1",
				"5, '####-05-02', 901, 550,  1,0",
				"6, '####-05-03', 901, 550,  1,0",
				"7, '####-05-04', 901, 550,  1,0",
				"8, '####-05-05', 901, 550,  1,0");

			$inject = str_replace("####", strval(date("Y")), $inject);		// aanwezigheid in dit jaar

			$i = 0;    
			foreach ($inject as $record)
			{
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`VELD_ID`,  
							`STARTMETHODE_ID`, 
							`CLUB_BEDRIJF`, 
							`DDWV`) 
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
			di.*,
			`T_Veld`.`CODE` AS `VELD_CODE`,
			`T_Veld`.`OMSCHRIJVING` AS  `VELD_OMS`,  
			`T_Baan`.`CODE` AS `BAAN_CODE`,
			`T_Baan`.`OMSCHRIJVING` AS  `BAAN_OMS`,       
			`T_Startmethode`.`CODE` AS `STARTMETHODE_CODE`,
			`T_Startmethode`.`OMSCHRIJVING` AS  `STARTMETHODE_OMS`,
			
			`T2_Veld`.`CODE` AS `VELD_CODE2`,
			`T2_Veld`.`OMSCHRIJVING` AS  `VELD_OMS2`,  
			`T2_Baan`.`CODE` AS `BAAN_CODE2`,
			`T2_Baan`.`OMSCHRIJVING` AS  `BAAN_OMS2`,       
			`T2_Startmethode`.`CODE` AS `STARTMETHODE_CODE2`,
			`T2_Startmethode`.`OMSCHRIJVING` AS  `STARTMETHODE_OMS2`			
		FROM
			`%s` `di`
			LEFT JOIN `ref_types` `T_Veld` ON (`di`.`VELD_ID` = `T_Veld`.`ID`)
			LEFT JOIN `ref_types` `T_Baan` ON (`di`.`BAAN_ID` = `T_Baan`.`ID`)
			LEFT JOIN `ref_types` `T_Startmethode` ON (`di`.`STARTMETHODE_ID` = `T_Startmethode`.`ID`)
			
            LEFT JOIN `ref_types` `T2_Veld` ON (`di`.`VELD_ID2` = `T2_Veld`.`ID`)
			LEFT JOIN `ref_types` `T2_Baan` ON (`di`.`BAAN_ID2` = `T2_Baan`.`ID`)
			LEFT JOIN `ref_types` `T2_Startmethode` ON (`di`.`STARTMETHODE_ID2` = `T2_Startmethode`.`ID`)
		WHERE
			`di`.`VERWIJDERD` = %d
		ORDER BY DATUM DESC;";	

		parent::DbUitvoeren("DROP VIEW IF EXISTS daginfo_view");							
		parent::DbUitvoeren(sprintf($query, "daginfo_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_daginfo_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_daginfo_view", $this->dbTable, 1));
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID = null, $DATUM = null, $heeftVerwijderd = false)
	{
		$functie = "Daginfo.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s,%s,%s)", $functie, $ID, $DATUM, $heeftVerwijderd));	

		if (($ID == null) && ($DATUM == null))
			throw new Exception("406;Geen ID en DATUM in aanroep;");
		
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
		Debug(__FILE__, __LINE__, print_r($obj, true));
		
		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");

		if (!$this->heeftDataToegang($obj['DATUM']))
			throw new Exception("401;Geen leesrechten;");

		$obj = $this->RecordToOutput($obj);
		return $obj;	
	}

	/*
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;

		$functie = "Daginfo.GetObjects";
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

		// Als ingelogde gebruiker geen bijzonder functie heeft, worden beperkte dataset opgehaald
		$l = MaakObject('Login');
		if (($l->isBeheerder() == true) || ($l->isInstaller() == true) || ($l->isInstructeur() == true) || ($l->isCIMT() == true))
		{
			// geen beperkingen voor deze gebruikers
		}
		else if ($l->isStarttoren() == true)
		{
			// starttoren mag alleen vandaag opvragen
			$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));		
		}
		else if (($l->isBeheerderDDWV() == true) || ($l->isDDWVCrew() == true))
		{
			// Daginfo voor DDWV is alleen op DDWV dagen bechikbaar
			$where .= " AND (DATUM IN (select DATUM from oper_rooster WHERE DDWV = 1))";	

			if ($l->isDDWVCrew() == true) 
			{
				// DDWV crew mag alleen DDWV dagen zien waar ze zelf dienst hadden
				$where .= sprintf(" AND (DATUM IN (select DATUM from oper_diensten WHERE LID_ID = %d))", $l->getUserFromSession());	
			}
		}
		else 
		{
			throw new Exception("401;Gebruiker mag daginfo niet opvragen;");
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
				`####daginfo_view`" . $where . $orderby;
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
		$functie = "Daginfo.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s, %s)", $functie, $id, $datum, (($verificatie === false) ? "False" :  $verificatie)));					
		
		if (!$this->heeftDataToegang())
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
		$functie = "Daginfo.HerstelObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

		if (!$this->heeftDataToegang(null, false))
			throw new Exception("401;Geen schrijfrechten;");

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");
		parent::HerstelVerwijderd($id);
	}			

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($DaginfoData)
	{
		$functie = "Daginfo.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($DaginfoData, true)));

		if ($DaginfoData == null)
			throw new Exception("406;Daginfo data moet ingevuld zijn;");	

		$where = "";
		$nieuw = true;
		if (array_key_exists('ID', $DaginfoData))
		{
			$id = isINT($DaginfoData['ID'], "ID");

			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{		
				$this->GetObject($id, null);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
							
		}

		if (!array_key_exists('DATUM', $DaginfoData))
			throw new Exception("406;Datum is verplicht;");

		$daginfoDatum = isDATE($DaginfoData['DATUM'], "DATUM");

		if (!$this->heeftDataToegang($DaginfoData['DATUM']))
			throw new Exception("401;Geen schrijfrechten;");

		// Voorkom dat datum meerdere keren voorkomt in de tabel
		try 	// Als record niet bestaat, krijgen we een exception
		{				
			$this->GetObject(null, $DaginfoData['DATUM'], false);
		}
		catch (Exception $e) {}		

		if (parent::NumRows() > 0)
			throw new Exception("409;Datum bestaat al;");

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($DaginfoData);
							
		$id = parent::DbToevoegen($d);
		Debug(__FILE__, __LINE__, sprintf("Daginfo toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($DaginfoData)
	{
		$functie = "Daginfo.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, json_encode($DaginfoData)));
		
		if ($DaginfoData == null)
			throw new Exception("406;Daginfo data moet ingevuld zijn;");	

		if (!array_key_exists('ID', $DaginfoData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$id = isINT($DaginfoData['ID'], "ID");
		
		// Voorkom dat datum meerdere keren voorkomt in de tabel
		if (array_key_exists('DATUM', $DaginfoData))
		{
			$daginfoDatum = isDATE($DaginfoData['DATUM'], "DATUM");

			try 	// Als record niet bestaat, krijgen we een exception
			{
				$di = $this->GetObject(null, $DaginfoData['DATUM'], false);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
			{
				if ($id != $di['ID'])
					throw new Exception("409;Datum bestaat reeds;");
			}	
		}
		else 
		{
			$di = $this->GetObject($id, null, false);
		}

		if (!$this->heeftDataToegang($di['DATUM']))
			throw new Exception("401;Geen schrijfrechten;");

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($DaginfoData);

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

		$field = 'VELD_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, "Types");		

		$field = 'BAAN_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'STARTMETHODE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'VELD_ID2';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'BAAN_ID2';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'STARTMETHODE_ID2';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

		$field = 'DDWV';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);
		
		$field = 'CLUB_BEDRIJF';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);

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

		if (isset($record['VELD_ID']))
			$retVal['VELD_ID']  = $record['VELD_ID'] * 1;

		if (isset($record['BAAN_ID']))
			$retVal['BAAN_ID']  = $record['BAAN_ID'] * 1;				

		if (isset($record['STARTMETHODE_ID']))
			$retVal['STARTMETHODE_ID']  = $record['STARTMETHODE_ID'] * 1;

        if (isset($record['VELD_ID2']))
            $retVal['VELD_ID2']  = $record['VELD_ID2'] * 1;

        if (isset($record['BAAN_ID2']))
            $retVal['BAAN_ID2']  = $record['BAAN_ID2'] * 1;

        if (isset($record['STARTMETHODE_ID2']))
            $retVal['STARTMETHODE_ID2']  = $record['STARTMETHODE_ID2'] * 1;

        // booleans
		if (isset($record['DDWV']))
			$retVal['DDWV']  = $record['DDWV'] == "1" ? true : false;

		if (isset($record['CLUB_BEDRIJF']))
			$retVal['CLUB_BEDRIJF'] = $record['CLUB_BEDRIJF'] == "1" ? true : false;

		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD'] = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
	
}
