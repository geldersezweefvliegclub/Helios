<?php
	class Rooster extends StartAdmin
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "oper_rooster";
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
					`OCHTEND_DDI_ID` mediumint UNSIGNED DEFAULT NULL,
					`OCHTEND_INSTRUCTEUR_ID` mediumint UNSIGNED DEFAULT NULL,
					`OCHTEND_LIERIST_ID` mediumint UNSIGNED DEFAULT NULL,
					`OCHTEND_HULPLIERIST_ID` mediumint UNSIGNED DEFAULT NULL,
					`OCHTEND_STARTLEIDER_ID` mediumint UNSIGNED DEFAULT NULL,
					`MIDDAG_DDI_ID` mediumint UNSIGNED DEFAULT NULL,
					`MIDDAG_INSTRUCTEUR_ID` mediumint UNSIGNED DEFAULT NULL,
					`MIDDAG_LIERIST_ID` mediumint UNSIGNED DEFAULT NULL,
					`MIDDAG_HULPLIERIST_ID` mediumint UNSIGNED DEFAULT NULL,
					`MIDDAG_STARTLEIDER_ID` mediumint UNSIGNED DEFAULT NULL,
					`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					 
					CONSTRAINT ID_PK PRIMARY KEY (ID),
						INDEX (`DATUM`), 
						INDEX (`DDWV`), 
						INDEX (`VERWIJDERD`),

					FOREIGN KEY (OCHTEND_DDI_ID) REFERENCES ref_leden(ID),		
					FOREIGN KEY (OCHTEND_INSTRUCTEUR_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (OCHTEND_LIERIST_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (OCHTEND_HULPLIERIST_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (OCHTEND_STARTLEIDER_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (MIDDAG_DDI_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (MIDDAG_INSTRUCTEUR_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (MIDDAG_LIERIST_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (MIDDAG_HULPLIERIST_ID) REFERENCES ref_leden(ID),	
					FOREIGN KEY (MIDDAG_STARTLEIDER_ID) REFERENCES ref_leden(ID)	
				)", $this->dbTable);
			parent::DbUitvoeren($query);

			if (isset($FillData))
			{
				$inject = array(
					"1, '####-05-01', 1, 0, 10001, 10265, 10408, NULL, 10001, 10115, 10001, 10804, NULL, NULL",
					"2, '####-05-02', 1, 0, 10115, 10470, 10804, NULL, NULL,  10115, 10408, NULL,  NULL, 10001",
					"3, '####-05-03', 1, 0, 10470, 10001, 10804, NULL, NULL,  10470, NULL,  NULL,  NULL, NULL",
					"4, '####-05-04', 1, 0, 10001, 10265, NULL,  NULL, NULL,  10470, NULL,  NULL,  NULL, NULL",
					"5, '####-05-05', 1, 0, NULL,  10470, 10408, NULL, 10408, 10470, NULL,  10858, NULL, 10408");

				$inject = str_replace("####", strval(date("Y")), $inject);		// rooster in dit jaar
				$i = 0;    

				foreach ($inject as $record)
				{    
									
					$query = sprintf("
							INSERT INTO `%s` (
								`ID`, 
								`DATUM`, 
								`DDWV`, 
								`CLUB_BEDRIJF`, 
								`OCHTEND_DDI_ID`, 
								`OCHTEND_INSTRUCTEUR_ID`, 
								`OCHTEND_LIERIST_ID`, 
								`OCHTEND_HULPLIERIST_ID`, 
								`OCHTEND_STARTLEIDER_ID`, 
								`MIDDAG_DDI_ID`, 
								`MIDDAG_INSTRUCTEUR_ID`, 
								`MIDDAG_LIERIST_ID`, 
								`MIDDAG_HULPLIERIST_ID`, 
								`MIDDAG_STARTLEIDER_ID`) 
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
				
			parent::DbUitvoeren("DROP VIEW IF EXISTS rooster_view");
			$query =  sprintf("CREATE VIEW `rooster_view` AS
				SELECT 
					rooster.*,
					ref_leden.NAAM AS OCHTEND_DDI,
					ref_leden_1.NAAM AS OCHTEND_INSTRUCTEUR,
					ref_leden_2.NAAM AS OCHTEND_HULPLIERIST,
					ref_leden_3.NAAM AS OCHTEND_STARTLEIDER,
					ref_leden_4.NAAM AS MIDDAG_DDI,
					ref_leden_5.NAAM AS MIDDAG_INSTRUCTEUR,
					ref_leden_6.NAAM AS MIDDAG_LIERIST,
					ref_leden_7.NAAM AS OCHTEND_LIERIST,
					ref_leden_8.NAAM AS MIDDAG_HULPLIERIST,
					ref_leden_9.NAAM AS MIDDAG_STARTLEIDER
				FROM
					`%s` AS `rooster`
					LEFT JOIN ref_leden         			ON (rooster.OCHTEND_DDI_ID = ref_leden.ID)
					LEFT JOIN ref_leden AS ref_leden_1      ON (rooster.OCHTEND_INSTRUCTEUR_ID = ref_leden_1.ID)
					LEFT JOIN ref_leden AS ref_leden_2      ON (rooster.OCHTEND_HULPLIERIST_ID = ref_leden_2.ID)
					LEFT JOIN ref_leden AS ref_leden_3      ON (rooster.OCHTEND_STARTLEIDER_ID = ref_leden_3.ID)
					LEFT JOIN ref_leden AS ref_leden_4      ON (rooster.MIDDAG_DDI_ID = ref_leden_4.ID)
					LEFT JOIN ref_leden AS ref_leden_5      ON (rooster.MIDDAG_INSTRUCTEUR_ID = ref_leden_5.ID)
					LEFT JOIN ref_leden AS ref_leden_6      ON (rooster.MIDDAG_LIERIST_ID = ref_leden_6.ID)
					LEFT JOIN ref_leden AS ref_leden_7      ON (rooster.OCHTEND_LIERIST_ID = ref_leden_7.ID)
					LEFT JOIN ref_leden AS ref_leden_8      ON (rooster.MIDDAG_HULPLIERIST_ID = ref_leden_8.ID)
					LEFT JOIN ref_leden AS ref_leden_9      ON (rooster.MIDDAG_STARTLEIDER_ID = ref_leden_9.ID)				
				WHERE
					`rooster`.`VERWIJDERD` = 0  
				ORDER BY 
					DATUM DESC;", $this->dbTable);	
						
			parent::DbUitvoeren($query);
		}

		/*
		Haal een enkel record op uit de database
		*/
		function GetObject($ID = null, $DATUM = null, $heeftVerwijderd = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Rooster.GetObject(%s,%s,%s)", $ID, $DATUM, $heeftVerwijderd));	

			if (($ID == null) && ($DATUM == null))
				throw new Exception("406;Geen ID en DATUM in aanroep;");

			if (($ID != null) && (isINT($ID) === false))
				throw new Exception("405;ID moet een integer zijn;");

			if (($DATUM != null) && (isDATE($DATUM) === false))
				throw new Exception("405;DATUM heeft onjuist formaat;");

			$conditie = array();
			if ($ID != null)
				$conditie['ID'] = isINT($ID, "ID");
			else
				$conditie['DATUM'] = isDATE($DATUM, "DATUM");

			if ($heeftVerwijderd == false)
				$conditie['VERWIJDERD'] = 0;		// Dus geen verwijderd record

			$obj = parent::GetSingleObject($conditie);
			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");
			
			return $obj;	
		}
	
		/*
		Haal een dataset op met records als een array uit de database. 
		*/		
		function GetObjects($params)
		{
			$functie = "Rooster.GetObjects";
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
					`rooster_view`" . $where . $orderby;
			
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
		function VerwijderObject($ID = null, $DATUM = null)
		{
			Debug(__FILE__, __LINE__, sprintf("Rooster.VerwijderObject(%s, %s)", $ID, $DATUM));								
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if (($ID == null) && ($DATUM == null))
				throw new Exception("406;Geen ID en DATUM in aanroep;");
			
			if ($ID != null)
			{
				isINT($ID, "ID");
			}
			else
			{
				isDATE($DATUM, "DATUM");
				$vObj = $this->GetObject(null, $DATUM);
				$ID = $vObj["ID"];
			}
			
			parent::MarkeerAlsVerwijderd($ID);
			if (parent::NumRows() === 0)
				throw new Exception("404;Record niet gevonden;");				
		}		


		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function AddObject($RoosterData)
		{
			Debug(__FILE__, __LINE__, sprintf("Rooster.AddObject(%s)", print_r($RoosterData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($RoosterData == null)
				throw new Exception("406;Daginfo data moet ingevuld zijn;");	

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

			if (!array_key_exists('CLUB_BEDRIJF', $RoosterData))
				throw new Exception("406;Club bedrijf is verplicht;");

			if (!array_key_exists('DDWV', $RoosterData))
				throw new Exception("406;DDWV is verplicht;");				

			$roosterDatum = isDATE($RoosterData['DATUM'], "DATUM");

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
			Debug(__FILE__, __LINE__, sprintf("Rooster.UpdateObject(%s)", print_r($RoosterData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($RoosterData == null)
				throw new Exception("406;Daginfo data moet ingevuld zijn;");	

			if (!array_key_exists('ID', $RoosterData))
				throw new Exception("406;ID moet ingevuld zijn;");

			$id = isINT($RoosterData['ID'], "ID");

            // Voorkom dat datum meerdere keren voorkomt in de tabel
			if (array_key_exists('DATUM', $RoosterData))
			{
				$roosterDatum = isDATE($DaginfoData['DATUM'], "DATUM");

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

			$field = 'OCHTEND_DDI_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');

			$field = 'OCHTEND_INSTRUCTEUR_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
			
			$field = 'OCHTEND_STARTLEIDER_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
		
			$field = 'OCHTEND_LIERIST_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');

			$field = 'OCHTEND_HULPLIERIST_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
			
			$field = 'MIDDAG_DDI_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
									
			$field = 'MIDDAG_INSTRUCTEUR_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
								
			$field = 'MIDDAG_STARTLEIDER_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
										
			$field = 'MIDDAG_LIERIST_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');

				$field = 'MIDDAG_HULPLIERIST_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
															
			$field = 'DDWV';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'CLUB_BEDRIJF';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			return $record;
		}
	}
?>