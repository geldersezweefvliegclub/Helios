<?php
	class AanwezigLeden extends StartAdmin
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "oper_aanwezig_leden";
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
                    `OPMERKINGEN` text DEFAULT NULL,
					`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
					CONSTRAINT ID_PK PRIMARY KEY (ID),
                        INDEX (`DATUM`), 
                        INDEX (`LID_ID`), 
						INDEX (`VERWIJDERD`),

					FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
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
					`l`.`NAAM` AS `VLIEGER`,
					`l`.`LIDTYPE_ID` AS `LIDTYPE_ID`,  
					CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`
				FROM
					`%s` `al`
					LEFT JOIN `ref_leden` `l` ON `al`.`LID_ID` = `l`.`ID`
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
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.GetObject(%s,%s,%s,%s)", $ID, $LID_ID, $DATUM, $heeftVerwijderd));	

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
			}

			if ($heeftVerwijderd == false)
				$conditie['VERWIJDERD'] = 0;		// Dus geen verwijderd record

			$obj = parent::GetSingleObject($conditie);
			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");
			
			if (!is_null($obj['AANKOMST']))
				$obj['AANKOMST'] = substr($obj['AANKOMST'] , 0, 5);	// alleen hh:mm
			
			if (!is_null($obj['VERTREK']))
				$obj['VERTREK'] = substr($obj['VERTREK'] , 0, 5);	// alleen hh:mm

			return $obj;	
		}
	
		/*
		Haal een dataset op met records als een array uit de database. 
		*/		
		function GetObjects($params)
		{
			$functie = "AanwezigLeden.GetObjects";
			Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
			
			$where = ' WHERE 1=1 ';
			$orderby = "";
			$alleenLaatsteAanpassing = false;
			$limit = -1;
			$start = -1;
			$velden = "*";
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
					case "LAATSTE_AANPASSING" : 
						{
							$alleenLaatsteAanpassing = isBOOL($value, "LAATSTE_AANPASSING");

							Debug(__FILE__, __LINE__, sprintf("%s: LAATSTE_AANPASSING='%s'", $functie, $alleenLaatsteAanpassing));
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
							$where .= sprintf(" AND LID_ID IN(%s)", trim($value));

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

							$where .= " AND DATUM >= ? ";
							array_push($query_params, $beginDatum);

							Debug(__FILE__, __LINE__, sprintf("%s: BEGIN_DATUM='%s'", $functie, $beginDatum));
							break;
						}
					case "EIND_DATUM" : 
						{
							$eindDatum = isDATE($value, "EIND_DATUM");

							$where .= " AND DATUM <= ? ";
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

			// Als er geen datum is meegegeven dan alleen vandaag
			if ((strpos($where, 'DATUM') === false) && (strpos($where, 'ID') === false)) {
				$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));
			}
				
			$query = "
				SELECT 
					%s
				FROM
					`aanwezig_leden_view`" . $where . $orderby;
			
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

				return $retVal;
			}
			return null;  // Hier komen we nooit :-)
		}	

		/*
		Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
		Het veld VERWIJDERD wordt op "1" gezet.
		*/
		function VerwijderObject($id = null, $lid_id = null, $datum = null, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.VerwijderObject('%s', %s, %s, %s)", $id, $lid_id, $datum, (($verificatie === false) ? "False" :  $verificatie)));					
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($id !== null)
			{
				isCSV($id, "ID");
			}
			else
			{
				if (($datum == null) || ($lid_id == null))
					throw new Exception("406;Geen ID en LID_ID/DATUM in aanroep;");

				isINT($lid_id, "LID_ID");
				isDATE($datum, "DATUM");	
			}

			if ($ID == null)
			{
				$vObj = $this->GetObject(null, $lid_id, $datum);
				$id = $vObj["ID"];
				
				$verificatie = false;	// we weten zeker dat record bestaat
			}
			
			parent::MarkeerAlsVerwijderd($id, $verificatie);		
		}		

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function AddObject($AanwezigLedenData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.AddObject(%s)", print_r($AanwezigLedenData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

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
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.UpdateObject(%s)", print_r($AanwezigLedenData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanwezigLedenData == null)
				throw new Exception("406;AanwezigLeden data moet ingevuld zijn;");	

			if (!array_key_exists('ID', $AanwezigLedenData))
				throw new Exception("406;ID moet ingevuld zijn;");

			$id = isINT($AanwezigLedenData['ID'], "ID");
			$db_record = $this->GetObject($id, null, null, false);

            // De datum kan niet aangepast worden. 
			if (array_key_exists('DATUM', $AanwezigLedenData))
			{
				if ($AanwezigLedenData['DATUM'] !== $db_record['DATUM'])
					throw new Exception("409;Datum kan niet gewijzigd worden;");
			}

            // De lid_id kan niet aangepast worden. 
			if (array_key_exists('LID_ID', $AanwezigLedenData))
			{
				if ($AanwezigLedenData['LID_ID'] != $db_record['LID_ID'])
					throw new Exception("409;Lid ID kan niet gewijzigd worden;");
			}

			// Neem data over uit aanvraag
			$d = $this->RequestToRecord($AanwezigLedenData);            
			parent::DbAanpassen($id, $d);			
			return  $this->GetObject($id);
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
		Aanmelden van een lid
		*/
		function Aanmelden($AanmeldenLedenData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.Aanmelden(%s)", print_r($AanmeldenLedenData, true)));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanmeldenLedenData == null)
				throw new Exception("406;AanmeldenLedenData data moet ingevuld zijn;");	

			if (!array_key_exists('LID_ID', $AanmeldenLedenData))
				throw new Exception("406;LID_ID moet ingevuld zijn;");

			$LidID = isINT($AanmeldenLedenData['LID_ID'], "LID_ID", false, "Leden");			
		   
			$datetime = new DateTime();
			$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 
			
			if (array_key_exists('TIJDSTIP', $AanmeldenLedenData))
				$datetime = isDATETIME($AanmeldenLedenData['TIJDSTIP'], "TIJDSTIP");
				
			if (array_key_exists('DATUM', $AanmeldenLedenData))
			{
				$dateParts = explode('-', isDATE($AanmeldenLedenData['DATUM'], 'DATUM'));
				$datetime->setDate($dateParts[0], $dateParts[1], $dateParts[2]);
			}

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

					if (strstr($dbType, $aanmeldType) !== false)
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

				if (is_null($db_data['AANKOMST']))
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
			
			if (!array_key_exists('AANKOMST', $AanmeldenLedenData))	
				$AanmeldenLedenData['AANKOMST'] = $datetime->format('H:i:00');

			$aangemeld = $this->AddObject($AanmeldenLedenData);

			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden toegevoegd id=%d", $aangemeld['ID']));
			return $this->GetObject($aangemeld['ID']);
		}

		/*
		Afmelden van een lid
		*/
		function Afmelden($AfmeldenLedenData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.Afmelden(%s)", print_r($AfmeldenLedenData, true)));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

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
			}
			catch (Exception $e) 
			{
				throw new Exception("409;Kan een lid alleen afmelden als het eerst aangemeld is;");
			}		

			// Aankomst was al gezet, mag niet overschreven worden
			unset($AanmeldenLedenData['AANKOMST']);
	
			// Zetten van de velden indien dit niet gedaan is	
			if (!array_key_exists('VERTREK', $AfmeldenLedenData))	
				$AfmeldenLedenData['VERTREK'] = $datetime->format('H:i:00');			

			$this->UpdateObject($AfmeldenLedenData);

			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden aangepast id=%s", $AfmeldenLedenData['ID']));		
			return  $this->GetObject($AfmeldenLedenData['ID']);
		}	
		
		/* 
		Welke potentiele vligers hebben we voor dit vliegtuig?
		*/
		function PotentieelVliegers($vliegtuigID, $datum = null)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers(%s,%s)", $vliegtuigID, $datum));

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

			$query = "SELECT LID_ID, VLIEGER FROM `aanwezig_leden_view`" . $where . $condition;
			
			parent::DbOpvraag($query, $query_params);
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan A = %d", parent::NumRows()));
			
			if (parent::NumRows() > 0)					// We hebben potentiele vliegers,
				return parent::DbData();

			// We hebben geen potentiele vliegers, dan plan B
			if ($rvObj['CLUBKIST'] == 1)
			{
				// Alle leden die vandaag aanwezig zijn, 601 = 'Erelid', 602 = 'Lid', 603 = 'Jeugdlid', 606	= 'Donateur', 608 = '5-rittenkaarthouder', 611 = 'Cursist'

				$query = sprintf("SELECT LID_ID, VLIEGER FROM `aanwezig_leden_view` %s AND LIDTYPE_ID IN (601, 602, 603, 606, 608, 611)", $where);
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
							'VLIEGER' => $vlucht['VLIEGERNAAM_LID']
						);

						if (!in_array($vlieger, $retVal)) 
							array_push($retVal, $vlieger);
					}
					return $retVal;
				}
			}

			// Jeetje nog niets, dan maar plan C, alle aanwezigen
			$query = sprintf("SELECT LID_ID, VLIEGER FROM `aanwezig_leden_view` %s", $where);														
			parent::DbOpvraag($query, array( ($datum == null) ? date("Y-m-d") : $datum ));
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan C = %d ", parent::NumRows()));

			if (parent::NumRows() > 0)				// We hebben potentiele vliegers,
				return parent::DbData();		

			// Als dan niets lukt, dan maar iedereen. Laten we het plan D noemen				
			$params = array();	
			$params["VELDEN"] = "ID,NAAM";	

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
					throw new Exception($e);
			}

			$ll = MaakObject('Leden');	
			$llijst = $ll->GetObjects($params);
			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden.PotentieelVliegers Plan D = %d", $llijst['totaal']));
				
			$retVal = array();
			foreach ($llijst['dataset'] as $lid)
			{
				$lid = array (
					'LID_ID' => $lid['ID'],
					'VLIEGER' => $lid['NAAM']
				);

				array_push($retVal, $lid);
			}
			return $retVal;
		}
	}
?>