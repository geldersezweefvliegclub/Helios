<?php
class Audit extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "audit";
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
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`TABEL` varchar(15) DEFAULT NULL,
				`TABEL_NAAM` varchar(25) DEFAULT NULL,
				`ACTIE` varchar(15) DEFAULT NULL,
				`VOOR` text DEFAULT NULL,
				`DATA` text DEFAULT NULL,
				`RESULTAAT` text DEFAULT NULL,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LID_ID`),
					
				FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID)
			)", $this->dbTable);
		parent::DbUitvoeren($query);
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
			audit.*,
			`l`.`NAAM` AS `NAAM`,
			`l`.`STARTTOREN` AS `STARTTOREN`,
			`l`.`BEHEERDER` AS `BEHEERDER`,
			`l`.`LIDTYPE_ID` AS `LIDTYPE_ID`,
			`t`.`OMSCHRIJVING` AS `LIDTYPE`,
			`di`.`DDWV` AS `DDWV`
		FROM
			`%s` `audit`
			LEFT JOIN `ref_leden` `l` ON (`audit`.`LID_ID` = `l`.`ID`)
			LEFT JOIN `ref_types` `t` ON (`l`.`LIDTYPE_ID` = `t`.`ID`)
			LEFT JOIN `oper_daginfo` `di` ON (`audit`.`DATUM` = `di`.`DATUM`)
		WHERE
			`audit`.`VERWIJDERD` = %d
		ORDER BY `audit`.`LAATSTE_AANPASSING` DESC;";	

		parent::DbUitvoeren("DROP VIEW IF EXISTS audit_view");							
		parent::DbUitvoeren(sprintf($query, "audit_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_audit_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_audit_view", $this->dbTable, 1));
	}

			/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($Tabel, $Naam, $Actie, $Voor, $Data, $Resultaat)
	{
		$functie = "Audit.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s, %s, %s, %s)", $functie, $Tabel, $Actie, $Voor, $Data, $Resultaat));
											
		$l = MaakObject('Login');

		$record = array();
		$record['LID_ID'] = $l->getUserFromSession(); 
		$record['DATUM'] = date("Y-m-d");   
		$record['TABEL'] = $Tabel;   
		$record['TABEL_NAAM'] = $Naam;     
		$record['ACTIE'] = $Actie;    
		$record['VOOR'] = $Voor;    
		$record['DATA'] = $Data;    
		$record['RESULTAAT'] = $Resultaat;    
					
		$id = parent::DbToevoegen($record);
		Debug(__FILE__, __LINE__, sprintf("Audit toegevoegd id=%d", $id));
	}

			/*
	Haal een dataset op met records als een array uit de database. 
	*/		
	function GetObjects($params)
	{
		global $app_settings;

		$functie = "Audit.GetObjects";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
		
		$where = ' WHERE 1=1 ';
		$orderby = "";
		$alleenLaatsteAanpassing = false;
		$hash = null;
		$limit = 1000;	 // standaard max 1000 records
		$start = -1;
		$velden = "*";
		$in = "";
		$alleenVerwijderd = false;
		$query_params = array();

		$l = MaakObject('Login');
		if (!$l->isBeheerder())
		{
			Debug(__FILE__, __LINE__, sprintf("%s: %s is geen beheerder, beperk query", $functie, $l->getUserFromSession()));

			$where .= sprintf(" AND LID_ID=%d", $l->getUserFromSession());
		}

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
				case "DATUM" : 
					{
						$datum = isDATE($value, "DATUM");

						$where .= " AND DATE(DATUM) = ? ";
						array_push($query_params, $datum);

						Debug(__FILE__, __LINE__, sprintf("%s: DATUM='%s'", $functie, $datum));
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
				case "TABEL" : 
					{
						$where .= " AND TABEL IN(?)";
						array_push($query_params, trim($value));

						Debug(__FILE__, __LINE__, sprintf("%s: TABEL='%s'", $functie, $value));
						break;
					}	 
				case "SELECTIE" : 
					{
						$where .= " AND ((NAAM LIKE ?) ";
						$where .= "  OR (TABEL LIKE ?) ";
						$where .= "  OR (ACTIE LIKE ?) ";
						$where .= "  OR (VOOR LIKE ?) ";
						$where .= "  OR (DATA LIKE ?) ";
						$where .= "  OR (RESULTAAT LIKE ?)) ";

						$s = "%" . trim($value) . "%";
						$query_params = array($s, $s, $s, $s, $s, $s);

						Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));	
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
				`####audit_view` " . $where . $orderby;
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
		
		if (isset($record['ZUSTERCLUB_ID']))
			$retVal['ZUSTERCLUB_ID']  = $record['ZUSTERCLUB_ID'] * 1;	
			
		// booleans					
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}
