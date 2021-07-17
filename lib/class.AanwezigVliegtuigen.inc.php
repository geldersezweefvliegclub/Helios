<?php
	class AanwezigVliegtuigen extends Helios
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "oper_aanwezig_vliegtuigen";
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
                    `AANKOMST` time DEFAULT NULL,
                    `VERTREK` time DEFAULT NULL,
					`LATITUDE` decimal(8,5) DEFAULT NULL,
					`LONGITUDE` decimal(8,5) DEFAULT NULL,
                    `HOOGTE` smallint DEFAULT NULL,
                    `SNELHEID` smallint DEFAULT NULL,
					`VERWIJDERD` tinyint NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
					CONSTRAINT ID_PK PRIMARY KEY (ID),
                        INDEX (`DATUM`), 
                        INDEX (`VLIEGTUIG_ID`), 
						INDEX (`VERWIJDERD`),

					FOREIGN KEY (VLIEGTUIG_ID) REFERENCES ref_vliegtuigen(ID)
				)", $this->dbTable);
			parent::DbUitvoeren($query);

            if (isset($FillData))
            {
                $inject = array(
                    "1, '####-05-01', 201, '07:58:00'   , NULL ",
                    "2, '####-05-01', 208, '09:01:00'   , '19:01:00' ",
                    "3, '####-05-01', 200, '11:03:00'   , '16:01:00' ",
					"4, '####-05-01', 211, '13:18:00'   , NULL ",
					"5, '####-05-02', 211, '09:43:00'  	, NULL ",
					"6, '####-05-02', 218, '10:22:00'  	, NULL ",
					"7, '####-05-03', 217, '09:57:00'  	, NULL ",
					"8, '####-05-04', 201, '12:03:00'  	, NULL ",
					"9, '####-05-04', 200, '12:45:00'  	, NULL ",
					"10, '####-05-04', 216,'11:57:00'   , NULL ");

				$inject = str_replace("####", strval(date("Y")), $inject);		// aanwezigheid in dit jaar

                $i = 0;    
                foreach ($inject as $record)
                {                   
                    $query = sprintf("
                            INSERT INTO `%s` (
                                `ID`, 
                                `DATUM`, 
                                `VLIEGTUIG_ID`, 
                                `AANKOMST`, 
                                `VERTREK`) 
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
					`av`.`ID`,
                    `av`.`DATUM`,
                    `av`.`VLIEGTUIG_ID`,
					time_format(`av`.`AANKOMST`,'%%H:%%i') AS `AANKOMST`,
					time_format(`av`.`VERTREK`,'%%H:%%i') AS `VERTREK`,
					`av`.`LATITUDE`,
					`av`.`LONGITUDE`,
                    `av`.`HOOGTE`,
                    `av`.`SNELHEID`,
					`av`.`VERWIJDERD`,
					`av`.`LAATSTE_AANPASSING`, 
					`v`.`REGISTRATIE`, 
					`v`.`CALLSIGN`, 
					`v`.`ZITPLAATSEN`, 
					`v`.`CLUBKIST`, 
					`v`.`FLARMCODE`, 
					`v`.`TYPE_ID`, 
					`v`.`TMG`, 
					`v`.`ZELFSTART`, 
					`v`.`SLEEPKIST`, 
					`v`.`VOLGORDE`, 
					CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`
				FROM
					`%s` `av`
					LEFT JOIN `ref_vliegtuigen` `v` ON (`av`.`VLIEGTUIG_ID` = `v`.`ID`)
				WHERE
					`av`.`VERWIJDERD` = %d  
				ORDER BY 
					DATUM DESC, CLUBKIST, VOLGORDE, ID;";				
			
			parent::DbUitvoeren("DROP VIEW IF EXISTS aanwezig_vliegtuigen_view");							
			parent::DbUitvoeren(sprintf($query, "aanwezig_vliegtuigen_view", $this->dbTable, 0));

			parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_aanwezig_vliegtuigen_view");
			parent::DbUitvoeren(sprintf($query, "verwijderd_aanwezig_vliegtuigen_view", $this->dbTable, 1));		
		}

		/*
		Haal een enkel record op uit de database
		*/		
		function GetObject($ID = null, $VLIEGTUIG_ID = null, $DATUM = null, $heeftVerwijderd = true)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.GetObject(%s,%s,%s,%s)", $ID, $VLIEGTUIG_ID, $DATUM, $heeftVerwijderd));	

			$conditie = array();
			if ($ID !== null)
			{
				$conditie['ID'] = isINT($ID, "ID");
			}
			else
			{
				if (($DATUM == null) || ($VLIEGTUIG_ID == null))
					throw new Exception("406;Geen ID en LID_ID/DATUM in aanroep;");

				$conditie['VLIEGTUIG_ID'] = isINT($VLIEGTUIG_ID, "VLIEGTUIG_ID", false, "Vliegtuigen");
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
			
			$obj = $this->RecordToOutput($obj);	
			return $obj;	
		}
	
		/*
		Haal een dataset op met records als een array uit de database. 
		*/		
		function GetObjects($params)
		{
			$functie = "AanwezigVliegtuigen.GetObjects";
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
							$where .= " AND (REG_CALL LIKE ?) ";

							$s = "%" . trim($value) . "%";
							array_push($query_params, $s);

							Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));
							break;
						}
					case "IN" : 
						{
							isCSV($value, "IN");
							$in = sprintf(" VLIEGTUIG_ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: IN='%s'", $functie, $value));
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
					case "VLIEGTUIG_ID" : 
						{
							$vliegtuigID = isINT($value, "VLIEGTUIG_ID");

							$where .= " AND VLIEGTUIG_ID = ? ";
							array_push($query_params, $vliegtuigID);

							Debug(__FILE__, __LINE__, sprintf("%s: VLIEGTUIG_ID='%s'", $functie, $vliegtuigID));
							break;
						}	
					default:
						{
							throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
							break;
						}										
				}
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

			// Als er geen datum is meegegeven dan alleen vandaag
			if ((strpos($where, 'DATUM') === false) && (strpos($where, 'ID') === false)) {
				$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));
			}			
				
			$query = "
				SELECT 
					%s
				FROM
					`####aanwezig_vliegtuigen_view`" . $where . $orderby;
			$query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : "") , $query);
			
			$retVal = array();

			$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
			$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);
			$retVal['hash'] = dechex((str_replace(":", "", substr($retVal['laatste_aanpassing'], -8)) * 1000) + ($retVal['totaal'] * 1));
			Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s, HASH=%s", $retVal['totaal'], $retVal['laatste_aanpassing'], $retVal['hash']));

			if ($retVal['hash'] == $hash)
				throw new Exception("304;Dataset ongewijzigd;");

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
		function VerwijderObject($id = null, $vliegtuig_id = null, $datum = null, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.VerwijderObject('%s', %s, %s, %s)", $id, $vliegtuig_id, $datum, (($verificatie === false) ? "False" :  $verificatie)));					
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($id !== null)
			{
				isCSV($id, "ID");
			}
			else
			{
				if (($datum == null) || ($vliegtuig_id == null))
					throw new Exception("406;Geen ID en VLIEGTUIG_ID/DATUM in aanroep;");

				isINT($vliegtuig_id, "VLIEGTUIG_ID");
				isDATE($datum. "DATUM");	
			}
			
			if ($id == null)
			{
				$vObj = $this->GetObject(null, $vliegtuig_id, $datum);
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
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.HerstelObject('%s')", $id));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($id == null)
				throw new Exception("406;Geen ID in aanroep;");
			
			isCSV($id, "ID");
			parent::HerstelVerwijderd($id);
		}		

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function AddObject($AanwezigVliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.AddObject(%s)", print_r($AanwezigVliegtuigData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanwezigVliegtuigData == null)
				throw new Exception("406;AanwezigLeden data moet ingevuld zijn;");	

			$where = "";
			$nieuw = true;
			if (array_key_exists('ID', $AanwezigVliegtuigData))
			{
				$id = isINT($AanwezigVliegtuigData['ID'], "ID");
				
				// ID is opgegeven, maar bestaat record?
				try 	// Als record niet bestaat, krijgen we een exception
				{		
					$this->GetObject($id, null, null);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));				
			}

			if (!array_key_exists('VLIEGTUIG_ID', $AanwezigVliegtuigData))
				throw new Exception("406;VLIEGTUIG_ID is verplicht;");		

			if (!array_key_exists('DATUM', $AanwezigVliegtuigData))
				throw new Exception("406;DATUM is verplicht;");		
				
			$vliegtuigID = isINT($AanwezigVliegtuigData['VLIEGTUIG_ID'], "VLIEGTUIG_ID", true, "Vliegtuigen");
			$aanmeldDatum = isDATE($AanwezigVliegtuigData['DATUM'], "DATUM");	

			// Voorkom dat datum meerdere keren voorkomt in de tabel
			try 	// Als record niet bestaat, krijgen we een exception
			{				
				$this->GetObject(null, $vliegtuigID, $aanmeldDatum, false);
			}
			catch (Exception $e) {}		

			if (parent::NumRows() > 0)
				throw new Exception("409;Aanmelding bestaat al;");
	
			// Neem data over uit aanvraag
			$a = $this->RequestToRecord($AanwezigVliegtuigData);
								
			$id = parent::DbToevoegen($a);
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen toegevoegd id=%d", $id));

			return $this->GetObject($id);
		}

		/*
		Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function UpdateObject($AanwezigVliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.UpdateObject(%s)", print_r($AanwezigVliegtuigData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanwezigVliegtuigData == null)
				throw new Exception("406;AanwezigVliegtuigen data moet ingevuld zijn;");	

			if (!array_key_exists('ID', $AanwezigVliegtuigData))
				throw new Exception("406;ID moet ingevuld zijn;");
				
			$id = isINT($AanwezigVliegtuigData['ID'], "ID");
			$db_record = $this->GetObject($id, null, null, false);

            // De datum kan niet aangepast worden. 
			if (array_key_exists('DATUM', $AanwezigVliegtuigData))
			{
				if ($AanwezigVliegtuigData['DATUM'] !== $db_record['DATUM'])
					throw new Exception("409;Datum kan niet gewijzigd worden;");
			}

            // De vliegtuig_id kan niet aangepast worden. 
			if (array_key_exists('VLIEGTUIG_ID', $AanwezigVliegtuigData))
			{
				if ($AanwezigVliegtuigData['VLIEGTUIG_ID'] != $db_record['VLIEGTUIG_ID'])
					throw new Exception(sprintf("409;Vliegtuig ID (%s, %s) kan niet gewijzigd worden;",$AanwezigVliegtuigData['VLIEGTUIG_ID'], $db_record['VLIEGTUIG_ID']));
			}

			// Neem data over uit aanvraag
			$d = $this->RequestToRecord($AanwezigVliegtuigData);            
			parent::DbAanpassen($id, $d);			
			return  $this->GetObject($id);
		}

		/*
		Aanmelden van een lid
		*/
		function Aanmelden($AanmeldenVliegtuigData, $zetTijd = true)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.Aanmelden(%s)", print_r($AanmeldenVliegtuigData, true)));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanmeldenVliegtuigData == null)
				throw new Exception("406;AanmeldenVliegtuigData data moet ingevuld zijn;");	

			if (!array_key_exists('VLIEGTUIG_ID', $AanmeldenVliegtuigData))
				throw new Exception("406;VLIEGTUIG_ID moet ingevuld zijn;");

			$vliegtuigID = isINT($AanmeldenVliegtuigData['VLIEGTUIG_ID'], "VLIEGTUIG_ID", false, "Vliegtuigen");			

			$datetime = new DateTime();
			$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 
			
			if (array_key_exists('TIJDSTIP', $AanmeldenVliegtuigData))
				$datetime = isDATETIME($AanmeldenVliegtuigData['TIJDSTIP'], "TIJDSTIP");
	
			if (array_key_exists('DATUM', $AanmeldenVliegtuigData))
			{
				$dateParts = explode('-', isDATE($AanmeldenVliegtuigData['DATUM'], 'DATUM'));
				$datetime->setDate($dateParts[0], $dateParts[1], $dateParts[2]);
			}

			$id = null;
			try
			{
				$db_data = $this->GetObject(null, $vliegtuigID, $datetime->format('Y-m-d'), false);
				$id = $db_data['ID'];
			}
			catch (Exception $e) {}		

			if ($id != null)
			{
				$AanmeldenVliegtuigData['ID'] = $id;
				
				if (is_null($db_data['AANKOMST']))
					$AanmeldenVliegtuigData['AANKOMST'] = $datetime->format('H:i:00');

				if (!is_null($db_data['VERTREK']))
					$AanmeldenVliegtuigData['VERTREK'] = null;

				$this->UpdateObject($AanmeldenVliegtuigData);
				Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen aangepast id=%s", $id));		
				return  $this->GetObject($id);
			}
			
			$AanmeldenVliegtuigData['VERTREK'] = null;	// zeker weten dat vertrek niet gezet wordt

			// Zetten van de velden indien dit niet gedaan is
			if (!array_key_exists('DATUM', $AanmeldenVliegtuigData))
				$AanmeldenVliegtuigData['DATUM'] = $datetime->format('Y-m-d');
			
			if (!array_key_exists('AANKOMST', $AanmeldenVliegtuigData))	
				$AanmeldenVliegtuigData['AANKOMST'] = $datetime->format('H:i:00');

			$aangemeld = $this->AddObject($AanmeldenVliegtuigData);

			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden toegevoegd id=%d", $aangemeld['ID']));
			return $this->GetObject($aangemeld['ID']);
		}

		/*
		Afmelden van een vliegtuig
		*/
		function Afmelden($AfmeldenVliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("AfmeldenVliegtuigData.Afmelden(%s)", print_r($AfmeldenVliegtuigData, true)));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AfmeldenVliegtuigData == null)
				throw new Exception("406;AfmeldenVliegtuigData data moet ingevuld zijn;");	

			if (!array_key_exists('VLIEGTUIG_ID', $AfmeldenVliegtuigData))
				throw new Exception("406;VLIEGTUIG_ID moet ingevuld zijn;");

			$vliegtuigID = isINT($AfmeldenVliegtuigData['VLIEGTUIG_ID'], "VLIEGTUIG_ID");
		   
			$datetime = new DateTime();
			$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 

			if (array_key_exists('TIJDSTIP', $AfmeldenVliegtuigData))
				$datetime = isDATETIME($AfmeldenVliegtuigData['TIJDSTIP'], "TIJDSTIP");
	
			try
			{
				$db_data = $this->GetObject(null, $vliegtuigID, $datetime->format('Y-m-d'), false);
				$AfmeldenVliegtuigData['ID'] = $db_data['ID'];
			}
			catch (Exception $e) 
			{
				throw new Exception("409;Kan een vliegtuig alleen afmelden als het eerst aangemeld is;");
			}		

			// Aankomst was al gezet, mag niet overschreven worden
			unset($AfmeldenVliegtuigData['AANKOMST']);

			// Neem data over uit aanvraag
			if (!array_key_exists('VERTREK', $AfmeldenVliegtuigData))	
				$AfmeldenVliegtuigData['VERTREK'] = $datetime->format('H:i:00');
				
			$this->UpdateObject($AfmeldenVliegtuigData);

			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen aangepast id=%s", $AfmeldenVliegtuigData['ID']));		
			return  $this->GetObject($AfmeldenVliegtuigData['ID']);
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

			$field = 'LATITUDE';
			if (array_key_exists($field, $input))
				$record[$field] = isLAT($input[$field], $field, true);

			$field = 'LONGITUDE';
			if (array_key_exists($field, $input))
				$record[$field] = isLON($input[$field], $field, true);				

			$field = 'DATUM';
			if (array_key_exists($field, $input))
				$record[$field] = isDATE($input[$field], $field);

			$field = 'VLIEGTUIG_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, false, 'Vliegtuigen');
				 
			$field = 'AANKOMST';
			if (array_key_exists($field, $input))
				$record[$field] = isTIME($input[$field], $field, true);				
				
			$field = 'VERTREK';
			if (array_key_exists($field, $input))
				$record[$field] = isTIME($input[$field], $field, true);

			$field = 'SNELHEID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true);

			$field = 'HOOGTE';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true);

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

			if (isset($record['VLIEGTUIG_ID']))
				$retVal['VLIEGTUIG_ID']  = $record['VLIEGTUIG_ID'] * 1;
			
			if (isset($record['LATITUDE']))
				$retVal['LATITUDE']  = $record['LATITUDE'] * 1;	

			if (isset($record['LONGITUDE']))
				$retVal['LONGITUDE']  = $record['LONGITUDE'] * 1;		

			if (isset($record['HOOGTE']))
				$retVal['HOOGTE']  = $record['HOOGTE'] * 1;						
						
			if (isset($record['SNELHEID']))
				$retVal['SNELHEID']  = $record['SNELHEID'] * 1;		

			if (isset($record['ZITPLAATSEN']))
				$retVal['ZITPLAATSEN']  = $record['ZITPLAATSEN'] * 1;	

			if (isset($record['VOLGORDE']))
				$retVal['VOLGORDE']  = $record['VOLGORDE'] * 1;	

			if (isset($record['TYPE_ID']))
				$retVal['TYPE_ID']  = $record['TYPE_ID'] * 1;								

			// booleans	
			if (isset($record['VERWIJDERD']))
				$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

			if (isset($record['CLUBKIST']))
				$retVal['CLUBKIST']  = $record['CLUBKIST'] == "1" ? true : false;

			if (isset($record['TMG']))
				$retVal['TMG']  = $record['TMG'] == "1" ? true : false;

			if (isset($record['ZELFSTART']))
				$retVal['ZELFSTART']  = $record['ZELFSTART'] == "1" ? true : false;

			if (isset($record['SLEEPKIST']))
				$retVal['SLEEPKIST']  = $record['SLEEPKIST'] == "1" ? true : false;

			return $retVal;
		}
		
		
	}