<?php
	class Vliegtuigen extends Helios
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "ref_vliegtuigen";
			$this->dbView = "vliegtuigen_view";
			$this->Naam = "Vliegtuigen";
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
					`REGISTRATIE` varchar(8) NOT NULL,
					`CALLSIGN` varchar(6) DEFAULT NULL,
					`ZITPLAATSEN` tinyint UNSIGNED NOT NULL DEFAULT '1',
					`CLUBKIST` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`FLARMCODE` varchar(6) DEFAULT NULL,
					`TYPE_ID` mediumint UNSIGNED DEFAULT NULL,
					`TMG` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`ZELFSTART` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`SLEEPKIST` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`VOLGORDE` tinyint UNSIGNED DEFAULT NULL,
					`INZETBAAR` tinyint UNSIGNED NOT NULL DEFAULT '1',
					`OPMERKINGEN` text DEFAULT NULL,
					`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
					CONSTRAINT ID_PK PRIMARY KEY (ID),	
						INDEX (`REGISTRATIE`), 
						INDEX (`CALLSIGN`), 
						INDEX (`CLUBKIST`), 
						INDEX (`TYPE_ID`), 
						INDEX (`VOLGORDE`), 
						INDEX (`VERWIJDERD`),
						
					FOREIGN KEY (TYPE_ID) REFERENCES ref_types(ID)
				)", $this->dbTable);
			parent::DbUitvoeren($query);
			
			if (isset($FillData))
			{
				$query = sprintf("
					INSERT INTO 
						`%s` 
							(`ID`, 
							`REGISTRATIE`, 
							`CALLSIGN`, 
							`ZITPLAATSEN`, 
							`CLUBKIST`, 
							`FLARMCODE`, 
							`TYPE_ID`, 
							`TMG`, 
							`ZELFSTART`, 
							`SLEEPKIST`, 
							`VOLGORDE`, 
							`VERWIJDERD`) 
						VALUES
							(199, 'PH-6020', 	'P16', 	2, 0, NULL, 	NULL, 0, 0, 0, 0, 0),
							(200, 'PH-1529', 	'E12', 	2, 1, '485069', 405,  0, 0, 0, 9, 0),
							(201, 'PH-1623', 	'E8', 	1, 1, NULL, 	404,  0, 0, 0, 8, 0),
							(208, 'D-KARC', 	'BRC', 	2, 0, NULL, 	NULL, 0, 0, 0, 0, 0),
							(209, 'D-KDIX', 	'IIX', 	2, 0, NULL, 	NULL, 0, 0, 0, 0, 0),
							(210, 'PH-614', 	'WM', 	1, 0, 'DDE299', NULL, 0, 0, 0, 0, 0),
							(211, 'D-KLUU', 	'7U', 	1, 0, 'DDBBBE', NULL, 0, 0, 0, 0, 0),
							(212, 'PH-KPZP', 	'ZP', 	2, 0, NULL, 	NULL, 0, 0, 0, 0, 0),
							(213, 'D-KRHT', 	'HT', 	1, 0, 'DD1534', NULL, 0, 1, 0, 0, 0),
							(214, 'D-KLLA', 	'LL', 	2, 0, '3EC9F2', NULL, 0, 0, 0, 0, 0),
							(215, 'D-KTXO', 	'X0', 	1, 0, NULL, 	NULL, 0, 0, 0, 0, 1),
							(216, 'PH-YLB', 	'YLB', 	2, 0, NULL, 	NULL, 0, 0, 0, 0, 0),
							(217, 'D-KNWW', 	'KW', 	1, 0, 'DDBC8A', NULL, 0, 0, 0, 0, 0),
							(218, 'PH-ELT', 	'ELT', 	2, 0, '484728', NULL, 0, 0, 1, 0, 0);", $this->dbTable);
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
					v.*,
					CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`,
					`t`.`OMSCHRIJVING` AS `VLIEGTUIGTYPE`
				FROM
					`%s` `v`    
					LEFT JOIN `ref_types` `t` ON (`v`.`TYPE_ID` = `t`.`ID`)
				WHERE
					`v`.`VERWIJDERD` = %s
				ORDER BY 
					CLUBKIST DESC, VOLGORDE, REGISTRATIE;";	
							
			parent::DbUitvoeren("DROP VIEW IF EXISTS vliegtuigen_view");							
			parent::DbUitvoeren(sprintf($query, "vliegtuigen_view", $this->dbTable, 0));

			parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_vliegtuigen_view");
			parent::DbUitvoeren(sprintf($query, "verwijderd_vliegtuigen_view", $this->dbTable, 1));
		}

		/*
		Haal een enkel record op uit de database
		*/
		function GetObject($ID)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.GetObject(%s)", $ID));	

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
		Haal een enkel record op uit de database
		*/
		function GetObjectByRegistratie($Registratie)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.GetObjectByRegistratie(%s)", $Registratie));	

			if ($Registratie == null)
				throw new Exception("406;Geen Registratie in aanroep;");

			$query = sprintf("
				SELECT
					*
				FROM
					%s
				WHERE
					REGISTRATIE like ? AND VERWIJDERD=0", $this->dbTable, $Registratie);

			$conditie = array($Registratie);

			parent::DbOpvraag($query, $conditie);
			$obj = parent::DbData();

			if ($obj == null)
			{
				throw new Exception("404;Record niet gevonden;");
			}

			$obj = $this->RecordToOutput($obj);
			return $obj[0];				
		}			
	
		/*
		Haal een dataset op met records als een array uit de database. 
		*/		
		function GetObjects($params)
		{
			$functie = "Vliegtuigen.GetObjects";
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
					case "SELECTIE" : 
						{
							$where .= " AND ((REGISTRATIE LIKE ?) ";
							$where .= "  OR (CALLSIGN LIKE ?) ";
							$where .= "  OR (FLARMCODE LIKE ?)) ";

							$s = "%" . trim($value) . "%";
							$query_params = array($s, $s, $s);

							Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $value));	
							break;
						}
					case "IN" : 
						{
							isCSV($value, "IN");
							$in = sprintf(" ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: IN='%s'", $functie, $value));
							break;
						}
					case "TYPES" : 
						{
							isCSV($value, "TYPES");
							$where .= sprintf(" AND TYPE_ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: TYPES='%s'", $functie, $value));
							break;
						}	
					case "ZITPLAATSEN" : 
						{
							$zitplaatsen = isINT($value, "ZITPLAATSEN");

							if (($zitplaatsen < 1) || ($zitplaatsen > 2))
								throw new Exception("405;ZITPLAATSEN moet een 1 of 2 zijn;");
							
							$where .= " AND ZITPLAATSEN=?";
							array_push($query_params, $zitplaatsen);
							
							Debug(__FILE__, __LINE__, sprintf("%s: ZITPLAATSEN='%s'", $functie, $zitplaatsen));
							break;
						}	
					case "CLUBKIST" : 
						{
							$clubkist = isBOOL($value, "CLUBKIST");
							$where .= " AND CLUBKIST=?";
							array_push($query_params, $clubkist);

							Debug(__FILE__, __LINE__, sprintf("%s: CLUBKIST='%s'", $functie, $clubkist));
							break;
						}		
					case "ZELFSTART" : 
						{
							$zelfstart = isBOOL($value, "ZELFSTART");
							$where .= " AND ZELFSTART=?";
							array_push($query_params, $zelfstart);

							Debug(__FILE__, __LINE__, sprintf("%s: ZELFSTART='%s'", $functie, $zelfstart));
							break;
						}			
					case "SLEEPKIST" : 
						{
							$sleepkist = isBOOL($value, "SLEEPKIST");
							$where .= " AND SLEEPKIST=?";
							array_push($query_params, $sleepkist);

							Debug(__FILE__, __LINE__, sprintf("%s: SLEEPKIST='%s'", $functie, $sleepkist));
							break;
						}		
					case "TMG" : 
						{
							$TMG = isBOOL($value, "TMG");
							$where .= " AND TMG=?";
							array_push($query_params, $TMG);

							Debug(__FILE__, __LINE__, sprintf("%s: TMG='%s'", $functie, $TMG));
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
				
			$query = "
				SELECT 
					%s
				FROM
					`####vliegtuigen_view`" . $where . $orderby;
			$query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : "") , $query);		
			
			$retVal = array();

			$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
			$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);
			$retVal['hash'] = dechex((str_replace(":", "", substr($retVal['laatste_aanpassing'], -8)) * 1000) + ($retVal['totaal'] * 1));
			Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s, HASH=%s", $retVal['totaal'], $retVal['laatste_aanpassing'], $retVal['hash']));	

			if ($retVal['hash'] == $hash)
				throw new Exception("704;Dataset ongewijzigd;");

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
		function VerwijderObject($id, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.VerwijderObject('%s', %s)", $id, (($verificatie === false) ? "False" :  $verificatie)));				
			if (!$this->heeftDataToegang(null, false))
				throw new Exception("401;Geen schrijfrechten;");

			if ($id == null)
				throw new Exception("406;Geen ID in aanroep;");
			
			isCSV($id, "ID");
			parent::MarkeerAlsVerwijderd($id, $verificatie);
		}		
		
		/*
		Herstel van een verwijderd record
		*/
		function HerstelObject($id)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.HerstelObject('%s')", $id));

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
		function AddObject($VliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.AddObject(%s)", print_r($VliegtuigData, true)));
			
			if ($VliegtuigData == null)
				throw new Exception("406;Vliegtuig data moet ingevuld zijn;");			

			if (array_key_exists('ID', $VliegtuigData))
			{
				$id = isINT($VliegtuigData['ID'], "ID");
				
				// ID is opgegeven, maar bestaat record?
				try 	// Als record niet bestaat, krijgen we een exception
				{	
					$this->GetObject($id);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
			}

			if (!array_key_exists('REGISTRATIE', $VliegtuigData))
				throw new Exception("406;REGISTRATIE is verplicht;");			
			
			if ($VliegtuigData['REGISTRATIE'] == null)	
				throw new Exception("406;REGISTRATIE is verplicht;");	
			
			try 	// Als record niet bestaat, krijgen we een exception
			{				
				$this->GetObjectByRegistratie($VliegtuigData['REGISTRATIE']); 
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Vliegtuig met registratie %s bestaat al;", $VliegtuigData['REGISTRATIE']));
										
			// Neem data over uit aanvraag
			$v = $this->RequestToRecord($VliegtuigData);
						
			$id = parent::DbToevoegen($v);
			Debug(__FILE__, __LINE__, sprintf("Vliegtuig toegevoegd id=%d", $id));

			return $this->GetObject($id);
		}

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function UpdateObject($VliegtuigData)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.UpdateObject(%s)", print_r($VliegtuigData, true)));
			
			if ($VliegtuigData == null)
				throw new Exception("406;Vliegtuig data moet ingevuld zijn;");			

			if (!array_key_exists('ID', $VliegtuigData))
				throw new Exception("406;ID moet ingevuld zijn;");

			$id = isINT($VliegtuigData['ID'], "ID");

            // Voorkom dat datum meerdere keren voorkomt in de tabel
			if (array_key_exists('REGISTRATIE', $VliegtuigData))
			{
				if ($VliegtuigData['REGISTRATIE'] == null)	
					throw new Exception("406;REGISTRATIE is verplicht;");	

				try 	// Als record niet bestaat, krijgen we een exception
				{
					$vdb = $this->GetObjectByRegistratie($VliegtuigData['REGISTRATIE']);
				}
				catch (Exception $e) {}

				if (parent::NumRows() > 0)
				{
					if ($id != $vdb['ID'])
						throw new Exception(sprintf("409;Vliegtuig met registratie %s bestaat al;", $VliegtuigData['REGISTRATIE']));
				}					
			}

			$vbd = $this->getObject($id);
			if ($vbd['CLUBKIST']) {

				// alleen beheerders mogen club vliegtuigen aanpassen
				if (!$this->heeftDataToegang(null, false))
					throw new Exception("401;Geen schrijfrechten;");
			}

			// Neem data over uit aanvraag
			$v = $this->RequestToRecord($VliegtuigData);

			parent::DbAanpassen($id, $v);
			if (parent::NumRows() === 0)
				throw new Exception("404;Record niet gevonden;");				
			
			return $this->GetObject($id);
		}

		/*
		Copieer data van request naar velden van het record 
		*/
		function RequestToRecord($input)
		{
			Debug(__FILE__, __LINE__, sprintf("Vliegtuigen.RequestToRecord(%s)", print_r($input, true)));
			$record = array();

			$field = 'ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

			$field = 'REGISTRATIE';
			if (array_key_exists($field, $input))
			{
				if (preg_match("/^[A-z][A-z]*-[A-z0-9][A-z0-9]*$/", $input['REGISTRATIE']) != 1) 
					throw new Exception("405;REGISTRATIE is onjuist;");
				
					$record['REGISTRATIE'] = $input['REGISTRATIE']; 
			}

			$field = 'ZITPLAATSEN';
			if (array_key_exists($field, $input))
			{
				$zitplaatsen = isINT($input[$field], $field);

				if (($zitplaatsen < 1) || ($zitplaatsen > 2))
					throw new Exception("405;ZITPLAATSEN moet 1 of 2 zijn;");	

				$record[$field] = $zitplaatsen;
			}

			$field = 'ZELFSTART';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'SLEEPKIST';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);
				
			$field = 'CLUBKIST';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'TMG';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'TYPE_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, "Types");

			$field = 'VOLGORDE';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true);

			$field = 'INZETBAAR';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);	

			if (array_key_exists('FLARMCODE', $input))
				$record['FLARMCODE'] = $input['FLARMCODE'];

			if (array_key_exists('CALLSIGN', $input))
				$record['CALLSIGN'] = $input['CALLSIGN'];

			if (array_key_exists('OPMERKINGEN', $input))
				$record['OPMERKINGEN'] = $input['OPMERKINGEN'];

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

			if (isset($record['TYPE_ID']))
				$retVal['TYPE_ID']  = $record['TYPE_ID'] * 1;
			
			if (isset($record['ZITPLAATSEN']))
				$retVal['ZITPLAATSEN']  = $record['ZITPLAATSEN'] * 1;	

			if (isset($record['VOLGORDE']))
				$retVal['VOLGORDE']  = $record['VOLGORDE'] * 1;		
				
				
			// booleans	
			if (isset($record['TMG']))
				$retVal['TMG']  = $record['TMG'] == "1" ? true : false;

			if (isset($record['ZELFSTART']))
				$retVal['ZELFSTART']  = $record['ZELFSTART'] == "1" ? true : false;

			if (isset($record['CLUBKIST']))
				$retVal['CLUBKIST']  = $record['CLUBKIST'] == "1" ? true : false;

			if (isset($record['SLEEPKIST']))
				$retVal['SLEEPKIST']  = $record['SLEEPKIST'] == "1" ? true : false;

			if (isset($record['INZETBAAR']))
				$retVal['INZETBAAR']  = $record['INZETBAAR'] == "1" ? true : false;	

			if (isset($record['VERWIJDERD']))
				$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

			return $retVal;
		}
	}
