<?php
	class AanwezigVliegtuigen extends StartAdmin
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
				
			parent::DbUitvoeren("DROP VIEW IF EXISTS aanwezig_vliegtuigen_view");
			$query =  sprintf("CREATE VIEW `aanwezig_vliegtuigen_view` AS
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
					CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`
				FROM
					`%s` `av`
					LEFT JOIN `ref_vliegtuigen` `v` ON (`av`.`VLIEGTUIG_ID` = `v`.`ID`)
				WHERE
					`av`.`VERWIJDERD` = 0  
				ORDER BY 
					DATUM DESC, ID;", $this->dbTable);				
			
			parent::DbUitvoeren($query);
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
			$functie = "AanwezigVliegtuigen.GetObjects";
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
							$where .= " AND (REG_CALL LIKE ?) ";

							$s = "%" . trim($value) . "%";
							array_push($query_params, $s);

							Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));
							break;
						}
					case "IN" : 
						{
							isCSV($value, "IN");
							$where .= sprintf(" AND VLIEGTUIG_ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: IN='%s'", $functie, $value));
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

			// Als er geen datum is meegegeven dan alleen vandaag
			if ((strpos($where, 'DATUM') === false) && (strpos($where, 'ID') === false)) {
				$where .= sprintf (" AND DATUM = '%s'", date("Y-m-d"));
			}			
				
			$query = "
				SELECT 
					%s
				FROM
					`aanwezig_vliegtuigen_view`" . $where . $orderby;
			
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
		function VerwijderObject($ID = null, $VLIEGTUIG_ID = null, $DATUM = null)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.VerwijderObject(%s, %s)", $ID, $DATUM));					
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($ID !== null)
			{
				isINT($ID, "ID");
			}
			else
			{
				if (($DATUM == null) || ($VLIEGTUIG_ID == null))
					throw new Exception("406;Geen ID en VLIEGTUIG_ID/DATUM in aanroep;");

				isINT($VLIEGTUIG_ID, "VLIEGTUIG_ID");
				isDATE($DATUM. "DATUM");	
			}
			
			if ($ID == null)
			{
				$vObj = $this->GetObject(null, $VLIEGTUIG_ID, $DATUM);
				$ID = $vObj["ID"];
			}
			
			parent::MarkeerAlsVerwijderd($ID);
			if (parent::NumRows() === 0)
				throw new Exception("404;Record niet gevonden;");			
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
				if ($AanwezigVliegtuigData['VLIEGTUIG_ID'] !== $db_record['VLIEGTUIG_ID'])
					throw new Exception("409;Vliegtuig ID kan niet gewijzigd worden;");
			}

			// Neem data over uit aanvraag
			$d = $this->RequestToRecord($AanwezigVliegtuigData);            
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
		Afmelden van een lid
		*/
		function Afmelden($AanmeldenVliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("AanwezigVliegtuigen.Afmelden(%s)", print_r($AanmeldenVliegtuigData, true)));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($AanmeldenVliegtuigData == null)
				throw new Exception("406;AanmeldenVliegtuigData data moet ingevuld zijn;");	

			if (!array_key_exists('VLIEGTUIG_ID', $AanmeldenVliegtuigData))
				throw new Exception("406;VLIEGTUIG_ID moet ingevuld zijn;");

			$vliegtuigID = isINT($AanmeldenVliegtuigData['VLIEGTUIG_ID'], "VLIEGTUIG_ID");
		   
			$datetime = new DateTime();
			$datetime->setTimeZone(new DateTimeZone('Europe/Amsterdam')); 

			if (array_key_exists('TIJDSTIP', $AanmeldenVliegtuigData))
				$datetime = isDATETIME($AanmeldenVliegtuigData['TIJDSTIP'], "TIJDSTIP");
	
			$db_record = null;
			try
			{
				$db_record = $this->GetObject(null, $vliegtuigID, $datetime->format('Y-m-d'), false);
			}
			catch (Exception $e) 
			{
				throw new Exception("409;Kan een vliegtuig alleen afmelden als het eerst aangemeld is;");
			}		

			// Neem data over uit aanvraag
			$db_record['VERTREK'] = $datetime->format('H:i:00');
			parent::DbAanpassen($db_record['ID'], $db_record);	

			Debug(__FILE__, __LINE__, sprintf("AanwezigLeden aangepast id=%s", $db_record['ID']));		
			return  $this->GetObject($db_record['ID']);
		}		
	}
?>