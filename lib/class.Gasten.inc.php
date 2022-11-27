<?php
class Gasten extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "oper_gasten";
		$this->dbView = "gasten_view";
		$this->Naam = "Gasten";
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
                `NAAM` varchar(75) NOT NULL,
                `DATUM` date NOT NULL,
                `OPMERKINGEN` text DEFAULT NULL,    
				`VELD_ID` mediumint UNSIGNED DEFAULT NULL,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`VERWIJDERD`),

				FOREIGN KEY (VELD_ID) REFERENCES ref_types(ID)	
            )", $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = array(
				"1, '####-05-01', 'Salamander', 'BVD-001-456'",
                "1, '####-05-01', 'Regenworm', NULL",
                "1, '####-05-01', 'Adder', NULL",
                "1, '####-05-02', 'Kikker', 'QQP-4566'",
                "1, '####-05-04', 'Kameleon', '1SVR-12'",
                "1, '####-05-04', 'Gekko', 'PLW'");

			$inject = str_replace("####", strval(date("Y")), $inject);		// aanwezigheid in dit jaar

			$i = 0;    
			foreach ($inject as $record)
			{    
				$fields = sprintf($record, parent::fakeText());
							
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`NAAM`, 
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
				g.*,
				`v`.`OMSCHRIJVING` AS `VELD`
			FROM
				`%s` `g` 
				LEFT JOIN `ref_types` `v` ON (`g`.`VELD_ID` = `v`.`ID`)   
			WHERE
				`g`.`VERWIJDERD` = %d
			ORDER BY 
				DATUM, ID;";				
		
		parent::DbUitvoeren("DROP VIEW IF EXISTS gasten_view");							
		parent::DbUitvoeren(sprintf($query, "gasten_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_gasten_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_gasten_view", $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID)
	{
		$functie = "Gasten.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));	

		if ($ID == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		$conditie = array();
		$conditie['ID'] = isINT($ID, "ID");

		$obj = parent::GetSingleObject($conditie);
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

		$functie = "Gasten.GetObjects";
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

		$query = "
			SELECT 
				%s
			FROM
				`####gasten_view` " . $where; // . $orderby;
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
		$functie = "Gasten.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));
		
		if (!$this->heeftDataToegang(null, false))
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
		$functie = "Gasten.HerstelObject";
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
	function AddObject($GastenData)
	{
		$functie = "Gasten.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($GastenData, true)));
		
		
		if ($GastenData == null)
			throw new Exception("406;Gasten data moet ingevuld zijn;");
				
		if (array_key_exists('ID', $GastenData))
		{
			$id = isINT($GastenData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{					
				$this->GetObject($id);
			}
			catch (Exception $e) {}			

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}

        if (!array_key_exists('DATUM', $GastenData))
            throw new Exception("406;DATUM is verplicht;");

		if (!array_key_exists('NAAM', $GastenData))
			throw new Exception("406;NAAM is verplicht;");
		
		
		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($GastenData);

		$id = parent::DbToevoegen($t);
		Debug(__FILE__, __LINE__, sprintf("Gast toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($GastenData)
	{
		$functie = "Gasten.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($GastenData, true)));
	

		if ($GastenData == null)
			throw new Exception("406;Gasten data moet ingevuld zijn;");
				
		if (!array_key_exists('ID', $GastenData))
			throw new Exception("406;ID moet ingevuld zijn;");
		
		$id = isINT($GastenData['ID'], "ID");
			
		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($GastenData);

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

        $field = 'DATUM';
        if (array_key_exists($field, $input))
            $record[$field] = isDATE($input[$field], $field);

        $field = 'NAAM';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];	 

		$field = 'VELD_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, 'Types');
			
				
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

		if (isset($record['VELD_ID']))
			$retVal['VELD_ID']  = $record['VELD_ID'] * 1;	

		// booleans	
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}