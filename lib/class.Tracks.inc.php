<?php
class Tracks extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_tracks";
		$this->dbView = "tracks_view";
		$this->Naam = "Tracks";
	}
	
	/*
	Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
	*/		
	function CreateTable($FillData)
	{
		$l = MaakObject('Login');
		if ($l->isInstaller() == false)
			throw new Exception("401;Geen installer;");
			
		if (parent::bestaatTabel())	
			throw new Exception("405;Tabel bestaat al;");

		$query = sprintf ("
			CREATE TABLE `%s` (
				`ID` mediumint  UNSIGNED NOT NULL AUTO_INCREMENT,
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`INSTRUCTEUR_ID` mediumint UNSIGNED NULL,
				`TEKST` text DEFAULT NULL, 
				`START_ID` mediumint UNSIGNED NULL, 
				`INGEVOERD` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`LINK_ID` mediumint UNSIGNED NULL,           
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LID_ID`),
					INDEX (`INSTRUCTEUR_ID`),
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),		
					FOREIGN KEY (INSTRUCTEUR_ID) REFERENCES ref_leden(ID),
					FOREIGN KEY (START_ID) REFERENCES oper_startlijst(ID),
					FOREIGN KEY (LINK_ID) REFERENCES %s(ID)				
			)", $this->dbTable, $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = array(
				"1, 10265, 10115, 1   , NULL, 0, '%s'", 
				"2, 10001, 10470, 4   , NULL, 0, '%s'", 
				"3, 10395, 10470, NULL, NULL, 0, '%s'", 
				"4, 10855, 10408, NULL, NULL, 1, '%s'", 
				"5, 10855, 10408, NULL, 4   , 0, '%s'", 
				"6, 10632, 10001, NULL, NULL, 0, '%s'", 
				"7, 10858, 10470, NULL, NULL, 0, '%s'"); 
					
			foreach ($inject as $record)
			{    
				$fields = sprintf($record, parent::fakeText());
							
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`,  
							`LID_ID`, 
							`INSTRUCTEUR_ID`, 
							`START_ID`,
							`LINK_ID`,
							`VERWIJDERD`,
							`TEKST`)
						VALUES
							(%s);", $this->dbTable, $fields);
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
				t.*,
				`l`.`NAAM` AS `LID_NAAM`,
				`i`.`NAAM` AS `INSTRUCTEUR_NAAM`
			FROM
				`%s` `t`    
				LEFT JOIN `ref_leden` `l` ON (`t`.`LID_ID` = `l`.`ID`)
				LEFT JOIN `ref_leden` `i` ON (`t`.`INSTRUCTEUR_ID` = `i`.`ID`)
			WHERE
				`t`.`VERWIJDERD` = %d
			ORDER BY 
				LAATSTE_AANPASSING DESC;";	
						
		parent::DbUitvoeren("DROP VIEW IF EXISTS tracks_view");							
		parent::DbUitvoeren(sprintf($query, "tracks_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_tracks_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_tracks_view", $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetObject($ID)
	{
		$functie = "Tracks.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));	

		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen leesrechten;");

		if ($ID == null)
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
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;

		$functie = "Tracks.GetObjects";
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

		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen leesrechten;");

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
				case "LID_ID" : 
					{
						$lidID = isINT($value, "LID_ID");
						$where .= " AND (LID_ID = ?)";
						array_push($query_params, $lidID);

						Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
						break;
					}	
				case "INSTRUCTEUR_ID" : 
					{
						$instructeurID = isINT($value,  "INSTRUCTEUR_ID");
						$where .= " AND (INSTRUCTEUR_ID = ?)";
						array_push($query_params, $instructeurID);

						Debug(__FILE__, __LINE__, sprintf("%s: INSTRUCTEUR_ID='%s'", $functie, $instructeurID));
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
				`####tracks_view`" . $where . $orderby;
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
		$functie = "Tracks.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));	

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");

		isCSV($id, "ID");	
		$trk = $this->GetObject($id);

		$l = MaakObject('Login');

		if (($l->getUserFromSession() != $trk["INSTRUCTEUR_ID"]) && (!$this->heeftDataToegang(null, false)))
			throw new Exception("401;Geen schrijfrechten;");
						
		parent::MarkeerAlsVerwijderd($id, $verificatie);
	}
	
	/*
	Herstel van een verwijderd record
	*/
	function HerstelObject($id)
	{
		$functie = "Tracks.HerstelObject";
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
	function AddObject($TrackData)
	{
		$functie = "Tracks.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($TrackData, true)));
		
		if (!$this->heeftDataToegang(null, true))
			throw new Exception("401;Geen schrijfrechten;");

		if ($TrackData == null)
			throw new Exception("406;Track data moet ingevuld zijn;");			

		if (array_key_exists('ID', $TrackData))
		{
			$id = isINT($TrackData['ID'], "ID");
						
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{	
				$this->GetObject($id);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}
		
		if (!array_key_exists('LID_ID', $TrackData))
			throw new Exception("406;LID_ID is verplicht;");
		
		if (!array_key_exists('TEKST', $TrackData))
			throw new Exception("406;TEKST is verplicht;");
			
		// Neem data over uit aanvraag
		$l = $this->RequestToRecord($TrackData);
					
		$id = parent::DbToevoegen($l);
		Debug(__FILE__, __LINE__, sprintf("Track toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Een bestaand record wordt NOOIT verwijderd, er wordt een nieuw record aangemaakt en originele record wordt als verwijderd gemarkeerd. Hierdoor maken we een audit log
	*/		
	function UpdateObject($TrackData)
	{
		$functie = "Tracks.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($TrackData, true)));
		
		if ($TrackData == null)
			throw new Exception("406;Track data moet ingevuld zijn;");			

		if (!array_key_exists('ID', $TrackData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$track = $this->GetObject($TrackData['ID']);

		if (!$this->heeftDataToegang(null, false))
		{
			$l = MaakObject('Login');

			if ($track["INSTRUCTEUR_ID"] != $l->getUserFromSession())
				throw new Exception("401;Geen schrijfrechten;");
		}

		// Bij update willen we de oude input bewaren. We doen dit als volgt
		// Markeer record als verwijderd
		// Maak een nieuw track record en verwijs via LINK_ID naar het verwijderde record 
		parent::MarkeerAlsVerwijderd($track['ID'], false);

		$track = array_merge($track, $this->RequestToRecord($TrackData));  // samenvoegen bestaande en nieuwe data

		$track['LINK_ID'] = $track['ID'];	 // verwijzing
		unset ($track['ID']);
		unset ($track['VERWIJDERD']);
		unset ($track['LAATSTE_AANPASSING']);
		$id = parent::DbToevoegen($track);

		Debug(__FILE__, __LINE__, sprintf("Track toegevoegd id=%d", $id));

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

		$field = 'LID_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, 'Leden');

		$field = 'INSTRUCTEUR_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, 'Leden');

		$field = 'START_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, "Startlijst");
		
		if (array_key_exists('TEKST', $input))
			$record['TEKST'] = $input['TEKST']; 

		if (array_key_exists('LINK_ID', $input))
			throw new Exception("405;LINK_ID kan niet extern gezet worden;");

		if (array_key_exists('INGEVOERD', $input))
			throw new Exception("405;INGEVOERD kan niet extern gezet worden;");

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

		if (isset($record['LID_ID']))
			$retVal['LID_ID']  = $record['LID_ID'] * 1;	

		if (isset($record['INSTRUCTEUR_ID']))
			$retVal['INSTRUCTEUR_ID']  = $record['INSTRUCTEUR_ID'] * 1;	

		if (isset($record['START_ID']))
			$retVal['START_ID']  = $record['START_ID'] * 1;	
		
		if (isset($record['LINK_ID']))
			$retVal['LINK_ID']  = $record['LINK_ID'] * 1;		

		// booleans	
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}
