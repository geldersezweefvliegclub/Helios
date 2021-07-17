<?php
	class Progressie extends Helios
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "oper_progressie";
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
				$i++;
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
			if (($l->isBeheerder() == false) && ($l->isInstructeur() == false))
				$where .= sprintf(" AND (LID_ID = '%d') ", $l->getUserFromSession());

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
			$functie = "Progressie.ProgressieKaart";
			Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		

			if (!array_key_exists('LID_ID', $params))
				throw new Exception("406;LID_ID ontbreekt in aanroep;");

			$alleenLaatsteAanpassing = false;
			$hash = null;
			$lid_id = -1;
			$velden = "							
				`competenties_view`.*,

				`p`.`ID` AS `PROGRESSIE_ID`, 
				`p`.`INGEVOERD`, 
				`p`.`OPMERKINGEN`, 
				`p`.`INSTRUCTEUR_NAAM`, 
				`p`.`LAATSTE_AANPASSING` AS LAATSTE_AANPASSING";

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
					LEERFASE_ID, VOLGORDE, competenties_view.ID";

			$retVal = array();

			$retVal['totaal'] = $this->Count($query);		// total amount of records in the database
			$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, null, "`p`.`LAATSTE_AANPASSING`");
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
				$rquery = sprintf($query, $velden);
				parent::DbOpvraag($rquery);
				$retVal['dataset'] = parent::DbData();

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

			$t = MaakObject('Types');
			$this->hoofdGroepen = $t->GetObjects(array("GROEP" => 10));
			$this->progressieKaart = $this->ProgressieKaart($params);		// Hier halen we de data op
			return $this->bouwBoom();
		}

		/*
		Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
		Het veld VERWIJDERD wordt op "1" gezet.
		*/
		function VerwijderObject($id = null, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Progressie.VerwijderObject('%s', %s)", $id, (($verificatie === false) ? "False" :  $verificatie)));
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
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
			Debug(__FILE__, __LINE__, sprintf("Progressie.HerstelObject('%s')", $id));

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
		function AddObject($ProgressieData)
		{
			Debug(__FILE__, __LINE__, sprintf("Progressie.AddObject(%s)", print_r($ProgressieData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
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
			Debug(__FILE__, __LINE__, sprintf("Progressie.SaveObject(%s)", print_r($ProgressieData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
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

			if (isset($record['COMPETENTIE_ID']))
				$retVal['COMPETENTIE_ID']  = $record['COMPETENTIE_ID'] * 1;		

			if (isset($record['INSTRUCTEUR_ID']))
				$retVal['INSTRUCTEUR_ID']  = $record['INSTRUCTEUR_ID'] * 1;		

			if (isset($record['LINK_ID']))
				$retVal['LINK_ID']  = $record['LINK_ID'] * 1;	

			// booleans	
			if (isset($record['VERWIJDERD']))
				$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

			return $retVal;
		}

		// Allerhoogste niveau om de progressie kaart te tonen
		// De hoofgroepen komen uit de types tabel
		function bouwBoom()
		{
			$functie = "Progressie.bouwBoom";
			Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($this->hoofdGroepen, true)));	
			Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($this->progressieKaart, true)));	

			$competentieBoom = array();

			foreach ($this->hoofdGroepen['dataset'] as $leerfase)
			{
				$children = $this->bouwStam($leerfase["ID"]);
				$onderwerp = ($leerfase["CODE"] != null) ? sprintf("%s: %s", $leerfase['CODE'], $leerfase['OMSCHRIJVING']) : $leerfase['OMSCHRIJVING'];

				$c = new EnkeleCompetentie(
					$leerfase["ID"],                // leerfase ID
					null,                           // competentie ID
					null,       					// blok ID
					null,                           // blok
					$onderwerp,                     // onderwerp
					null,                           // documentatie
					$children,                      // child data
					null,                           // progressie ID
					null,                           // Datum behaald
					$this->isBehaald(null, $children),    // is onderliggende competentie behaald
					null);                          // afgetekend door
				
				array_push($competentieBoom, $c);
			}

			return $competentieBoom;
		}

		function bouwStam($topID)
		{
			$pKaart = array(); 

			foreach ($this->progressieKaart['dataset'] as $competentie)
			{
				if (($competentie["LEERFASE_ID"] == $topID) && ($competentie["BLOK_ID"] == null))  
				{					
					$children = $this->bouwTakken($competentie["ID"]);

					$c = new EnkeleCompetentie(
						0,                              // leerfase ID
						$competentie["ID"],             // competentie ID
						$competentie["BLOK_ID"],        // blok ID
						$competentie["BLOK"],           // blok
						$competentie["ONDERWERP"],      // onderwerp
						$competentie["DOCUMENTATIE"],   // documentatie
						$children,                      // child data
						$competentie["PROGRESSIE_ID"],  // progressie ID
						$competentie["INGEVOERD"],      // Datum behaald
						$this->isBehaald($competentie["INGEVOERD"], $children),    // is onderliggende competentie behaald
						$competentie["INSTRUCTEUR_NAAM"],  // afgetekend door
						$competentie["OPMERKINGEN"]);   // Opmerkingen bij het behalen
					
					array_push($pKaart, $c);
				}    
			}

			if (count($pKaart) == 0)
				return null;

			return $pKaart;
		}

		
		// Nu alle onderliggende niveaus. Wordt via een recursieve aanroep opgebouwd.
		function bouwTakken($ouderID)
		{  
			$pKaart = array();  

			foreach ($this->progressieKaart['dataset'] as $competentie)
			{   
				if ($competentie["BLOK_ID"] == $ouderID)
				{
					$children = $this->bouwTakken($competentie["ID"]);

					$c = new EnkeleCompetentie(
						0,                              // leerfase ID
						$competentie["ID"],             // competentie ID
						$competentie["BLOK_ID"],        // blok ID
						$competentie["BLOK"],           // blok
						$competentie["ONDERWERP"],      // onderwerp
						$competentie["DOCUMENTATIE"],   // documentatie
						$children,                      // child data
						$competentie["PROGRESSIE_ID"],  // progressie ID
						$competentie["INGEVOERD"],      // Datum behaald
						$this->isBehaald($competentie["INGEVOERD"], $children),    // is onderliggende competentie behaald
						$competentie["INSTRUCTEUR_NAAM"],  // afgetekend door
						$competentie["OPMERKINGEN"]);   // Opmerkingen bij het behalen
					
					array_push($pKaart, $c);
				}    
			}

			if (count($pKaart) == 0)
				return null;

			return $pKaart;
		}  

		// Geeft 0/1/2 terug als alle compententies behaald zijn, of misschien maar een gedeelte
		function isBehaald($datumBehaald, $kaarten)
		{
			if ($kaarten == null)
			{
				if ($datumBehaald != null)
					return 2;           // behaald want datum is ingevoerd
				else
					return 0;           // 0 = nee
			}

			$retValue = -1;             // -1 nog niet bepaald (is nooit een return waarde)

			for ($i=0 ; $i < count($kaarten) ; $i++)
			{
				switch ($kaarten[$i]->IS_BEHAALD)
				{
					case 0: // 0 = niet behaald
						{
							if ($retValue == 2)
								return 1;           // Vorige wel gehaald, deze niet = gedeeltelijk gehaald
							
							$retValue = 0;
							break;
						}
					case 1: // 1 = gedeeltelijk, onderliggende comptentie is gedeeltelijk gehaald
						{
							return 1; 
						}
					case 2: // 2= gehaald
						{
							if ($retValue == 0)
								return 1;           // Vorige niet gehaald, deze wel = gedeeltelijk gehaald
							
							$retValue = 2;
							break;
						}
				}
			}
			return $retValue;
		}		
	}

	class EnkeleCompetentie 
	{
		public $ID;
		public $LEERFASE_ID;
		public $COMPETENTIE_ID;
		public $BLOK_ID;
		public $BLOK;
		public $ONDERWERP;
		public $DOCUMENTATIE;
		public $OPMERKINGEN;
	
		public $PROGRESSIE_ID;
		public $IS_BEHAALD;              // 0 = nee, 1 = gedeeltelijk, 2 = ja
		public $INGEVOERD;
		public $INSTRUCTEUR_NAAM;
	
		public $children;
	
		public function __construct(
				$id,
				$leerfaseID,
				$competentieID,
				$blokID,
				$blok,
				$onderwerp,
				$documentatie, 
				$childData,
	
				$progressieID = null,
				$datumBehaald = null,
				$isBehaald = 0,
				$afgetekendDoor  = null,
				$opmerkingen = null
			)
		{
			$this->LEERFASE_ID = $leerfaseID;
			$this->ID = $competentieID;
			$this->BLOK_ID = $blokID;
			$this->BLOK = $blok;
			$this->ONDERWERP = $onderwerp;
			$this->DOCUMENTATIE = $documentatie;
			$this->children = $childData;
	
			$this->PROGRESSIE_ID  = $progressieID;
			$this->INGEVOERD = $datumBehaald;
			$this->IS_BEHAALD = $isBehaald;
			$this->INSTRUCTEUR_NAAM = $afgetekendDoor;
			$this->OPMERKINGEN = $opmerkingen;
		}
	}