<?php
class AanwezigLeden extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_aanwezig_leden";
		$this->dbView = "aanwezig_leden_view";
		$this->Naam = "Leden aanwezig";
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
				`POSITIE` tinyint UNSIGNED DEFAULT NULL,
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`VOORAANMELDING` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`AANKOMST` time DEFAULT NULL,
				`VERTREK` time DEFAULT NULL,
				`OVERLAND_VLIEGTUIG_ID` mediumint UNSIGNED DEFAULT NULL,
				`VOORKEUR_VLIEGTUIG_TYPE` text(100) DEFAULT NULL,
				`TRANSACTIE_ID` mediumint UNSIGNED DEFAULT NULL,
				`VELD_ID` mediumint UNSIGNED DEFAULT NULL,
				`OPMERKINGEN` text DEFAULT NULL,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`), 
					INDEX (`LID_ID`), 
					INDEX (`VERWIJDERD`),

				FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
				FOREIGN KEY (VELD_ID) REFERENCES ref_types(ID),
				FOREIGN KEY (TRANSACTIE_ID) REFERENCES oper_transacties(ID),
				FOREIGN KEY (OVERLAND_VLIEGTUIG_ID) REFERENCES ref_vliegtuigen(ID) 
			)", $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = array(
				"1, '####-05-01', NULL, 10001, 1, '07:58:00', NULL, 		200, NULL, '%s' ",
				"2, '####-05-01', 1, 	10265, 0, '09:01:00', '09:01:00', 	201, NULL, '%s' ",
				"3, '####-05-01', 2, 	10115, 0, '11:03:00', '16:01:00', 	NULL, '404, 405', '%s' ",
				"4, '####-05-01', NULL, 10855, 0, '13:18:00', NULL, 		NULL, '404, 405', '%s' ",
				"5, '####-05-02', NULL,	10001, 0,  NULL  	, NULL,  		NULL, '404', '%s' ",
				"6, '####-05-02', NULL,	10470, 0,  NULL  	, NULL,  		217, NULL, '%s' ",
				"7, '####-05-03', 1, 	10213, 0,  NULL  	, NULL,  		211, '404', '%s' ",
				"8, '####-05-04', 1, 	10063, 0,  NULL  	, NULL,  		NULL,'404', '%s' ",
				"9, '####-05-04', 2, 	10858, 0,  NULL  	, NULL,  		NULL, NULL, '%s' ",
				"10, '####-05-04', 3, 	10632, 0,  '09:15:00',NULL,  		NULL, NULL, '%s' ");

			$inject = str_replace("####", strval(date("Y")), $inject);		// aanwezigheid in dit jaar

			$i = 0;    
			foreach ($inject as $record)
			{    
				$fields = sprintf($record, parent::fakeText());
							
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`POSITIE`, 
							`LID_ID`, 
							`VOORAANMELDING`, 
							`AANKOMST`, 
							`VERTREK`, 
							`OVERLAND_VLIEGTUIG_ID`, 
							`VOORKEUR_VLIEGTUIG_TYPE`, 
							`OPMERKINGEN`) 
						VALUES
							(%s);", $this->dbTable, $fields);
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
				`al`.`ID`,
				`al`.`DATUM`,
				`al`.`POSITIE`,
				`al`.`LID_ID`,
				`al`.`VELD_ID`,
				`al`.`TRANSACTIE_ID`,
				`al`.`VOORAANMELDING`,
				time_format(`al`.`AANKOMST`,'%%H:%%i') AS `AANKOMST`,
				time_format(`al`.`VERTREK`,'%%H:%%i') AS `VERTREK`,
				`al`.`OVERLAND_VLIEGTUIG_ID`,
				`al`.`VOORKEUR_VLIEGTUIG_TYPE`,
				`al`.`OPMERKINGEN`,
				`al`.`VERWIJDERD`,
				`al`.`LAATSTE_AANPASSING`,

				(SELECT 
					GROUP_CONCAT(CODE) FROM ref_types 
				WHERE GROEP = 4 AND VOORKEUR_VLIEGTUIG_TYPE LIKE CONCAT('%%',ID,'%%') ) AS VLIEGTUIGTYPE_CODE, 

				(SELECT 
					GROUP_CONCAT(OMSCHRIJVING) FROM ref_types 
				WHERE GROEP = 4 AND VOORKEUR_VLIEGTUIG_TYPE LIKE CONCAT('%%',ID,'%%') ) AS VLIEGTUIGTYPE_OMS, 

				(SELECT 
					time_format(sec_to_time(sum(
						time_to_sec(
						timediff(ifnull(LANDINGSTIJD,curtime()),STARTTIJD)))
						),'%%H:%%i')  FROM oper_startlijst 
				WHERE `VERWIJDERD`= 0 AND DATUM = `al`.`DATUM` AND `STARTTIJD` IS NOT NULL AND VLIEGER_ID = `al`.`LID_ID`) AS VLIEGTIJD,

				(SELECT 
					COUNT(*)  FROM oper_startlijst 
				WHERE `VERWIJDERD`= 0 AND DATUM = `al`.`DATUM` AND VLIEGER_ID = `al`.`LID_ID`) AS STARTS,

				(SELECT 
					COUNT(*)  FROM oper_startlijst 
				WHERE `VERWIJDERD`= 0 AND DATUM = `al`.`DATUM` AND `STARTTIJD` IS NOT NULL AND `LANDINGSTIJD` IS NULL AND VLIEGER_ID = `al`.`LID_ID` LIMIT 1) AS VLIEGT,

				CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`,
				`l`.`NAAM`,
				`l`.`VOORNAAM`, 
				`l`.`TUSSENVOEGSEL`, 
				`l`.`ACHTERNAAM`, 
				`l`.`ADRES`, 
				`l`.`POSTCODE`, 
				`l`.`WOONPLAATS`, 
				`l`.`LIDNR`, 
				`l`.`LIDTYPE_ID`, 
				`l`.`MOBIEL`, 
				`l`.`EMAIL`, 
				`l`.`NOODNUMMER`, 
				`l`.`TELEFOON`, 
				`l`.`INSTRUCTEUR`, 
				`l`.`STARTLEIDER`, 
				`l`.`LIERIST`,
				`l`.`CIMT`,
				`l`.`DDWV_CREW`,
				`l`.`DDWV_BEHEERDER`,
				`l`.`BEHEERDER`,
				`l`.`STARTTOREN`,
				`l`.`ROOSTER`,
				`l`.`CLUBBLAD_POST`,
				`l`.`MEDICAL`,
				`l`.`GEBOORTE_DATUM`,
				`l`.`ZUSTERCLUB_ID`,
				`l`.`INLOGNAAM`,    
				`l`.`SECRET`,  
				`l`.`AVATAR`,        
				`l`.`STARTVERBOD`,
				`l`.`PRIVACY`,
				`l`.`STATUSTYPE_ID`,
				`t`.`OMSCHRIJVING` AS `VELD`,
				`lt`.`OMSCHRIJVING` AS `LIDTYPE`,
				`s`.`CODE` AS `STATUS`,
				`s`.`SORTEER_VOLGORDE` AS `STATUS_SORTEER_VOLGORDE`
			FROM
				`%s` `al`
				LEFT JOIN `ref_leden` `l` ON (`al`.`LID_ID` = `l`.`ID`)
				LEFT JOIN `ref_types` `t` ON (`al`.`VELD_ID` = `t`.`ID`)
				LEFT JOIN `ref_types` `lt` ON (`l`.`LIDTYPE_ID` = `lt`.`ID`)
				LEFT JOIN `ref_types` `s` ON (`l`.`STATUSTYPE_ID` = `s`.`ID`)
				LEFT JOIN `ref_vliegtuigen` `v` ON (`al`.`OVERLAND_VLIEGTUIG_ID` = `v`.`ID`)
			WHERE
				`al`.`VERWIJDERD` = %d 
			ORDER BY 
				DATUM DESC, POSITIE, ID;";				
		
		parent::DbUitvoeren("DROP VIEW IF EXISTS aanwezig_leden_view");							
		parent::DbUitvoeren(sprintf($query, "aanwezig_leden_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_aanwezig_leden_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_aanwezig_leden_view", $this->dbTable, 1));		
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID = null, $LID_ID = null, $DATUM = null, $heeftVerwijderd = true)
	{
		$functie = "AanwezigLeden.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s,%s,%s,%s)", $functie, $ID, $LID_ID, $DATUM, $heeftVerwijderd));	

		$conditie = array();
		if ($ID !== null)
		{
			$conditie['ID'] = isINT($ID, "ID");
		}
		else
		{
			if (($DATUM == null) || ($LID_ID == null))
				throw new Exception("406;Geen ID en LID_ID/DATUM in aanroep;");

			$conditie['LID_ID'] = isINT($LID_ID, "LID_ID", false, "Leden");
			$conditie['DATUM'] = isDATE($DATUM, "DATUM");	

			if ($heeftVerwijderd == false)
				$conditie['VERWIJDERD'] = 0;		// Dus geen verwijderd record
		}

		$obj = parent::GetSingleObject($conditie);
		Debug(__FILE__, __LINE__, print_r($obj, true));
		
		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");
		
		if (!is_null($obj['AANKOMST']))
			$obj['AANKOMST'] = substr($obj['AANKOMST'] , 0, 5);	// alleen hh:mm
		
		if (!is_null($obj['VERTREK']))
			$obj['VERTREK'] = substr($obj['VERTREK'] , 0, 5);	// alleen hh:mm

		// Controle of de gebruiker deze data wel mag ophalen
		if (!$this->heeftDataToegang($obj['DATUM']))
		{
			// is ingelogde gebruiker de persoon zelf? Nee, dan geen toegang
			$l = MaakObject('Login');
		
			if (($obj['LID_ID'] != $l->getUserFromSession()))
				throw new Exception("401;Geen leesrechten;");
		}
		$obj = $this->RecordToOutput($obj);
		return $obj;	
	}

	/*
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;
		
		$functie = "AanwezigLeden.GetObjects";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
		
		$where = ' WHERE 1=1 ';
		$orderby = "";
		$alleenLaatsteAanpassing = false;
		$alleenVerwijderd = false;
		$hash = null;
		$limit = -1;
		$start = -1;
		$in = "";
		$velden = "*";

		// Als ingelogde gebruiker geen bijzonder functie heeft, worden beperkte dataset opgehaald
		$l = MaakObject('Login');

		if ($l->isStarttoren() == true)
		{
			// starttoren mag alleen vandaag opvragen
			$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));		
		}

		$rl = MaakObject('Leden');
		$rlObj = $rl->GetObject($l->getUserFromSession());
		$privacyMasker = $rlObj['PRIVACY'];

		if ($rlObj['LIDTYPE_ID'] == 625) 	// DDWV'er mogen alleen DDWV dagen opvragen
		{
			$where .= " AND (SELECT count(*) FROM oper_rooster AS `or` WHERE `or`.`DATUM` = `aanwezig_leden_view`.`DATUM` AND `or`.`DDWV` = 1) = 1";
		}

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
				case "SELECTIE" : 
					{
						$where .= " AND (VLIEGER LIKE ?) ";

						$s = "%" . trim($value) . "%";
						array_push($query_params, $s);

						Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));
						break;
					}
				case "IN" : 
					{
						isCSV($value, "IN");
						$in = sprintf(" LID_ID IN(%s)", trim($value));

						Debug(__FILE__, __LINE__, sprintf("%s: IN='%s'", $functie, $value));
						break;
					}
				case "TYPES" : 
					{
						isCSV($value, "TYPES");
						$where .= sprintf(" AND LIDTYPE_ID IN(%s)", trim($value));

						Debug(__FILE__, __LINE__, sprintf("%s: TYPES='%s'", $functie, $value));
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
				case "NIET_VERTROKKEN" : 
					{
						$alleenAanwezig = isBOOL($value, "NIET_VERTROKKEN");
						if ($alleenAanwezig)
							$where .= " AND VERTREK IS NULL ";
						break;
					}	
				default:
					{
						throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
					break;
					}		
			}
		}

		// Als er geen datum is meegegeven dan alleen vandaag
		if ((strpos($where, 'DATUM') === false) && (strpos($where, 'ID') === false)) {
			$beginDatum = date("Y-m-d");
			$eindDatum = date("Y-m-d");
			$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));
		}

		if ($in != "")
		{
			if (strpos($where, 'AND') === false) {
				$where .=  " AND" . $in;			// Er is geen where conditie, dus beperken we dataset to IN parameters
			}
			else {
				$where .=  sprintf(" OR (%s)", $in); // Er is WEL een where conditie, dus IN parameters als extra toevoegen
			}
		}
			
		$query = "
			SELECT 
				%s
			FROM
				`####aanwezig_leden_view`" . $where . $orderby;
		$query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : "") , $query);			
		
		$retVal = array(); 

		$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
		$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);

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

			$lid = $rl->GetObject($l->getUserFromSession());

			// Haal het rooster op. Tijdens kamp dagen zijn er DDWV en clubleden. DDWV'ers mogen geen clubleden zien tijdens kamp 
			if (!isset($beginDatum) || !isset($eindDatum))
			{
				// als begin en einddatum niet bekend zijn, dan via dataset bepalen
				$b = new DateTime("2100-01-01");
				$e = new DateTime("1900-01-01");

				foreach ($retVal['dataset'] as $a)
				{
					if (new DateTime($a['DATUM']) < $b)
						$b = new DateTime($a['DATUM']);
					if (new DateTime($a['DATUM']) > $e)
						$e = new DateTime($a['DATUM']);	
				}
				$beginDatum = $b->format("Y-m-d");
				$eindDatum = $e->format("Y-m-d");
			}

			$r = MaakObject('Rooster');
			$robjs = $r->GetObjects(array (
				'BEGIN_DATUM' => $beginDatum, 
				'EIND_DATUM' => $eindDatum, 
			));
			$rooster = array();
			foreach ($robjs['dataset'] as $dag)	// maak named array, handig voor later
				$rooster[$dag['DATUM']] = $dag;

			Debug(__FILE__, __LINE__, sprintf("%s: rooster = %s)", $functie, print_r($rooster, true)));	
			// klaar met rooster ophalen

			for ($i=0; $i < count($retVal['dataset']) ; $i++) 
			{
				if (array_key_exists('REG_CALL', $retVal['dataset'][$i]))
				{
					if (isINT($retVal['dataset'][$i]['OVERLAND_VLIEGTUIG_ID']) == false)
						$retVal['dataset'][$i]['REG_CALL'] = null;		// view geeft "()" als vliegtuig null is. Dit is workarround
				}

				// Voor DDWV'ers laten we geen namen leden zien op clubdagen (bijv op kamp dagen)
				// de namen van DDWV'ers mogen we wel tonen
				$verberg = false;
				if ($lid['LIDTYPE_ID'] == 625 && $retVal['dataset'][$i]['LIDTYPE_ID'] != 625) {
					$verberg = $rooster[$retVal['dataset'][$i]['DATUM']]['CLUB_BEDRIJF'];
				}
				
				$opmerking = $retVal['dataset'][$i]['OPMERKINGEN'];
				$retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);	
				$retVal['dataset'][$i] = $this->privacyMask($retVal['dataset'][$i], $privacyMasker || $verberg);	// privacy mask voor aanmeldingen
				$retVal['dataset'][$i] = $rl->privacyMask($retVal['dataset'][$i], $privacyMasker);				// deze functie verwijderd OPMERKING
				$retVal['dataset'][$i]['OPMERKINGEN'] = $opmerking;												// en zo lossen we dat op

				$l = MaakObject('Login');
				if ((($l->isBeheerder() === true)  || ($l->isInstructeur() === true) || ($l->isCIMT() === true)) && 
					  isset($retVal['dataset'][$i]['LID_ID']))	
					{
						$sl = MaakObject('Startlijst');

						$barometer = $sl->GetRecency($retVal['dataset'][$i]['LID_ID']);
						$retVal['dataset'][$i]['STATUS_BAROMETER'] = $barometer['STATUS_BAROMETER'];
					}
			}

			$retVal['hash'] = hash("crc32", json_encode($retVal));	
			Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s, HASH=%s", 
													$retVal['totaal'],  
													$retVal['laatste_aanpassing'], 
													$retVal['hash']));

			if ($retVal['hash'] == $hash)
				throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

			return $retVal;
		}
		return null;  // Hier komen we nooit :-)
	}	

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function VerwijderObject($ids = null, $lid_id = null, $datum = null, $verificatie = true)
	{
		$functie = "AanwezigLeden.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s, %s, %s)", $functie, $ids, $lid_id, $datum, (($verificatie === false) ? "False" :  $verificatie)));

		if ($ids !== null)
		{
			isCSV($ids, "ID");
		}
		else
		{
			if (($datum == null) || ($lid_id == null))
				throw new Exception("406;Geen ID en LID_ID/DATUM in aanroep;");

			isINT($lid_id, "LID_ID");
			isDATE($datum, "DATUM");	
		}

        $l = MaakObject('Login');
        $ddwv = MaakObject('DDWV');     // eventueel DDWV strippen retour

		if ($ids !== null)
        {
            $arrayIDs = explode(",", $ids);

            foreach ($arrayIDs as $id) {
                $db_data = $this->GetObject($id);

                if (($this->heeftDataToegang() == false) && ($db_data['LID_ID'] != $l->getUserFromSession()))
                    throw new Exception("401;Geen schrijfrechten;");

                parent::MarkeerAlsVerwijderd($id, false);
                $ddwv->AfmeldenLidBijboekenDDWV($db_data);
            }
        }
        else
		{
			$db_data = $this->GetObject(null, $lid_id, $datum);
			$id = $db_data["ID"];

            if (($this->heeftDataToegang($datum) == false) && ($db_data['LID_ID'] != $l->getUserFromSession()))
                throw new Exception("401;Geen schrijfrechten;");

            parent::MarkeerAlsVerwijderd($id, false);
            $ddwv->AfmeldenLidBijboekenDDWV($db_data);
		}
	}	
	
	/*
	Herstel van een verwijderd record
	*/
	function HerstelObject($id)
	{
		$functie = "AanwezigLeden.HerstelObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

		if ($this->heeftDataToegang() == false)
			throw new Exception("401;Geen schrijfrechten;");

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");
		parent::HerstelVerwijderd($id);
	}

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($AanwezigLedenData)
	{
		$functie = "AanwezigLeden.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($AanwezigLedenData, true)));
			
		if ($AanwezigLedenData == null)
			throw new Exception("406;AanwezigLeden data moet ingevuld zijn;");	

		$where = "";
		$nieuw = true;
		if (array_key_exists('ID', $AanwezigLedenData))
		{
			$id = isINT($AanwezigLedenData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{		
				$this->GetObject($id, null, null);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));				
		}

		if (!array_key_exists('LID_ID', $AanwezigLedenData))
			throw new Exception("406;LID_ID is verplicht;");

		if (!array_key_exists('DATUM', $AanwezigLedenData))
			throw new Exception("406;DATUM is verplicht;");

		$lidID = isINT($AanwezigLedenData['LID_ID'], "LID_ID");
		$aanmeldDatum = isDATE($AanwezigLedenData['DATUM'], "DATUM");	
		
		$login = MaakObject('Login');
		if ((!$this->heeftDataToegang($AanwezigLedenData['DATUM'])) && ($lidID!= $login->getUserFromSession()))
			throw new Exception("401;Geen schrijfrechten;");		

		// Voorkom dat datum meerdere keren voorkomt in de tabel
		try 	// Als record niet bestaat, krijgen we een exception
		{				
			$this->GetObject(null, $lidID, $aanmeldDatum, false);
		}
		catch (Exception $e) {}		

		if (parent::NumRows() > 0)
			throw new Exception("409;Aanmelding bestaat al;");

		// Neem data over uit aanvraag
		$a = $this->RequestToRecord($AanwezigLedenData);
							
		$id = parent::DbToevoegen($a);
		Debug(__FILE__, __LINE__, sprintf("AanwezigLeden toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($AanwezigLedenData)
	{
		$functie = "AanwezigLeden.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($AanwezigLedenData, true)));
		
		if ($AanwezigLedenData == null)
			throw new Exception("406;AanwezigLeden data moet ingevuld zijn;");	

		if (!array_key_exists('ID', $AanwezigLedenData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$id = isINT($AanwezigLedenData['ID'], "ID");
		$db_record = $this->GetObject($id, null, null, false);

		// De datum kan niet aangepast worden. 
		if (array_key_exists('DATUM', $AanwezigLedenData))
		{
			// we  moeten leading 0 plaatsen voor de datum, dan gaat 2020-4-2 ook goed. Dit wordt dan 2020-04-02
			$aanwezigDate = datetime::createfromformat('Y-m-d',$AanwezigLedenData['DATUM']);	
			$dbDate = datetime::createfromformat('Y-m-d',$db_record['DATUM']);
		
			if ($aanwezigDate->format("Y-m-d") !== $dbDate->format("Y-m-d"))
				throw new Exception("409;Datum kan niet gewijzigd worden;");
		}

		// De lid_id kan niet aangepast worden. 
		if (array_key_exists('LID_ID', $AanwezigLedenData))
		{
			if ($AanwezigLedenData['LID_ID'] != $db_record['LID_ID'])
				throw new Exception(sprintf("409;Lid ID (%s, %s) kan niet gewijzigd worden;",$AanwezigLedenData['LID_ID'], $db_record['LID_ID']));
		}

        $l = MaakObject('Login');
		if (($this->heeftDataToegang($db_record['DATUM']) == false) && ($db_record['LID_ID'] != $l->getUserFromSession()))
        throw new Exception("401;Geen schrijfrechten;");

		// Neem data over uit aanvraag
		$d = $this->RequestToRecord($AanwezigLedenData);            
		parent::DbAanpassen($id, $d);			
		return  $this->GetObject($id);
	}

	/*
	Aanmelden van een lid
	*/
	function Aanmelden($AanmeldenLedenData)
	{
		$functie = "AanwezigLeden.Aanmelden";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($AanmeldenLedenData, true)));

		if ($AanmeldenLedenData == null)
			throw new Exception("406;AanmeldenLedenData data moet ingevuld zijn;");	

		if (!array_key_exists('LID_ID', $AanmeldenLedenData))
			throw new Exception("406;LID_ID moet ingevuld zijn;");

		$LidID = isINT($AanmeldenLedenData['LID_ID'], "LID_ID", false, "Leden");			
		
		$datetime = new DateTime();
		$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 
		
		if (array_key_exists('TIJDSTIP', $AanmeldenLedenData))
			$datetime = isDATETIME($AanmeldenLedenData['TIJDSTIP'], "TIJDSTIP");
			
		if (!array_key_exists('DATUM', $AanmeldenLedenData))
		{
			$AanmeldenLedenData['DATUM'] = date('Y-m-d');
		}

		$l = MaakObject('Login');
		if ($LidID != $l->getUserFromSession()) 
		{
			if (!$this->heeftDataToegang($AanmeldenLedenData['DATUM']))
				throw new Exception("401;Geen schrijfrechten;");
		}

		$dateParts = explode('-', isDATE($AanmeldenLedenData['DATUM'], 'DATUM'));
		$datetime->setDate($dateParts[0], $dateParts[1], $dateParts[2]);
		
		// Check of lid al eerder voor deze dag is aangemeld. Zo ja, dan wordt id ingevuld
		$id = null;
		try
		{
			$db_data = $this->GetObject(null, $LidID, $datetime->format('Y-m-d'), false);
			$id = $db_data['ID'];
		}
		catch (Exception $e) {}		

		if ($id != null)
		{
			$AanmeldenLedenData['ID'] = $id;

			// Bouw CSV string als type voor type aangemeld wordt. Dus 1e aanmelding met alleen ASK21, daarna aanmelding LS4
			if (array_key_exists('VOORKEUR_VLIEGTUIG_TYPE', $AanmeldenLedenData))
			{
				$aanmeldType = $AanmeldenLedenData['VOORKEUR_VLIEGTUIG_TYPE'];	// voorkeur type uit aanmelding 
				$dbType = $db_data['VOORKEUR_VLIEGTUIG_TYPE'];				// voorkeur type uit database

				$a = explode(',',$dbType);

				if (in_array($aanmeldType, $a))
				{
					// heeft dit vliegtuig al als voorkeur, dus geen update
					unset($AanmeldenLedenData['VOORKEUR_VLIEGTUIG_TYPE']);			
				}
				else
				{
					// toevoegen van voorkeur type
					if ($dbType == null)
						$AanmeldenLedenData['VOORKEUR_VLIEGTUIG_TYPE'] = $aanmeldType;
					else
						$AanmeldenLedenData['VOORKEUR_VLIEGTUIG_TYPE'] = $dbType . "," . $aanmeldType;
				}
			}

			if (($AanmeldenLedenData['DATUM'] === date('Y-m-d')) && (is_null($db_data['AANKOMST'])))
				$AanmeldenLedenData['AANKOMST'] = $datetime->format('H:i:00');

			if (!is_null($db_data['VERTREK']))
				$AanmeldenLedenData['VERTREK'] = null;
			
			// Moeten we uberhaupt wel de database aanpassen
			$aanpassen = false;
			$velden = array(
				"ID",
				"DATUM",
				"POSITIE" ,
				"LID_ID" ,
				"VOORAANMELDING" ,
				"AANKOMST",
				"VERTREK",
				"OVERLAND_VLIEGTUIG_ID" ,
				"VOORKEUR_VLIEGTUIG_TYPE" ,
				"OPMERKINGEN");

			foreach ($velden as $veld)
			{
				if (array_key_exists($veld, $AanmeldenLedenData))
				{
					if ($db_data[$veld] != $AanmeldenLedenData[$veld])
					{
						$aanpassen = true;
						break;
					}
				}
			}
			if ($aanpassen)
			{
                // We zijn nu bijna klaar om aanmelding op te slaan in de database, maar voor DDWV moeten we strippen afschrijven
                $ddwv = MaakObject('DDWV');
                $id = $ddwv->AanmeldenLidAfboekenDDWV($AanmeldenLedenData);

                if ($id >= 0)
                    $AanmeldenLedenData['TRANSACTIE_ID'] = $id;

				$this->UpdateObject($AanmeldenLedenData);
				Debug(__FILE__, __LINE__, sprintf("AanwezigLeden aangepast id=%s", $id));		
				return  $this->GetObject($id);
			}	
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden NIET aangepast id=%s", $id));
			return $db_data;
		}
		
		$AanmeldenLedenData['VERTREK'] = null;	// zeker weten dat vertrek niet gezet wordt						

		// Zetten van de velden indien dit niet gedaan is
		if (!array_key_exists('DATUM', $AanmeldenLedenData))
			$AanmeldenLedenData['DATUM'] = $datetime->format('Y-m-d');
		
		if (($AanmeldenLedenData['DATUM'] === date('Y-m-d')) && (!array_key_exists('AANKOMST', $AanmeldenLedenData)))	
			$AanmeldenLedenData['AANKOMST'] = $datetime->format('H:i:00');

		// Achteraan aan de lijst van aanwezigen toevoegen, dit doe je door POSITIE te zetten
		if (!array_key_exists('POSITIE', $AanmeldenLedenData))	
		{
			$aanwezigen = $this->GetObjects(array(
				'BEGIN_DATUM' => $AanmeldenLedenData['DATUM'],
				'EIND_DATUM' => $AanmeldenLedenData['DATUM'],
				'VELDEN' => "POSITIE",		// Alleen POSITIE is nodig
				'SORT' => "POSITIE DESC",	// Sorteer op POSITIE aflopenend, hoogte POSITIE staat bovenaan
				'MAX' => 1,					// Dan hebben we ook maar 1 record nodig
			));

			$pos = 1;	// default, als er nog niemand aanwezig is
			if ($aanwezigen['totaal'] > 0) 	// Er zijn leden aanwezig
			{
				$pos = $aanwezigen['dataset'][0]['POSITIE'] + 1;
			}
			$AanmeldenLedenData['POSITIE'] = $pos;
		}

        // We zijn nu bijna klaar om aanmelding op te slaan in de database, maar voor DDWV moeten we strippen afschrijven
        $ddwv = MaakObject('DDWV');
        $id = $ddwv->AanmeldenLidAfboekenDDWV($AanmeldenLedenData);

        if ($id >= 0)
            $AanmeldenLedenData['TRANSACTIE_ID'] = $id;

		$aangemeld = $this->AddObject($AanmeldenLedenData);

		Debug(__FILE__, __LINE__, sprintf("AanwezigLeden toegevoegd id=%d", $aangemeld['ID']));
		return $this->GetObject($aangemeld['ID']);
	}

	/*
	Afmelden van een lid
	*/
	function Afmelden($AfmeldenLedenData)
	{
		$functie = "AanwezigLeden.Afmelden";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($AfmeldenLedenData, true)));

		if ($AfmeldenLedenData == null)
			throw new Exception("406;AfmeldenLedenData data moet ingevuld zijn;");	

		if (!array_key_exists('LID_ID', $AfmeldenLedenData))
			throw new Exception("406;LID_ID moet ingevuld zijn;");

		$LidID = isINT($AfmeldenLedenData['LID_ID'], "LID_ID");
		
		$datetime = new DateTime();
		$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 

		if (array_key_exists('TIJDSTIP', $AfmeldenLedenData))
			$datetime = isDATETIME($AfmeldenLedenData['TIJDSTIP'], "TIJDSTIP");

		try
		{
			$db_data = $this->GetObject(null, $LidID, $datetime->format('Y-m-d'), false);
			$AfmeldenLedenData['ID'] = $db_data['ID'];

			$l = MaakObject('Login');
			if ($LidID != $l->getUserFromSession()) 
			{
				if (!$this->heeftDataToegang($db_data['DATUM']))	
					throw new Exception("401;Geen schrijfrechten;");
			}
		}
		catch (Exception $e) 
		{
			throw new Exception("409;Kan een lid alleen afmelden als het eerst aangemeld is;");
		}		

		// Zetten van de velden indien dit niet gedaan is	
		if (!array_key_exists('VERTREK', $AfmeldenLedenData))	
			$AfmeldenLedenData['VERTREK'] = $datetime->format('H:i:00');

        // DDWV terug boeken, maar niet als we al een start hebben gemaakt
        $sObj = MaakObject('Startlijst');
        $params = array('LID_ID' => $db_data['LID_ID'],
                        'BEGIN_DATUM' => $db_data['DATUM'],
                        'EIND_DATUM' => $db_data['DATUM']);
        $logboek = $sObj->GetLogboek($params);
        Debug(__FILE__, __LINE__, sprintf("%s: logboek=%s", $functie, print_r($logboek, true)));

        if ($logboek['totaal'] == 0)       // er is nog niet gevlogen, dus mogen DDWV strippen terug
        {
            $ddwv = MaakObject('DDWV');
            $id = $ddwv->AfmeldenLidBijboekenDDWV($db_data);

            if ($id >= 0) {
                $AfmeldenLedenData['TRANSACTIE_ID'] = null;
            }
        }
		$this->UpdateObject($AfmeldenLedenData);

		Debug(__FILE__, __LINE__, sprintf("%s: AanwezigLeden aangepast id=%s", $functie, $AfmeldenLedenData['ID']));
		return  $this->GetObject($AfmeldenLedenData['ID']);
	}	
	
	/* 
	Welke potentiele vligers hebben we voor dit vliegtuig?
	*/
	function PotentieelVliegers($vliegtuigID, $datum = null)
	{
		$functie = "AanwezigLeden.PotentieelVliegers";
		Debug(__FILE__, __LINE__, sprintf("%s(%s,%s)", $functie, $vliegtuigID, $datum));

		if (!$this->heeftDataToegang($datum))	
			throw new Exception("401;Geen leesrechten;");

		if ($vliegtuigID == null)
			throw new Exception("406;Geen VLIEGTUID_ID in aanroep;");

		$vliegtuigID = isINT($vliegtuigID, "VLIEGTUIG_ID");
			
		$query_params = array();

		$where = " WHERE DATUM = ? ";
		array_push($query_params, ($datum == null) ? date("Y-m-d") : $datum);

		// Haal de info op uit de daginfo
		$retVal = null;
		$hierop = "1 AS HIEROP";
		$condition = "";

		$rv = MaakObject('Vliegtuigen');
		$rvObj = $rv->GetObject($vliegtuigID);
		
		if ($rvObj['CLUBKIST'] == 1)
		{
			$condition .= " AND ((OVERLAND_VLIEGTUIG_ID = ?) OR (VOORKEUR_VLIEGTUIG_TYPE LIKE '%" . $rvObj['TYPE_ID'] . "%'))";
			array_push($query_params, $rvObj['ID']);
		}
		else
		{
			$condition .= " AND (OVERLAND_VLIEGTUIG_ID = ? )";
			array_push($query_params, $rvObj['ID']);			
		}

		$query = "SELECT LID_ID, NAAM FROM `aanwezig_leden_view`" . $where . $condition;
		
		parent::DbOpvraag($query, $query_params);
		Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan A = %d", parent::NumRows()));
		
		if (parent::NumRows() > 0)					// We hebben potentiele vliegers,
			return parent::DbData();

		// We hebben geen potentiele vliegers, dan plan B
		if ($rvObj['CLUBKIST'] == 1)
		{
			// Alle leden die vandaag aanwezig zijn, 601 = 'Erelid', 602 = 'Lid', 603 = 'Jeugdlid', 606	= 'Donateur', 608 = '5-rittenkaarthouder', 611 = 'Cursist'

			$query = sprintf("SELECT LID_ID, NAAM FROM `aanwezig_leden_view` %s AND LIDTYPE_ID IN (601, 602, 603, 606, 608, 611)", $where);
			parent::DbOpvraag($query, array( ($datum == null) ? date("Y-m-d") : $datum ));
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan B1 = %d", parent::NumRows()));

			if (parent::NumRows() > 0)				// We hebben potentiele vliegers,
				return parent::DbData();		
		}
		else
		{
			// Iedereen die de laatste 90 dagen op dit vliegtuig gevlogen heeft.

			$sl= MaakObject('Startlijst');

			$params['VLIEGTUIG_ID'] = $vliegtuigID;
			$params['BEGIN_DATUM'] = date('Y-m-d', strtotime('-90 days', strtotime($datum)));
			$params['EIND_DATUM'] = date('Y-m-d', strtotime($datum));

			$startlijst = $sl->GetObjects($params);
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan B2 = %d", $startlijst['totaal']));

			if ($startlijst['totaal'] > 0)
			{
				$retVal = array();

				foreach ($startlijst['dataset'] as $vlucht)
				{
					$vlieger = array (
						'LID_ID' => $vlucht['VLIEGER_ID'],
						'NAAM' => $vlucht['VLIEGERNAAM_LID']
					);

					if (!in_array($vlieger, $retVal)) 
						array_push($retVal, $vlieger);
				}
				return $retVal;
			}
		}

		// Jeetje nog niets, dan maar plan C, alle aanwezigen
		$query = sprintf("SELECT LID_ID, NAAM FROM `aanwezig_leden_view` %s", $where);														
		parent::DbOpvraag($query, array( ($datum == null) ? date("Y-m-d") : $datum ));
		Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan C = %d ", parent::NumRows()));

		if (parent::NumRows() > 0)				// We hebben potentiele vliegers,
			return parent::DbData();		

		// Als dan niets lukt, dan maar iedereen. Laten we het plan D noemen				
		$params = array();	
		$params["VELDEN"] = "ID,NAAM";
		$params["TYPES"] = "";	

		$t = MaakObject('Types');
		$types = $t->GetObjects(array('GROEP' => 6, 'VELDEN' => "ID")); 	// groep 6 = lid types

		// maak CSV string met alle lidtypes
		foreach ($types['dataset'] as $type)
		{
			if (isset($params["TYPES"]))
				$params["TYPES"]  .= ",";

			$params["TYPES"] .= $type['ID'];	
		}

		// Bekijk daginfo of we DDWV kun uitsluiten
		$di = MaakObject('Daginfo');

		// als er geen daginfo is, dan komt er een exceptie
		try {
			$diObj = $di->GetObject(null, ($datum == null) ? date("Y-m-d") : $datum );

			if ($diObj['DDWV'] !== "1") 	// Vandaag is er geen DDWV	
			{
				$params["TYPES"] = str_replace("625", "999999", $params["TYPES"]);		// 625 = DDWV, die moet er uit, 999999 bestaat niet als lidtype
			}
		}
		catch(Exception $exception)
		{
			list($dummy, $exceptionMsg) = explode(": ", $exception);
			list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

			if ($httpStatus != "404")
				throw new Exception($exception);
		}

		$ll = MaakObject('Leden');	
		$llijst = $ll->GetObjects($params);
		Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan D = %d", $llijst['totaal']));
			
		$retVal = array();
		foreach ($llijst['dataset'] as $lid)
		{
			$lid = array (
				'LID_ID' => $lid['ID'],
				'NAAM' => $lid['NAAM']
			);

			array_push($retVal, $lid);
		}
		return $retVal;
	}

	function IsAangemeldVandaag($lidID) 
	{
		try {
			$aanwezigRecord = $this->GetObject(null, $lidID, date("Y-m-d"));
			return true;
		}
		catch(Exception $exception)
		{
			// exceptie als er geen aanmelding is
			return false;
		}
		return true;
	}

	// privacy maskering
	function privacyMask($aanmelding, $privacy) 
	{
		$l = MaakObject('Login');
		if (($l->isBeheerder() === true) ||
			($l->isBeheerderDDWV() === true) ||
			($l->isInstructeur() === true) ||
			($l->isStarttoren() == true) ||
			($l->isCIMT() === true))	
		{
			return $aanmelding;
		}

		
		if (($aanmelding['LID_ID'] != $l->getUserFromSession()) &&  ($aanmelding['PRIVACY'] || $privacy))
		{
			$aanmelding['VOORNAAM'] 		= "****";
			$aanmelding['TUSSENVOEGSEL'] 	= "****";
			$aanmelding['ACHTERNAAM'] 		= "****";
			$aanmelding['NAAM'] 			= "****";
			$aanmelding['INLOGNAAM'] 		= "****";
			$aanmelding['EMAIL'] 			= "****";
			$aanmelding['LIDNR'] 			= "****";
		}

		return $aanmelding;
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
	
		$field = 'POSITIE';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true);

		$field = 'LID_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, 'Leden');

		$field = 'VELD_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, 'Types');

        $field = 'TRANSACTIE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, 'Transacties');

		$field = 'VOORAANMELDING';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);
		
		$field = 'AANKOMST';
		if (array_key_exists($field, $input))
			$record[$field] = isTIME($input[$field], $field, true);
			
		$field = 'VERTREK';
		if (array_key_exists($field, $input))
			$record[$field] = isTIME($input[$field], $field, true);
			
		$field = 'OVERLAND_VLIEGTUIG_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, "Vliegtuigen");
		
		$field = 'VOORKEUR_VLIEGTUIG_TYPE';
		if (array_key_exists($field, $input))		
			$record[$field] = isCSV($input[$field], $field, true);

		$field = 'OPMERKINGEN';
		if (array_key_exists($field, $input))
			$record[$field] = $input[$field];	
			
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

		if (isset($record['POSITIE']))
			$retVal['POSITIE']  = $record['POSITIE'] * 1;
		
		if (isset($record['LID_ID']))
			$retVal['LID_ID']  = $record['LID_ID'] * 1;	

		if (isset($record['OVERLAND_VLIEGTUIG_ID']))
			$retVal['OVERLAND_VLIEGTUIG_ID']  = $record['OVERLAND_VLIEGTUIG_ID'] * 1;		

		if (isset($record['LIDTYPE_ID']))
			$retVal['LIDTYPE_ID']  = $record['LIDTYPE_ID'] * 1;	

		if (isset($record['ZUSTERCLUB_ID']))
			$retVal['ZUSTERCLUB_ID']  = $record['ZUSTERCLUB_ID'] * 1;	

		if (isset($record['STARTS']))
			$retVal['STARTS']  = $record['STARTS'] * 1;		

		if (isset($record['VELD_ID']))
			$retVal['VELD_ID']  = $record['VELD_ID'] * 1;

        if (isset($record['TRANSACTIE_ID']))
            $retVal['TRANSACTIE_ID']  = $record['TRANSACTIE_ID'] * 1;

        // booleans
		if (isset($record['VOORAANMELDING']))
			$retVal['VOORAANMELDING']  = $record['VOORAANMELDING'] == "1" ? true : false;

		if (isset($record['LIERIST']))
			$retVal['LIERIST']  = $record['LIERIST'] == "1" ? true : false;

		if (isset($record['STARTLEIDER']))
			$retVal['STARTLEIDER']  = $record['STARTLEIDER'] == "1" ? true : false;

		if (isset($record['INSTRUCTEUR']))
			$retVal['INSTRUCTEUR']  = $record['INSTRUCTEUR'] == "1" ? true : false;

		if (isset($record['CIMT']))
			$retVal['CIMT']  = $record['CIMT'] == "1" ? true : false;

		if (isset($record['DDWV_CREW']))
			$retVal['DDWV_CREW']  = $record['DDWV_CREW'] == "1" ? true : false;
			
		if (isset($record['DDWV_BEHEERDER']))
			$retVal['DDWV_BEHEERDER']  = $record['DDWV_BEHEERDER'] == "1" ? true : false;
			
		if (isset($record['BEHEERDER']))
			$retVal['BEHEERDER']  = $record['BEHEERDER'] == "1" ? true : false;
			
		if (isset($record['STARTTOREN']))
			$retVal['STARTTOREN']  = $record['STARTTOREN'] == "1" ? true : false;
			
		if (isset($record['ROOSTER']))
			$retVal['ROOSTER']  = $record['ROOSTER'] == "1" ? true : false;
			
		if (isset($record['CLUBBLAD_POST']))
			$retVal['CLUBBLAD_POST']  = $record['CLUBBLAD_POST'] == "1" ? true : false;

		if (isset($record['AUTH']))
			$retVal['AUTH']  = $record['AUTH'] == "1" ? true : false;
			
		if (isset($record['STARTVERBOD']))
			$retVal['STARTVERBOD']  = $record['STARTVERBOD'] == "1" ? true : false;
			
		if (isset($record['PRIVACY']))
			$retVal['PRIVACY']  = $record['PRIVACY'] == "1" ? true : false;

		if (isset($record['VLIEGT']))
			$retVal['VLIEGT']  = $record['VLIEGT'] == "1" ? true : false;
			
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;			

		return $retVal;
	}
}
