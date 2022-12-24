<?php
class Progressie extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_progressie";
		$this->dbView = "progressie_view";
		$this->Naam = "Progressie";
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
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`COMPETENTIE_ID` mediumint UNSIGNED NOT NULL,
				`INSTRUCTEUR_ID` mediumint UNSIGNED NOT NULL,
				`OPMERKINGEN` text NULL,  
				`INGEVOERD` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`LINK_ID` mediumint UNSIGNED NULL,    		
				`GELDIG_TOT` date DEFAULT NULL,		
				`SCORE` smallint UNSIGNED NULL,  	   
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LID_ID`), 
					INDEX (`INSTRUCTEUR_ID`), 
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
					FOREIGN KEY (INSTRUCTEUR_ID) REFERENCES ref_leden(ID),
					FOREIGN KEY (COMPETENTIE_ID) REFERENCES ref_competenties(ID),
					FOREIGN KEY (LINK_ID) REFERENCES %s(ID)
				)", $this->dbTable, $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = "
				('1', '10063', '101', '10804', 'Isadora weet hoe de vliegdag en de opleiding is'),
				('2', '10063', '102', '10804', 'Doorgenomen dat veligheid niet vanzelf komt'),
				('3', '10632', '101', '10804',  NULL),
				('4', '10632', '102', '10804',  NULL),
				('5', '10632', '103', '10804',  'Verschillende situaties geoefend'),
				('6', '10470', '187', '10115',  'Landen bij andere club'),
				('7', '10395', '132', '10408',  NULL),
				('8', '10632', '104', '10804',  NULL),
				('9', '10632', '105', '10804',  NULL),
				('10','10632', '106', '10804',  NULL),
				('11','10470', '188', '10115',  NULL),
				('12','10395', '133', '10408',  NULL);";
			
			$query = sprintf("
					INSERT INTO `%s` (
						`ID`, 
						`LID_ID`, 
						`COMPETENTIE_ID`, 
						`INSTRUCTEUR_ID`, 
						`OPMERKINGEN`) 
					VALUES
						%s;", $this->dbTable, $inject);
			parent::DbUitvoeren($query);
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
				p.*,
				`t`.`OMSCHRIJVING` AS `LEERFASE`,
				`c`.`ONDERWERP` AS `COMPETENTIE`,
				`l`.`NAAM` AS `LID_NAAM`,
				`i`.`NAAM` AS `INSTRUCTEUR_NAAM`
			FROM
				`%s` `p`  
				LEFT JOIN `ref_competenties` `c` ON (`p`.`COMPETENTIE_ID` = `c`.`ID`)  
				LEFT JOIN `ref_leden` `l` ON (`p`.`LID_ID` = `l`.`ID`)  
				LEFT JOIN `ref_leden` `i` ON (`p`.`INSTRUCTEUR_ID` = `i`.`ID`)  
				LEFT JOIN `ref_types` `t` ON (`c`.`LEERFASE_ID` = `t`.`ID`)
			WHERE
				`p`.`VERWIJDERD` = %d
			ORDER BY 
				LID_ID, LAATSTE_AANPASSING DESC, c.ID;";
								
		parent::DbUitvoeren("DROP VIEW IF EXISTS progressie_view");							
		parent::DbUitvoeren(sprintf($query, "progressie_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_progressie_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_progressie_view", $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID)
	{
		Debug(__FILE__, __LINE__, sprintf("Progressie.GetObject(%s)", $ID));	

		if ($ID == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		$conditie = array();
		$conditie['ID'] = isINT($ID, "ID");

		$obj = parent::GetSingleObject($conditie);

		// ophalen mag alleen door ingelogde gebruiker of beheerder
		$l = MaakObject('Login');
		if ($l->getUserFromSession() != $obj['LID_ID'])
		{
			if (!$this->heeftDataToegang())
				throw new Exception("401;Geen leesrechten;");
		}

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

		$functie = "Progressie.GetObjects";
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
		if (!$this->heeftDataToegang()) 
		{
			if ($l->isStarttoren()) 	// starttoren mag beperkt opvragen
			{
				// bouw CSV string met competenties om op vliegtuigen te vliegen, starttoren is niet geintresseerd in andere behaalde progressies
				$rv = MaakObject('Vliegtuigen');
				$rvObjs = $rv->GetObjects(
					array(
						'CLUBKIST' => true,			
						'VELDEN' => 'BEVOEGDHEID_LOKAAL_ID, BEVOEGDHEID_OVERLAND_ID' 
					));

				$csvCompetenties = "";	
				for ($i=0 ; $i < count($rvObjs['dataset']); $i++)
				{
					
					if (isset($rvObjs['dataset'][$i]['BEVOEGDHEID_LOKAAL_ID'])) 
					{
						if ($csvCompetenties != "") {
							$csvCompetenties .= ",";
						}

						$csvCompetenties .= $rvObjs['dataset'][$i]['BEVOEGDHEID_LOKAAL_ID'];
					}
					
					if (isset($rvObjs['dataset'][$i]['BEVOEGDHEID_OVERLAND_ID'])) 
					{
						if ($csvCompetenties != "") {
							$csvCompetenties .= ",";
						}

						$csvCompetenties .= $rvObjs['dataset'][$i]['BEVOEGDHEID_OVERLAND_ID'];
					}
				}
				// done

				// Welke vliegers zijn vandaag aanwezig, starttoren mag alleen opvragen van aanwezige leden
				$al = MaakObject('AanwezigLeden');
				$laObjs = $al->GetObjects(
					array(
						'BEGIN_DATUM' => date('Y-m-d'),	
						'EIND_DATUM' => date('Y-m-d')
					));

				$csvLeden = "";
				for ($i=0 ; $i < count($laObjs['dataset']); $i++)
				{
					if ($csvLeden != "") {
						$csvLeden .= ",";
					}
					$csvLeden .= $laObjs['dataset'][$i]['LID_ID'];
				}	
				// done

				if ($csvLeden != "")
					$where .= sprintf(" AND LID_ID IN (%s) ", $csvLeden);

				$where .= sprintf(" AND COMPETENTIE_ID IN(%s) ", $csvCompetenties);
			}
			else
			{
				$where .= sprintf(" AND (LID_ID = '%d') ", $l->getUserFromSession());
			}
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
				case "LID_ID" : 
					{
						$lidID = isINT($value, "LID_ID");

						if ($lidID != $l->getUserFromSession())
						{
							if ((!$this->heeftDataToegang()) && !$l->isStarttoren())
								throw new Exception("401;Gebruiker mag geen progressie van ander lid opvragen;");								
						}
						
						$where .= " AND LID_ID=?";
						array_push($query_params, $lidID);	
						
						Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
						break;	  
					}  	
				case "INSTRUCTEUR_ID" : 
					{
						$instructeurID = isINT($value, "INSTRUCTEUR_ID");
						$where .= " AND INSTRUCTEUR_ID=?";
						array_push($query_params, $instructeurID);	
						
						Debug(__FILE__, __LINE__, sprintf("%s: INSTRUCTEUR_ID='%s'", $functie, $instructeurID));
						break;	  
					}  		
				case "IN" : 
					{
						isCSV($value, "IN");
						$where .=  " AND" . sprintf(" COMPETENTIE_ID IN(%s)", trim($value));

						Debug(__FILE__, __LINE__, sprintf("%s: IN='%s'", $functie, $value));
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
				`####progressie_view` " . $where  . $orderby;
		$query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : "") , $query);
		
		$retVal = array();

		$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
		$retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query, $query_params);
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

			for ($i=0; $i < count($retVal['dataset']) ; $i++) 
			{
				$retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);
			}
			return $retVal;
		}
		return null;  // Hier komen we nooit :-)
	}	

	function ProgressieKaart($params)
	{
		global $app_settings;

		$functie = "Progressie.ProgressieKaart";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		

		$alleenLaatsteAanpassing = false;
		$hash = null;
	
		$l = MaakObject('Login');
		$lid_id = $l->getUserFromSession();

		$velden = "							
			`competenties_view`.`LEERFASE`,
			`competenties_view`.`BLOK`,
			`competenties_view`.`ONDERWERP`,
			`competenties_view`.`DOCUMENTATIE`,
			`p`.`OPMERKINGEN`, 
			`p`.`INSTRUCTEUR_NAAM`, 
			`p`.`INGEVOERD`, 
			`p`.`SCORE`, 
			`p`.`GELDIG_TOT`, 

			`competenties_view`.`LEERFASE_ID`,
			`competenties_view`.`ID`,
			`p`.`ID` AS `PROGRESSIE_ID`, 
			`competenties_view`.`BLOK_ID`";
			
			
		foreach ($params as $key => $value)
		{
			switch ($key)
			{
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
				case "LID_ID" : 
					{							
						$lid_id = isINT($value, "LID_ID");

						if ($lid_id != $l->getUserFromSession())
						{
							if (!$this->heeftDataToegang())
								throw new Exception("401;Gebruiker mag geen progressie van ander lid opvragen;");								
						}
						
						Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lid_id));
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
				competenties_view LEFT JOIN (SELECT * FROM progressie_view WHERE LID_ID = $lid_id) p ON 
				competenties_view.ID = p.COMPETENTIE_ID 
			ORDER BY 
				LEERFASE_ID, competenties_view.VOLGORDE, competenties_view.ID";

		$retVal = array();

		$retVal['totaal'] = $this->Count($query);		// total amount of records in the database
		$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, null, "`p`.`LAATSTE_AANPASSING`");
		Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));	

		if ($alleenLaatsteAanpassing)
		{
			$retVal['dataset'] = null;
			return $retVal;
		}
		else
		{				
			$rquery = sprintf($query, $velden);
			parent::DbOpvraag($rquery);
			$retVal['dataset'] = parent::DbData();

			$retVal['hash'] = hash("crc32", json_encode($retVal));
			Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));	

			if ($retVal['hash'] == $hash)
				throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

			return $retVal;
		}
		
		return null;  // Hier komen we nooit :-)			
	}

	/* 
	Haal de progressie kaart op, maar bouw meteen eem boom structuur, dat scheelt werk voor de client. 
	De data is gelijk aan ProgressieKaart
	*/
	function ProgressieBoom($params)
	{
		$functie = "Progressie.ProgressieBoom";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));	

		include_once('lib/class.Boom.inc.php');
		return Boom::bouwBoom($this->ProgressieKaart($params));		// Ophalen data en boom structuur maken
	}

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function VerwijderObject($id = null, $verificatie = true)
	{
		$functie = "Progressie.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));
		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen schrijfrechten;");

		if ($id === null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");								
		parent::MarkeerAlsVerwijderd($id, $verificatie);	
	}		

	/*
	Herstel van een verwijderd record
	*/
	function HerstelObject($id)
	{
		$functie = "Progressie.HerstelObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

		$l = MaakObject('Login');
		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen schrijfrechten;");

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");
		parent::HerstelVerwijderd($id);
	}			

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($ProgressieData)
	{
		$functie = "Progressie.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($ProgressieData, true)));
		
		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen schrijfrechten;");
		
		if ($ProgressieData == null)
			throw new Exception("406;Type data moet ingevuld zijn;");
				
		if (array_key_exists('ID', $ProgressieData))
		{
			$id = isINT($ProgressieData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{					
				$this->GetObject($id);
			}
			catch (Exception $e) {}			

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}

		if (!array_key_exists('LID_ID', $ProgressieData))
			throw new Exception("406;LID_ID is verplicht;");
		
		if (!array_key_exists('COMPETENTIE_ID', $ProgressieData))
			throw new Exception("406;COMPETENTIE_ID is verplicht;");
		
		$l = MaakObject('Login');
		if ($l->getUserFromSession() == $ProgressieData["LID_ID"])	
			throw new Exception("401;Mag geen eigen progressie toevoegen;");

		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($ProgressieData);

		$id = parent::DbToevoegen($t);
		Debug(__FILE__, __LINE__, sprintf("Progressie toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Een bestaand record wordt NOOIT verwijderd, er wordt een nieuw record aangemaakt en originele record wordt als verwijderd gemarkeerd. Hierdoor maken we een audit log
	*/		
	function UpdateObject($ProgressieData)
	{
		$functie = "Progressie.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($ProgressieData, true)));
		
		if (!$this->heeftDataToegang())
			throw new Exception("401;Geen schrijfrechten;");

		if ($ProgressieData == null)
			throw new Exception("406;Progressie data moet ingevuld zijn;");			

		if (!array_key_exists('ID', $ProgressieData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$id = isINT($ProgressieData['ID'], "ID");	

		// Bij update willen we de oude input bewaren. We doen dit als volgt
		// Markeer record als verwijderd
		// Maak een nieuw progressie record en verwijs via LINK_ID naar het verwijderde record 
		$progressie = $this->GetObject($id);
		parent::MarkeerAlsVerwijderd($id, false);

		$progressie = array_merge($progressie, $this->RequestToRecord($ProgressieData));  // samenvoegen bestaande en nieuwe data

		$progressie['LINK_ID'] = $id;	 // verwijzing
		unset ($progressie['ID']);
		unset ($progressie['VERWIJDERD']);
		unset ($progressie['LAATSTE_AANPASSING']);
		$id = parent::DbToevoegen($progressie);

		Debug(__FILE__, __LINE__, sprintf("Progressie toegevoegd id=%d", $id));

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
			$record[$field] = isINT($input[$field], $field, false, "Leden");

		$field = 'INSTRUCTEUR_ID';
		if (array_key_exists($field, $input))
		{
			$record[$field] = isINT($input[$field], $field, false, "Leden");
		}
		else
		{
			$l = MaakObject('Login');
			$userID = $l->getUserFromSession();

			if ($userID > 0)
				$record['INSTRUCTEUR_ID'] = $userID;
		}			

		$field = 'COMPETENTIE_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, "Competenties");

		if (array_key_exists('OPMERKINGEN', $input))
			$record['OPMERKINGEN'] = $input['OPMERKINGEN'];

        $field = 'SCORE';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true);

        $field = 'GELDIG_TOT';
        if (array_key_exists($field, $input))
            $record[$field] = isDATE($input[$field], $field);

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

		if (isset($record['COMPETENTIE_ID']))
			$retVal['COMPETENTIE_ID']  = $record['COMPETENTIE_ID'] * 1;		

		if (isset($record['INSTRUCTEUR_ID']))
			$retVal['INSTRUCTEUR_ID']  = $record['INSTRUCTEUR_ID'] * 1;		

		if (isset($record['LINK_ID']))
			$retVal['LINK_ID']  = $record['LINK_ID'] * 1;

        if (isset($record['SCORE']))
            $retVal['SCORE']  = $record['SCORE'] * 1;

        // booleans
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}	
}
