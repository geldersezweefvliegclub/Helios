<?php
	class Diensten extends Helios
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "oper_diensten";
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
                    `DATUM` DATE NOT NULL,
                    `ROOSTER_ID` mediumint  UNSIGNED NOT NULL,
                    `LID_ID` mediumint UNSIGNED NOT NULL,
                    `TYPE_DIENST_ID` mediumint UNSIGNED DEFAULT NULL,
                    `INGEVOERD_DOOR_ID` mediumint UNSIGNED DEFAULT NULL,
					
					`AANWEZIG` tinyint UNSIGNED DEFAULT NULL,
					`AFWEZIG` tinyint UNSIGNED DEFAULT NULL,

					`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
					CONSTRAINT ID_PK PRIMARY KEY (ID),	
						INDEX (`LID_ID`), 
						INDEX (`ROOSTER_ID`), 
						INDEX (`DATUM`), 
						INDEX (`TYPE_DIENST_ID`), 
						INDEX (`VERWIJDERD`),
						
					FOREIGN KEY (TYPE_DIENST_ID) REFERENCES ref_types(ID),
                    FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
                    FOREIGN KEY (INGEVOERD_DOOR_ID) REFERENCES ref_leden(ID)

				)", $this->dbTable);
			parent::DbUitvoeren($query);
			
			if (isset($FillData))
			{
                $inject = array(
					"1, '####-05-01', 1800, 10001,  1",
                    "2, '####-05-01', 1801, 10265,  1",
                    "3, '####-05-01', 1802, 10408,  1",
                    "5, '####-05-01', 1804, 10001,  1",
                    "6, '####-05-01', 1805, 10115,  1",
                    "7, '####-05-01', 1806, 10001,  1",
                    "8, '####-05-01', 1807, 10804,  1",

                    "9, '####-05-02', 1800, 10115,  2",
                    "10, '####-05-02', 1801, 10470,  2",
                    "11, '####-05-02', 1802, 10804,  2",
                    "14, '####-05-02', 1805, 10115,  2",
                    "15, '####-05-02', 1806, 10408,  2",
                    "16, '####-05-02', 1807, 10001,  2",

					"17, '####-05-03', 1800, 10470,  3",
                    "18, '####-05-03', 1801, 10001,  3",
                    "19, '####-05-03', 1802, 10804,  3",
                    "21, '####-05-03', 1804, 10470,  3",
                    "22, '####-05-03', 1805, 10115,  3",

                    
					"25, '####-05-04', 1800, 10001,  4",
                    "26, '####-05-04', 1801, 10265,  4",
                    "30, '####-05-04', 1805, 10115,  4",
                    "31, '####-05-04', 1806, 10470,  4",

                    "33, '####-05-05', 1800, 10470,  5",
                    "34, '####-05-05', 1801, 10408,  5",

                    "37, '####-05-05', 1804, 10001,  5",
                    "38, '####-05-05', 1805, 10115,  5",
                    "39, '####-05-05', 1806, 10858,  5",
                    "40, '####-05-05', 1807, 10408,  5");

                
                
                    $inject = str_replace("####", strval(date("Y")), $inject);		// rooster in dit jaar
                    $i = 0;    
    
                    foreach ($inject as $record)
                    {    
                        $query = sprintf("
                            INSERT INTO 
                                `%s` 
                                    (`ID`, 
                                    `DATUM`, 
                                    `TYPE_DIENST_ID`, 
                                    `LID_ID`, 
                                    `ROOSTER_ID`) 
                                VALUES
                                    (%s)", $this->dbTable, $record);
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
					d.*,
                    `l`.`NAAM` AS `NAAM`,
                    `i`.`NAAM` AS `INGEVOERD_DOOR`,
					`t`.`OMSCHRIJVING` AS `TYPE_DIENST`
				FROM
					`%s` `d`    
					LEFT JOIN `ref_types` `t` ON (`d`.`TYPE_DIENST_ID` = `t`.`ID`)
                    LEFT JOIN `ref_leden` `l` ON (`d`.`LID_ID` = `l`.`ID`)
                    LEFT JOIN `ref_leden` `i` ON (`d`.`INGEVOERD_DOOR_ID` = `i`.`ID`)
				WHERE
					`d`.`VERWIJDERD` = %s
				ORDER BY 
					DATUM DESC, SORTEER_VOLGORDE;";	
							
			parent::DbUitvoeren("DROP VIEW IF EXISTS diensten_view");							
			parent::DbUitvoeren(sprintf($query, "diensten_view", $this->dbTable, 0));

			parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_diensten_view");
			parent::DbUitvoeren(sprintf($query, "verwijderd_diensten_view", $this->dbTable, 1));
		}

		/*
		Haal een enkel record op uit de database
		*/
		function GetObject($ID)
		{
			Debug(__FILE__, __LINE__, sprintf("Diensten.GetObject(%s)", $ID));	

			if ($ID == null)
				throw new Exception("406;Geen ID in aanroep;");

			$conditie = array();
			$conditie['ID'] = isINT($ID, "TYPE");

			$obj = parent::GetSingleObject($conditie);
			Debug(__FILE__, __LINE__, print_r($obj, true));

			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");

			$obj = $this->RecordToOutput($obj);
			return $obj;
		}

        /*
		Haal een enkel record op uit de database op basis van datum en type dienst 
		*/
		function GetObjectByDatumDienst($Datum, $TypeDienst)
		{
			Debug(__FILE__, __LINE__, sprintf("Diensten.GetObjectByDatumType(%s, %s)", $Datum, $TypeDienst));	

			if ($Datum == null)
				throw new Exception("406;Geen datum in aanroep;");

            if ($TypeDienst == null)
				throw new Exception("406;Geen TypeDienst in aanroep;");                

			$conditie = array();
            isDATE($Datum, "DATUM");
			$conditie['DATUM'] = $Datum;
            $conditie['TYPE_DIENST_ID'] = isINT($TypeDienst, "TYPE_DIENST");
     
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
			$functie = "Rooster.GetObjects";
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
                    case "LID_ID" : 
                        {
                            $lidID = isINT($value, "LID_ID");
                            $where .= " AND LID_ID=?";
                            array_push($query_params, $lidID);	
                            
                            Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
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
					case "TYPES" : 
						{
							isCSV($value, "TYPES");
							$where .= sprintf(" AND TYPE_ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: TYPES='%s'", $functie, $value));
							break;
						}	
					case "AANWEZIG" : 
						{
							$aanwezig = isBOOL($value, "AANWEZIG");
							$where .= " AND AANWEZIG=?";
							array_push($query_params, $aanwezig);

							Debug(__FILE__, __LINE__, sprintf("%s: AANWEZIG='%s'", $functie, $aanwezig));
							break;
						}		
                    case "AFWEZIG" : 
                        {
                            $afwezig = isBOOL($value, "AFWEZIG");
                            $where .= " AND AFWEZIG=?";
                            array_push($query_params, $afwezig);

                            Debug(__FILE__, __LINE__, sprintf("%s: AFWEZIG='%s'", $functie, $afwezig));
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
					`####diensten_view`" . $where . $orderby;
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
		function VerwijderObject($id, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Diensten.VerwijderObject('%s', %s)", $id, (($verificatie === false) ? "False" :  $verificatie)));				
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
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
			Debug(__FILE__, __LINE__, sprintf("Diensten.HerstelObject('%s')", $id));

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
		function AddObject($DienstData)
		{
			Debug(__FILE__, __LINE__, sprintf("Diensten.AddObject(%s)", print_r($DienstData, true)));
			
			$l = MaakObject('Login');
        // TODO
	//		if ($l->magSchrijven() == false)	
	//			throw new Exception("401;Geen schrijfrechten;");

			if ($DienstData == null)
				throw new Exception("406;Diensten data moet ingevuld zijn;");			

            // Als lid niet meegegeven is, gebruik dan ingelogde lid
            if (!array_key_exists('LID_ID', $DienstData))
            {
                $DienstData['LID_ID'] = $l->getUserFromSession(); 
            }

			if (array_key_exists('ID', $DienstData))
			{
				$id = isINT($DienstData['ID'], "ID");
				
				// ID is opgegeven, maar bestaat record?
				try 	// Als record niet bestaat, krijgen we een exception
				{	
					$this->GetObject($id);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
			}

			if (!array_key_exists('DATUM', $DienstData))
				throw new Exception("406;DATUM is verplicht;");			
			
			if ($DienstData['DATUM'] == null)	
				throw new Exception("406;DATUM is verplicht;");	

            if (!array_key_exists('TYPE_DIENST_ID', $DienstData))
				throw new Exception("406;TYPE_DIENST_ID is verplicht;");			                
			
			try 	// Als record niet bestaat, krijgen we een exception
			{				
				$this->GetObjectByDatumDienst($DienstData['DATUM'], $DienstData['TYPE_DIENST_ID']); 
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Dienst %s op %s bestaat al;", $DienstData['TYPE_DIENST_ID'], $DienstData['DATUM']));
					
            // Check of rooster voor de dag bestaat
            $r = MaakObject('Rooster');
            try 
            {
                $rooster = $r->GetObject(null, $DienstData['DATUM']);  // Als datum niet bestaat komt er een exceptie
            }
            catch (Exception $e) 
            {
                $rooster['DATUM'] = $DienstData['DATUM'];
                $rooster = $r->AddObject($rooster);
            }

			// Neem data over uit aanvraag
			$v = $this->RequestToRecord($DienstData);
            $v['ROOSTER_ID'] = $rooster['ID'];
            $v['INGEVOERD_DOOR_ID'] = $l->getUserFromSession(); 

			$id = parent::DbToevoegen($v);
			Debug(__FILE__, __LINE__, sprintf("Dienst toegevoegd id=%d", $id));

			return $this->GetObject($id);
		}

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function UpdateObject($DienstData)
		{
			Debug(__FILE__, __LINE__, sprintf("Diensten.UpdateObject(%s)", print_r($DienstData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)	
				throw new Exception("401;Geen schrijfrechten;");

			if ($DienstData == null)
				throw new Exception("406;Diensten data moet ingevuld zijn;");			

			if (!array_key_exists('ID', $DienstData))
				throw new Exception("406;ID moet ingevuld zijn;");

			$id = isINT($DienstData['ID'], "ID");
            $ddb = $this->GetObject($id);

            $Datum = $ddb['DATUM'];
            $Dienst = $ddb['TYPE_DIENST_ID'];

            // Voorkom dat dienst meerdere keren voorkomt in de tabel
			if (array_key_exists('DATUM', $DienstData))
			{
				if ($DienstData['DATUM'] == null)	
					throw new Exception("406;DATUM is verplicht;");
                    
                isDATE($Datum, "DATUM");    
                $Datum = $DienstData['DATUM'];
            }

            // Voorkom dat dienst meerdere keren voorkomt in de tabel
			if (array_key_exists('TYPE_DIENST_ID', $DienstData))
			{
				if ($DienstData['TYPE_DIENST_ID'] == null)	
					throw new Exception("406;TYPE_DIENST_ID is verplicht;");
                    
                isDATE($Datum, "DATUM");    
                $Dienst = isINT($DienstData['TYPE_DIENST_ID'], "TYPE_DIENST_ID");
            }

            try 	// Als record niet bestaat, krijgen we een exception
            {
                $adb = $this->GetObjectByDatumDienst($Datum, $Dienst);
            }
            catch (Exception $e) {}

            if (parent::NumRows() > 0)
            {
                if ($id != $adb['ID'])
                    throw new Exception(sprintf("409;Dienst %s op %s bestaat al;", $Dienst, $Datum));
            }					
			
            // Check of rooster voor de dag bestaat
            $r = MaakObject('Rooster');
            try 
            {
                $rooster = $r->GetObject(null, $Datum);  // Als datum niet bestaat komt er een exceptie
            }
            catch (Exception $e) 
            {
                $rooster['DATUM'] = $Datum;
                $rooster = $r->AddObject($rooster);
            }
            
			// Neem data over uit aanvraag
			$v = $this->RequestToRecord($DienstData);
            $v['ROOSTER_ID'] = $rooster['ID'];
            $v['INGEVOERD_DOOR_ID'] = $l->getUserFromSession(); 

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
			Debug(__FILE__, __LINE__, sprintf("Diensten.RequestToRecord(%s)", print_r($input, true)));
			$record = array();

			$field = 'ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

            $field = 'DATUM';
            if (array_key_exists($field, $input))
                $record[$field] = isDATE($input[$field], $field);

            $field = 'LID_ID';
            if (array_key_exists($field, $input))
                $record[$field] = isINT($input[$field], $field, false, "Leden");

            $field = 'TYPE_DIENST_ID';
            if (array_key_exists($field, $input))
                $record[$field] = isINT($input[$field], $field, false, "Types");

			$field = 'AANWEZIG';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'AFWEZIG';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);
			
            // AANWEZIG en AFWEZIG kunnen niet tegelijk TRUE zijn.
            if ($record['AANWEZIG'] == true) 
                $record['AFWEZIG'] = false;

            if ($record['AFWEZIG'] == true) 
                $record['AANWEZIG'] = false;    

            if (array_key_exists('INGEVOERD_DOOR_ID', $input))
				throw new Exception("405;INGEVOERD_DOOR_ID kan niet extern gezet worden;");

            if (array_key_exists('ROOSTER_ID', $input))
				throw new Exception("405;ROOSTER_ID kan niet extern gezet worden;");

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
                
            if (isset($record['INGEVOERD_DOOR_ID']))
				$retVal['INGEVOERD_DOOR_ID']  = $record['INGEVOERD_DOOR_ID'] * 1;		

			if (isset($record['TYPE_DIENST_ID']))
				$retVal['TYPE_DIENST_ID']  = $record['TYPE_DIENST_ID'] * 1;
			
			if (isset($record['ROOSTER_ID']))
				$retVal['ROOSTER_ID']  = $record['ROOSTER_ID'] * 1;	
				
			// booleans	
			if (isset($record['AANWEZIG']))
				$retVal['AANWEZIG']  = $record['AANWEZIG'] == "1" ? true : false;

			if (isset($record['AFWEZIG']))
				$retVal['AFWEZIG']  = $record['AFWEZIG'] == "1" ? true : false;

			if (isset($record['VERWIJDERD']))
				$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

			return $retVal;
		}
	}
