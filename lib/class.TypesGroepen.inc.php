<?php
class TypesGroepen extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "ref_types_groepen";
		$this->dbView = "types_groepen_view";
		$this->Naam = "Types groepen";
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
				`CODE` varchar(10) DEFAULT NULL,
				`EXT_REF` varchar(25) DEFAULT NULL,
				`OMSCHRIJVING` varchar(75) NOT NULL,
				`SORTEER_VOLGORDE` tinyint UNSIGNED DEFAULT NULL,
				`READ_ONLY` tinyint UNSIGNED NOT NULL DEFAULT '0',       
				`BEDRAG_EENHEDEN` tinyint UNSIGNED NOT NULL DEFAULT '0',  
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`VERWIJDERD`)
				)", $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = "
				(9, 'Vliegveld',1,1),
				(1, 'Banen',2,0),
				(2, 'Windrichting',32,0),
				(3, 'Windkracht',33,0),
				(4, 'Vliegtuig types',10,0),
				(5, 'Start methodes',11,1),
				(6, 'Lidmaatschap', 12,1),
				(7, 'Bedrijf',20,1),                    
				(10, 'Opleidingsblok',35,0),
				(11, 'Bewolking',36,0),
				(12, 'Windontwikkeling',34,0),
				(13, 'Zicht',31,0),
				(14, 'Luchtruim',30,0),
				(15, 'Veldleiding',21,0),
				(16, 'Termiek',38,0),
				(17, 'Bewolking dekking',37,0),
				(18, 'Diensten',14,1),
				(19, 'Status',13,1),
				(20, 'DDWV strippen',20,1),
				(21, 'iDeal bestellen',21,1);";

			$query = sprintf("
					INSERT INTO `%s` (
						`ID`,  
						`OMSCHRIJVING`, 
						`SORTEER_VOLGORDE`, 
						`READ_ONLY`) 
					VALUES
						%s;", $this->dbTable, $inject);
		
			parent::DbUitvoeren($query);

            $query = sprintf("UPDATE %s SET BEDRAG_EENHEDEN=1 WHERE ID IN(20, 21)", $this->dbTable);
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
				groepen.*
			FROM
				`%s` `groepen`
			WHERE
				`groepen`.`VERWIJDERD` = %d
			ORDER BY 
				SORTEER_VOLGORDE, ID;";		
						
		parent::DbUitvoeren("DROP VIEW IF EXISTS types_groepen_view");							
		parent::DbUitvoeren(sprintf($query, "types_groepen_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_types_groepen_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_types_groepen_view", $this->dbTable, 1));
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID)
	{
		$functie = "TypesGroepen.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));	

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

		$functie = "TypesGroepen.GetObjects";
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
				`####types_groepen_view` " . $where; // . $orderby;
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
	function VerwijderObject($id = null, $verificatie = true)
	{
		$functie = "TypesGroepen.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

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
		$functie = "TypesGroepen.HerstelObject";
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
	function AddObject($TypeData)
	{
		$functie = "TypesGroepen.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($TypeData, true)));
		
		if (!$this->heeftDataToegang(null, false))
			throw new Exception("401;Geen schrijfrechten;");
		
		if ($TypeData == null)
			throw new Exception("406;Type data moet ingevuld zijn;");
				
		if (array_key_exists('ID', $TypeData))
		{
			$id = isINT($TypeData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{	
				$this->GetObject($id);
			}
			catch (Exception $e) {}			

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}
		
		if (!array_key_exists('OMSCHRIJVING', $TypeData))
			throw new Exception("406;Omschrijving is verplicht;");
		
		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($TypeData);

		$id = parent::DbToevoegen($t);
		Debug(__FILE__, __LINE__, sprintf("type toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($TypeData)
	{
		$functie = "TypesGroepen.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($TypeData, true)));
		
		if (!$this->heeftDataToegang(null, false))
			throw new Exception("401;Geen schrijfrechten;");

		if ($TypeData == null)
			throw new Exception("406;Type data moet ingevuld zijn;");
				
		if (!array_key_exists('ID', $TypeData))
			throw new Exception("406;ID moet ingevuld zijn;");
			
		$id = isINT($TypeData['ID'], "ID");

		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($TypeData);

		parent::DbAanpassen($id, $t);
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

		$field = 'SORTEER_VOLGORDE';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true);

		$field = 'READ_ONLY';
		if (array_key_exists($field, $input))
			$record[$field] = isBOOL($input[$field], $field);

        $field = 'BEDRAG_EENHEDEN';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);
					
		if (array_key_exists('OMSCHRIJVING', $input))
			$record['OMSCHRIJVING'] = $input['OMSCHRIJVING']; 

		if (array_key_exists('CODE', $input))
			$record['CODE'] = $input['CODE']; 

		if (array_key_exists('EXT_REF', $input))
			$record['EXT_REF'] = $input['EXT_REF']; 
			
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

		if (isset($record['SORTEER_VOLGORDE']))
			$retVal['SORTEER_VOLGORDE']  = $record['SORTEER_VOLGORDE'] * 1;		

		// booleans	
		if (isset($record['READ_ONLY']))
			$retVal['READ_ONLY']  = $record['READ_ONLY'] == "1" ? true : false;

        if (isset($record['BEDRAG_EENHEDEN']))
            $retVal['BEDRAG_EENHEDEN']  = $record['BEDRAG_EENHEDEN'] == "1" ? true : false;

		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}
