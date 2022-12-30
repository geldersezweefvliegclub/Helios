<?php
class Leden extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "ref_leden";
		$this->dbView = "leden_view";
		$this->Naam = "Leden";
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
				`NAAM` varchar(255) NOT NULL,
				`VOORNAAM` varchar(15) DEFAULT NULL,
				`TUSSENVOEGSEL` varchar(8) DEFAULT NULL,
				`ACHTERNAAM` varchar(30) DEFAULT NULL,
				`ADRES` varchar(50) DEFAULT NULL,
				`POSTCODE` varchar(10) DEFAULT NULL,
				`WOONPLAATS` varchar(50) DEFAULT NULL,
				`TELEFOON` varchar(255) DEFAULT NULL,
				`MOBIEL` varchar(255) DEFAULT NULL,
				`NOODNUMMER` varchar(255) DEFAULT NULL,
				`EMAIL` varchar(45) DEFAULT NULL,
				`LIDNR` varchar(10) DEFAULT NULL,
				`LIDTYPE_ID` mediumint UNSIGNED NOT NULL,
				`STATUSTYPE_ID` mediumint UNSIGNED NULL,
				`ZUSTERCLUB_ID` mediumint UNSIGNED DEFAULT NULL,
				`BUDDY_ID` mediumint UNSIGNED NULL,
				`BUDDY_ID2` mediumint UNSIGNED NULL,
				`LIERIST` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`LIERIST_IO` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`STARTLEIDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`INSTRUCTEUR` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`CIMT` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`DDWV_CREW` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`DDWV_BEHEERDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`BEHEERDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`STARTTOREN` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`ROOSTER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`SLEEPVLIEGER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`RAPPORTEUR` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`GASTENVLIEGER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`CLUBBLAD_POST` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`MEDICAL` date DEFAULT NULL,
				`GEBOORTE_DATUM` date DEFAULT NULL,
				`INLOGNAAM` varchar(45) DEFAULT NULL,
				`WACHTWOORD` varchar(255) DEFAULT NULL,    
				`SECRET` varchar(255) DEFAULT NULL,  
				`AUTH` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`AVATAR` varchar(255) DEFAULT NULL,        
				`STARTVERBOD` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`PRIVACY` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`SLEUTEL1` varchar(25) DEFAULT NULL,
				`SLEUTEL2` varchar(25) DEFAULT NULL,
				`KNVVL_LIDNUMMER` varchar(25) DEFAULT NULL,
				`BREVET_NUMMER` varchar(25) DEFAULT NULL,
				`EMAIL_DAGINFO` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`OPMERKINGEN` text DEFAULT NULL,
				`TEGOED` FLOAT NOT NULL DEFAULT 0,
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`NAAM`),
					INDEX (`LIDTYPE_ID`),
					INDEX (`STARTLEIDER`),
					INDEX (`INSTRUCTEUR`),
					INDEX (`LIERIST`),
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (LIDTYPE_ID) REFERENCES ref_types(ID),
					FOREIGN KEY (ZUSTERCLUB_ID) REFERENCES %s(ID),
					FOREIGN KEY (BUDDY_ID) REFERENCES %s(ID),
					FOREIGN KEY (BUDDY_ID2) REFERENCES %s(ID)  			
			)", $this->dbTable, $this->dbTable, $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$query = sprintf("
				INSERT INTO 
					`%s` 
						(`ID`,  
						`NAAM`,
						`VOORNAAM`, 
						`TUSSENVOEGSEL`, 
						`ACHTERNAAM`, 
						`ADRES`, 
						`POSTCODE`, 
						`WOONPLAATS`, 
						`LIDNR`, 
						`LIDTYPE_ID`, 
						`MOBIEL`, 
						`EMAIL`, 
						`NOODNUMMER`, 
						`TELEFOON`, 
						`INSTRUCTEUR`, 
						`DDWV_CREW`, 
						`STARTLEIDER`, 
						`STARTVERBOD`, 
						`LIERIST`) 
					VALUES
						('1','Beheerder', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 601 ,NULL, NULL, NULL, NULL, '0', '0','0','0','0'),
						('2','Zusterclub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 607 ,NULL, NULL, NULL, NULL, '0', '0','0','0','0'),
						('10855','Truus de Mier', 'Truus', 'de', 'Mier', 'Boompje 72', '2211 AA', 'Puthoek', '12091', 625,NULL,'mier@fabeltje.com', NULL, NULL, '0', '0','0','1','0'),
						('10213','Teun Stier', 'Teun', NULL, 'Stier', 'Weide 1', '7311 AA', 'De Veenen', '10022', 602, NULL, '06-1256770','stier@fabeltje.com','06-1256770', '0','1','1','1','0'),
						('10858','Meneer de Uil', 'Meneer', NULL, 'Uil', 'Kerkstraat 42', '1299 AA', 'Heuven', '41382', 601,'06-5112006', NULL, '0882-10111','0310-430210', '0','0','0','1','1'),
						('10408','Meindert het Paard', 'Meindert', 'het', 'Paard', 'Hoefijzer 3', '8311 QR', 'Heidekant', '11139',603,'06-1025500','meindert@fabeltje.com','0112-11801','086-1506822','1', '0','1','0','1'),
						('10804','Willem Bever', 'Willem', NULL, 'Bever', 'Linge 66', '7412 BA', 'Dunsborg', '588139', 602,'06-2828281','willem@fabeltje.com', NULL, NULL, '0', '0','0','1','1'),
						('10632','Gerrit de Postduif', 'Gerrit', 'de', 'Postduif', 'Sperwerweg 57', '1623 VT', 'Zwiep', '11140',602,'06-10285005','g.de.postduif@fabeltje.com','0319-18348','0278-20000', '0','0','0','1','0'),
						('10063','Isadora Paradijsvogel', 'Isadora', NULL, 'Paradijsvogel', 'Vogellaarstraat 1a', '5407 KR', 'Kulsdom', '91900',606,'06-10005001','isadora.paradijsvogel@fabeltje.com','0112-99820','0818-71100', '0','0','0','1','0'),
						('10265','Lowieke de Vos','Lowieke', NULL, 'Vos', 'Kattenstaart 18', '9277 YT', 'Zuidloo', '12239',602,'06-10333005','vos@fabeltje.com','0112-10008','020-120333', '0','0','0','1','0'),
						('10395','Juffrouw Ooievaar', 'Juffrouw', NULL, 'Ooievaar', 'Torenspits 18', '1231 JG', 'Tjoene', '22154',608,'06-2011301','ooievaar@fabeltje.com', NULL, '070-120311', '0','0','0','0','0'),
						('10115','Momfer de Mol', 'Momfer', 'de', 'Mol', 'Trompetdreef 31', '7812 UI', 'Hanendorp', '93562',625,'06-2009710','m.de.mol@fabeltje.com', NULL, '020-200120','1', '0','0','1','0'),
						('10470','Bor de Wolf','Bor', 'de', 'Wolf', 'Wolga 11', '7630 EE', 'Niersen', '11511',602,'06-1119220','wolf@fabeltje.com', NULL, '027-120887','1', '0','0','1','0'),
						('10001','Zoef de Haas', 'Zoef', 'de', 'Haas', 'Ringlaan 99', '1056 AI', 'Assel', '31313',603,'06-6119330','zoefzoef@fabeltje.com', NULL, '027-120827','1', '0','1','1','0');
				", $this->dbTable);
			parent::DbUitvoeren($query);	
			
			// maak gebruiker beheerder beheerder
			$query = sprintf("UPDATE `%s` SET `BEHEERDER` = 1 WHERE ID = 1", $this->dbTable);
			parent::DbUitvoeren($query);

			// toevoegen zusterclub
			$query = sprintf("UPDATE `%s` SET `ZUSTERCLUB_ID` = 2 WHERE ID IN (10115, 10855)", $this->dbTable);
			parent::DbUitvoeren($query);

			// voeg login informatie toe om te kunnen testen
			$login_info = array();
			$login_info['1']     = (object) ['INLOGNAAM' => 'beheer', 'WACHTWOORD' => sha1(strtolower ('beheer') . '@dm1nAcc0unt')];	// beheerder
			$login_info['10115'] = (object) ['INLOGNAAM' => 'momfer', 'WACHTWOORD' => sha1(strtolower ('momfer') . 'mol')];				// Momfer de mol als DDWV'er
			$login_info['10001'] = (object) ['INLOGNAAM' => 'zoef',   'WACHTWOORD' => sha1(strtolower ('zoef')   . 'haas')];			// Zoef de haas als instructeur
			$login_info['10213'] = (object) ['INLOGNAAM' => 'teun',   'WACHTWOORD' => sha1(strtolower ('teun')   . 'stier')];			// Teun stier als DDWV crew
			$login_info['10632'] = (object) ['INLOGNAAM' => 'gerrit', 'WACHTWOORD' => sha1(strtolower ('gerrit') . 'postduif')];		// Gerrit de Postduif als lid

			foreach ($login_info as $ID => $credentials) 
			{
				$query = sprintf("UPDATE `%s` SET `INLOGNAAM` = '%s', `WACHTWOORD` = '%s'  WHERE ID = %s", $this->dbTable, $credentials->INLOGNAAM, $credentials->WACHTWOORD, $ID);
				parent::DbUitvoeren($query);	
			}

			$query = sprintf("UPDATE `%s` SET `OPMERKINGEN` = 'DEMO Account' WHERE ID >= 0", $this->dbTable);
			parent::DbUitvoeren($query);
		}
	}

	/*
	Maak database views, als view al bestaat wordt deze overschreven
	*/		
	function CreateViews()
	{
		global $app_settings;

		$paxBevoegdheid = 271;
		if (isset($app_settings['PaxBevoegdheid']))
		{
			$paxBevoegdheid = $app_settings['PaxBevoegdheid'];
		}

		$l = MaakObject('Login');
		if ($l->isInstaller() == false)
			throw new Exception("401;Geen installer;");

		$query = "CREATE VIEW `%s` AS
			SELECT 
				l.*,
				IF ((SELECT count(*) FROM `oper_progressie` WHERE `LID_ID` = l.ID AND `COMPETENTIE_ID` = %d) = 0, 0, 1) AS PAX,
				`t`.`OMSCHRIJVING` AS `LIDTYPE`,
				`s`.`OMSCHRIJVING` AS `STATUS`,
				`z`.`NAAM` AS `ZUSTERCLUB`,
				`b`.`NAAM` AS `BUDDY`,
				`b2`.`NAAM` AS `BUDDY2`
			FROM
				`%s` `l`    
				LEFT JOIN `ref_types` `t` ON (`l`.`LIDTYPE_ID` = `t`.`ID`)
				LEFT JOIN `ref_types` `s` ON (`l`.`STATUSTYPE_ID` = `s`.`ID`)
				LEFT JOIN `ref_leden` `z` ON (`l`.`ZUSTERCLUB_ID` = `z`.`ID`)
				LEFT JOIN `ref_leden` `b` ON (`l`.`BUDDY_ID` = `b`.`ID`)
				LEFT JOIN `ref_leden` `b2` ON (`l`.`BUDDY_ID` = `b2`.`ID`)
			WHERE
				`l`.`VERWIJDERD` = %d
			ORDER BY 
				ACHTERNAAM, VOORNAAM;";
							
		parent::DbUitvoeren("DROP VIEW IF EXISTS leden_view");							
		parent::DbUitvoeren(sprintf($query, "leden_view", $paxBevoegdheid, $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_leden_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_leden_view", $paxBevoegdheid, $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	toegangControleOverslaan is toegevoegd voor interne reden, zie InstructieVlucht() in Startlijst
	*/
	function GetObject($ID, $toegangControleOverslaan = false)
	{
		$functie = "Leden.GetObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $ID, $toegangControleOverslaan ? "true" : "false"));	

		$privacyMasker = false;

		if (!isset($ID))
			throw new Exception("406;Geen ID in aanroep;");

		$conditie = array();
		$conditie['ID'] = isINT($ID, "ID");
	

		// ophalen mag alleen door ingelogde gebruiker of beheerder, of vanuit een interne functie
		$l = MaakObject('Login');
		if (($l->getUserFromSession() != $ID) && ($toegangControleOverslaan == false))
		{
			if (!$this->heeftDataToegang() && !$l->isBeheerderDDWV() && !$l->isRooster())
				throw new Exception("401;Geen leesrechten;");

			$rlObj = $this->GetObject($l->getUserFromSession());
			$privacyMasker = $rlObj['PRIVACY'];
		}
		$obj = parent::GetSingleObject($conditie);
		Debug(__FILE__, __LINE__, print_r($obj, true));

		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");
					
		$obj = $this->RecordToOutput($obj);
		$obj = $this->privacyMask($obj, $privacyMasker); // privacy maskering
		return $obj;				
	}

	/*
	Haal een enkel record op uit de database
	*/
	function GetObjectByLidnr($LidNr)
	{
		$functie = "Leden.GetObjectByLidnr";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $LidNr));	

		if ($LidNr == null)
			throw new Exception("406;Geen LidNr in aanroep;");

		$conditie = array();
		$conditie['LIDNR'] = isINT($LidNr, "LIDNR");
		$conditie['VERWIJDERD'] = 0;		

		$obj = parent::GetSingleObject($conditie);	
		
		// ophalen mag alleen door ingelogde gebruiker of beheerder
		$l = MaakObject('Login');
		if ($l->getUserFromSession() != $obj['ID'])
		{
			if (!$this->heeftDataToegang() && !$l->isBeheerderDDWV())
				throw new Exception("401;Geen leesrechten;");
		}			

		if ($obj == null)
			throw new Exception("404;Record niet gevonden;");

		$obj = $this->RecordToOutput($obj);
		return $obj;				
	}
		
	/*
	Vraag lid record op basis van de login naam, login naam is dus uniek
	*/
	function GetObjectByLoginNaam($loginNaam)
	{
		$functie = "Leden.GetObjectByLoginNaam";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $loginNaam));	

		if ($loginNaam == null)
			throw new Exception("406;Geen login naam in aanroep;");

		$conditie = array();
		$conditie['INLOGNAAM'] = $loginNaam ;
		$conditie['VERWIJDERD'] = 0;		

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

		$functie = "Leden.GetObjects";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
		
		$where = ' WHERE 1=1 ';
		$orderby = "";
		$alleenLaatsteAanpassing = false;
		$hash = null;
		$limit = -1;
		$start = -1;
		$velden = "*";
		$alleenVerwijderd = false;
		$privacyMasker = false;
		$query_params = array();

		$l = MaakObject('Login');
		if ($l->isDDWV())
		{
			Debug(__FILE__, __LINE__, sprintf("%s: %s is DDWV'er, beperk query", $functie, $l->getUserFromSession()));
			$where .= " AND ((LIDTYPE_ID = 625) OR (DDWV_CREW = '1')) ";
		}
		else if (($l->isBeheerder() == false) && 
					($l->isBeheerderDDWV() == false) && 
					($l->isStarttoren() == false)) 
		{
			// 601 = Erelid
			// 602 = Lid
			// 603 = Jeugdlid
            // 604 = private owner
			// 606 = Donateur
			// 625 = DDWV
			$where .= " AND LIDTYPE_ID IN (601, 602,603, 604, 606, 625)";
		}

		$rlObj = $this->GetObject($l->getUserFromSession());
		$privacyMasker = $rlObj['PRIVACY'];
		
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
						$where .= " AND ((NAAM LIKE ?) ";
						$where .= "  OR (EMAIL LIKE ?) ";
						$where .= "  OR (TELEFOON LIKE ?) ";
						$where .= "  OR (MOBIEL LIKE ?) ";
						$where .= "  OR (NOODNUMMER LIKE ?)) ";

						$s = "%" . trim($value) . "%";
						$query_params = array($s, $s, $s, $s, $s);

						Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));	
						break;
					}
				case "TYPES" : 						
					{
						if ($l->isDDWV()) 
						{
							Debug(__FILE__, __LINE__, sprintf("%s: Gebruiker is DDWV'er, lidtype kan dan niet gespecifieerd worden", $functie, $limit));
						}
						else
						{
							isCSV($value, "TYPES");
							$where .= sprintf(" AND LIDTYPE_ID IN(%s)", trim($value));

							Debug(__FILE__, __LINE__, sprintf("%s: TYPES='%s'", $functie, $value));
						}
						break;
					}	
				case "CLUBLEDEN" : 
					{
						$alleenLeden = isBOOL($value, "CLUBLEDEN");

						if ($alleenLeden)
							$where .= sprintf(" AND LIDTYPE_ID > 600 AND LIDTYPE_ID <= 606 ");

						Debug(__FILE__, __LINE__, sprintf("%s: CLUBLEDEN='%s'", $functie, $alleenLeden));
						break;
					}	
				case "INSTRUCTEURS" : 
					{
						$alleenInstructeur = isBOOL($value, "INSTRUCTEURS");

						if ($alleenInstructeur)
							$where .= sprintf(" AND INSTRUCTEUR = 1 ");

						Debug(__FILE__, __LINE__, sprintf("%s: INSTRUCTEURS='%s'", $functie, $alleenInstructeur));
						break;
					}	
				case "DDWV_CREW" : 
					{
						$alleenDDWV = isBOOL($value, "DDWV_CREW");

						if ($alleenDDWV)
							$where .= sprintf(" AND DDWV_CREW = 1 ");

						Debug(__FILE__, __LINE__, sprintf("%s: DDWV_CREW='%s'", $functie, $alleenDDWV));
						break;
					}		
				case "BEHEERDERS" : 
					{
						$alleenBeheerder = isBOOL($value, "BEHEERDER");

						if ($alleenBeheerder)
							$where .= sprintf(" AND BEHEERDER = 1 ");

						Debug(__FILE__, __LINE__, sprintf("%s: BEHEERDER='%s'", $functie, $alleenBeheerder));
						break;
					}												
				case "LIERISTEN" : 
					{
						$alleenLieristen = isBOOL($value, "LIERISTEN");

						if ($alleenLieristen)
							$where .= sprintf(" AND LIERIST = 1 ");

						Debug(__FILE__, __LINE__, sprintf("%s: LIERISTEN='%s'", $functie, $alleenLieristen));
						break;
					}	
				case "LIO" : 
						{
							$alleenLIO = isBOOL($value, "LIO");
	
							if ($alleenLIO)
								$where .= sprintf(" AND LIERIST_IO = 1 ");
	
							Debug(__FILE__, __LINE__, sprintf("%s: LIERISTEN_IO='%s'", $functie, $alleenLIO));
							break;
						}	
				case "STARTLEIDERS" : 
					{
						$alleenStartleiders = isBOOL($value, "STARTLEIDERS");

						if ($alleenStartleiders)
							$where .= sprintf(" AND STARTLEIDER = 1 ");

						Debug(__FILE__, __LINE__, sprintf("%s: STARTLEIDERS='%s'", $functie, $alleenStartleiders));
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
				`####leden_view`" . $where . $orderby;
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

			for ($i=0; $i < count($retVal['dataset']) ; $i++) 
			{
				$retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);

				// privacy maskering
				$retVal['dataset'][$i] = $this->privacyMask($retVal['dataset'][$i], $privacyMasker);	
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
		$functie = "Leden.VerwijderObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

		// schrijven mag alleen door beheerder
		$l = MaakObject('Login');
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV())
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
		$functie = "Leden.HerstelObject";
		Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

		// schrijven mag alleen door (DDWV) beheerder
		$l = MaakObject('Login');

		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV())
			throw new Exception("401;Geen schrijfrechten;");

		if ($id == null)
			throw new Exception("406;Geen ID in aanroep;");
		
		isCSV($id, "ID");
		parent::HerstelVerwijderd($id);
	}	

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function AddObject($LidData)
	{
		$functie = "Leden.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($LidData, true)));
		
		// schrijven mag alleen door beheerder
		$l = MaakObject('Login');
		if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV())
			throw new Exception("401;Geen schrijfrechten;");
		
		if ($LidData == null)
			throw new Exception("406;Lid data moet ingevuld zijn;");			

		if (array_key_exists('ID', $LidData))
		{
			$id = isINT($LidData['ID'], "ID");
						
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{	
				$this->GetObject($id);
			}
			catch (Exception $e) {}	

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}
		
		// Voorkom het dezelfde lidnr meerdere keren voorkomt in de tabel
		if (array_key_exists('LIDNR', $LidData))
		{
			if ($LidData['LIDNR'] != null)	// null is altijd goed
			{
				try 	// Als record niet bestaat, krijgen we een exception
				{				
					$this->GetObjectByLidnr($LidData['LIDNR']);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met LIDNR=%s bestaat al;", $LidData['LIDNR']));
			}
		}

		// Voorkom het dezelfde login meerdere keren voorkomt in de tabel
		if (array_key_exists('INLOGNAAM', $LidData))
		{
			if ($LidData['INLOGNAAM'] != null)	// null is altijd goed
			{
				try 	// Als record niet bestaat, krijgen we een exception
				{				
					$this->GetObjectByLoginNaam($LidData['INLOGNAAM']);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met INLOGNAAM=%s bestaat al;", $LidData['INLOGNAAM']));
			}			
		}

		if ((!array_key_exists('NAAM', $LidData)) && (!array_key_exists('ACHTERNAAM', $LidData)))
			throw new Exception("406;NAAM is verplicht;");
		
		if (!array_key_exists('LIDTYPE_ID', $LidData))
			throw new Exception("406;Lidtype is verplicht;");
			
		// Neem data over uit aanvraag
		$l = $this->RequestToRecord($LidData);
					
		$id = parent::DbToevoegen($l);
		Debug(__FILE__, __LINE__, sprintf("lid toegevoegd id=%d", $id));

		$lid = $this->GetObject($id);

		if ($lid['SECRET'] == null)
			$this->SetSecret($id);

		return $lid;
	}

	/*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($LidData)
	{
		$functie = "Leden.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($LidData, true)));
		
		// schrijven mag alleen door ingelogde gebruiker of beheerder
		$l = MaakObject('Login');
		if ($l->getUserFromSession() != $LidData["ID"])
		{
			if (!$l->isBeheerder() && !$l->isBeheerderDDWV() && !$l->isCIMT() && !$l->isRooster())
				throw new Exception("401;Geen schrijfrechten;");
		}

		if ($LidData == null)
			throw new Exception("406;Lid data moet ingevuld zijn;");			

		if (!array_key_exists('ID', $LidData))
			throw new Exception("406;ID moet ingevuld zijn;");

		$id = isINT($LidData['ID'], "ID");
		
		// Voorkom dat datum meerdere keren voorkomt in de tabel
		if (array_key_exists('LIDNR', $LidData))
		{
			if ($LidData['LIDNR'] != null)	// null is altijd goed
			{
				try 	// Als record niet bestaat, krijgen we een exception
				{
					$lid = $this->GetObjectByLidnr($LidData['LIDNR']);

					if (parent::NumRows() > 0)
					{
						if ($id != $lid['ID'])
							throw new Exception("409;LidNr bestaat reeds;");
					}	
				}
				catch (Exception $e) {}
			}
		}

		// Voorkom het dezelfde login meerdere keren voorkomt in de tabel
		if (array_key_exists('INLOGNAAM', $LidData))
		{
			if ($LidData['INLOGNAAM'] != null)	// null is altijd goed
			{
				try 	// Als record niet bestaat, krijgen we een exception
				{
					$lid = $this->GetObjectByLoginNaam($LidData['INLOGNAAM']);
				
					if (parent::NumRows() > 0)
					{
						if ($id != $lid['ID'])
							throw new Exception("409;Inlognaam bestaat reeds;");
					}	
				}
				catch (Exception $e) {}
			}
		}			

		// Neem data over uit aanvraag
		$l = $this->RequestToRecord($LidData);

		parent::DbAanpassen($id, $l);
		if (parent::NumRows() === 0)
			throw new Exception("404;Record niet gevonden;");				
		
		$lid = $this->GetObject($id);

		if ($lid['SECRET'] == null)
			$this->SetSecret($id);

		return $lid;
	}		

	/*
	Upload van een avatar
	*/
	function UploadAvatar($id, $file)
	{
		$functie = "Leden.UploadAvatar";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $id));
		
		// schrijven mag alleen door ingelogde gebruiker of beheerder
		$l = MaakObject('Login');
		if ($l->getUserFromSession() != $id)
		{
			if (!$this->heeftDataToegang(null, false) && !$l->isBeheerderDDWV())
				throw new Exception("401;Geen schrijfrechten;");
		}

		$id = isINT($id, "ID");
		
		// geeft een exceptie als id niet bestaat
		$lid = $this->GetObject($id);

		$upload_dir = "avatars/";
		$ext_type = array('.gif','.jpg','.jpe','.jpeg','.png');

		$extension = null;

		if(strpos($file, 'data:image/jpeg;base64,') === 0) {
			$file = str_replace('data:image/jpeg;base64,', '', $file);
			$extension = '.jpg';
		} elseif (strpos($file, 'data:image/jpg;base64,') === 0) {
			$file = str_replace('data:image/jpg;base64,', '', $file);
			$extension = '.jpg';
		} elseif (strpos($file, 'data:image/png;base64,') === 0) {
			$file = str_replace('data:image/png;base64,', '', $file);
			$extension = '.png';
		} elseif (strpos($file, 'data:image/gif;base64,') === 0) {
			$file = str_replace('data:image/gif;base64,', '', $file);
			$extension = '.gif';
		}
		if (!in_array(strtolower($extension), $ext_type)) {
			Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar extentie '%s' is ongeldig)", $extension));
			throw new Exception("422;Onjuiste bestand extentie;");	
		}
		$image = base64_decode($file);

		$filename = $lid['INLOGNAAM'] . '-' . date('YmdHis') . $extension;
		Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar opslag =%s", $upload_dir. $filename));
		if(file_put_contents($upload_dir. $filename, $image) === FALSE) {
			Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar file_put_contents error"));
			throw new Exception("422;Bestand upload mislukt;");			
		}
		
		$url =  'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://{$_SERVER['HTTP_HOST']}";

		$upd['AVATAR'] = sprintf("%s/%s/%s", $url, $upload_dir, $filename);
		parent::DbAanpassen($id, $upd);

		Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar image url= %s", $upd['AVATAR']));
		return $upd['AVATAR'] ;
	}

	/*
	Zet secret key voor google authenticator
	*/
	function SetSecret($id)
	{
		$ga = new PHPGangsta_GoogleAuthenticator();
		$secret = $ga->createSecret();

		$query = sprintf("UPDATE `%s` SET `SECRET` = '%s'  WHERE ID = %s", $this->dbTable , $secret, $id);
		parent::DbUitvoeren($query);	
	}

	function isPermissie($key, $id = null, $lid = null)
	{
		$functie = "Leden.isPermissie";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s, %s)", $functie, $key, $id, isset($lid[$key]) ? $lid[$key] : "-"));	

		$retVal = false;

		if (($id == null) && ($lid == null)) {
			Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
			return false;
		}
		
		if ($lid == null) 
		{
			try
			{
				$lid = $this->GetObject($id);
			}
			catch(Exception $exception) { return false; }
		}
		
		if (isset($lid[$key]) &&  $lid[$key] == true) 
			$retVal = true;
			
		Debug(__FILE__, __LINE__, sprintf("Lid=%s isPermissie %s=%s", $lid['NAAM'], $key, ($retVal) ? "true" : "false"));
		return $retVal;				
	}


	function isClubVlieger($id = null, $lid = null)
	{
		$functie = "Leden.isClubVlieger";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $id, print_r($lid, true)));	
		$retVal = false;
		
		if (($id == null) && ($lid == null)) {
			Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
			return false;
		}

		if ($lid == null) 
		{
			try
			{
				$lid = $this->GetObject($id);
			}
			catch(Exception $exception) { return false; }
		}
		
		if (($lid['LIDTYPE_ID'] == "601") ||       // 601 = Erelid
			($lid['LIDTYPE_ID'] == "602") ||       // 602 = Lid 
			($lid['LIDTYPE_ID'] == "603") ||       // 603 = Jeugdlid
            ($lid['LIDTYPE_ID'] == "604") ||       // 604 = Private owner (mag ook op club vliegtuigen vliegen voor trainingsvlucht)
			($lid['LIDTYPE_ID'] == "606") ||       // 606 = Donateur
            ($lid['LIDTYPE_ID'] == "608") ||       // 608 = 5 Rittenkaart
            ($lid['LIDTYPE_ID'] == "611"))         // 611 = Cursist
			$retVal = true;
			
		Debug(__FILE__, __LINE__, sprintf("LIDTYPE_ID=%s isVliegendLid=%s", $lid['LIDTYPE_ID'], $retVal ? "true" : "false"));
		return $retVal;			
	}

	function isDDWV($id = null, $lid = null)
	{
		$functie = "Leden.isDDWV";
		Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $id, print_r($lid, true)));	
		$retVal = false;
		
		if (($id == null) && ($lid == null)) {
			Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
			return false;
		}
		
		if ($lid == null) 
		{
			try
			{
				$lid = $this->GetObject($id);
			}
			catch(Exception $exception) { return false; }
		}
		
		if ($lid['LIDTYPE_ID'] == "625")        // 625 = DDWV
			$retVal = true;
			
		Debug(__FILE__, __LINE__, sprintf("LIDTYPE_ID=%s isDDWV=%s", $lid['LIDTYPE_ID'], $retVal ? "true" : "false"));
		return $retVal;				
	}						

	// privacy maskering
	function privacyMask($lid, $privacy = false)
	{
		global $app_settings;

		$l = MaakObject('Login');
		if (array_key_exists("WACHTWOORD", $lid))
		{
			if (($l->isBeheerder() === true))
				$lid['WACHTWOORD'] 	= dechex(crc32($lid['WACHTWOORD']));
			else
				$lid['WACHTWOORD'] 	= "****";
		}

        // We mogen tegoeden van leden alleen opvragen als beheerder of DDWV beheerder
        if (array_key_exists("TEGOED", $lid))
        {
            if (($l->isBeheerder() !== true) && ($l->isBeheerderDDWV() !== true) && ($lid['ID'] !==  $l->getUserFromSession()))
                $lid['TEGOED'] 	= "-1";
        }
		
		$secret = (isset($lid['SECRET'] )) ? $lid['SECRET'] : null;
		if ($secret != null) {
			if (($l->isBeheerder() === true) ||
				($l->isBeheerderDDWV() === true) ||
				($lid['ID'] ==  $l->getUserFromSession())) 
			{
				$ga = new PHPGangsta_GoogleAuthenticator();
				$lid['SECRET'] = $ga->getQRCodeGoogleUrl($lid['INLOGNAAM'], $lid['SECRET'], $app_settings['Vereniging']);
			}				
			else
			{
				$lid['SECRET'] 	= "****";
			}
		}


		if ($lid['ID'] !== $l->getUserFromSession())
		{
			if (($l->isBeheerder() === false) &&
				($l->isBeheerderDDWV() === false) &&
				($l->isInstructeur() === false) &&
				($l->isStarttoren() == false) &&
				($l->isCIMT() === false))					
			{
				if (($lid['PRIVACY'] === true) || ($privacy)) 	// dit lid heeft privacy aan, of we MOETEN privicay mask toepassen
				{				
					$lid['TELEFOON'] 		= "****";
					$lid['MOBIEL'] 			= "****";
					$lid['ADRES'] 			= "****";
					$lid['POSTCODE'] 		= "****";
					$lid['WOONPLAATS'] 		= "****";
					$lid['GEBOORTE_DATUM'] 	= "****";

					$lid['AVATAR'] 			= null;
				}

				if ($l->isStarttoren() == false)
				{
					$lid['MEDICAL'] 		= null;
					$lid['NOODNUMMER'] 		= null;
					$lid['STARTVERBOD'] 	= null;
				}
				
				$lid['OPMERKINGEN'] 	= null;
			}

			if (($l->isBeheerder() === false) &&
				($l->isBeheerderDDWV() === false) &&
				($l->isRooster() === false) &&
				($l->isInstructeur() === false) &&
				($l->isCIMT() === false))	
			{
				$lid['OPMERKINGEN'] 	= null;
			}
		}
		return $lid;
	}

	
	function specialeRol($record, $dbLid, $field)
	{
		if (array_key_exists($field, $record)) 
			return ($record[$field] == 1);

		if (isset($dbLid) && array_key_exists($field, $dbLid))
			return ($dbLid[$field] == 1);
		
		// er is geen info beschikbaar in record, en ook niet in de database. We nemen geen risico
		return true; 		
	}

	/*
	Copieer data van request naar velden van het record 
	*/
	function RequestToRecord($input)
	{
		global $app_settings;

		$record = array();
		$l = MaakObject('Login');
		$dbLid = null;

		$ikBenHetZelf = false;
		if (array_key_exists("ID", $input)) {
			$ikBenHetZelf = ($l->getUserFromSession() == $input["ID"]);

			// We halen lid op uit de database, 
			try 	// Geeft exceptie als het niet bestaat. We gaan wel door
			{
				$dbLid = $this->GetObject($input["ID"]);
			}
			catch (Exception $exception) { }
		}

		// onderstaande velden zijn beperkt aanpasbaar
		if (($l->isBeheerder()) || ($l->isBeheerderDDWV()))
		{
			$field = 'DDWV_CREW';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);		

			$field = 'ZUSTERCLUB_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');
		}

		if (($l->isBeheerder()) || ($l->isRooster()))
		{
			$field = 'LIERIST';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'LIERIST_IO';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);	

			$field = 'STARTLEIDER';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);	
		
			$field = 'SLEEPVLIEGER';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);		
			
			$field = 'GASTENVLIEGER';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);		
		}

		if (($l->isBeheerder()) || ($l->isRooster()) || $l->isCIMT())  {
			if (array_key_exists('OPMERKINGEN', $input))
			$record['OPMERKINGEN'] = $input['OPMERKINGEN']; 
		}

		if (($l->isBeheerder()) || ($l->isCIMT()))
		{
			$field = 'INSTRUCTEUR';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'STATUSTYPE_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, "Types");		
				
			$field = 'BUDDY_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, 'Leden');

            $field = 'BUDDY_ID2';
            if (array_key_exists($field, $input))
                $record[$field] = isINT($input[$field], $field, true, 'Leden');
        }
		
		if ($l->isBeheerder() == true)
		{
			$field = 'ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

			$field = 'LIDNR';
			if (array_key_exists($field, $input))
				$record[$field] = $input[$field];				

			$field = 'LIDTYPE_ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true, "Types");

			$field = 'CIMT';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'ROOSTER';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);					

			$field = 'STARTTOREN';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'DDWV_BEHEERDER';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			if ($app_settings['DemoMode'] === false)		// in demo mode mogen we het veld beheerder niet aanpassen
			{
				$field = 'BEHEERDER';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);
			}

			$field = 'RAPPORTEUR';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'STARTVERBOD';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			// url naar extern bestand
			if (array_key_exists('AVATAR', $input))
				$record['AVATAR'] = $input['AVATAR']; 	
		}

		if ($ikBenHetZelf || $l->isBeheerder() || $l->isBeheerderDDWV())
		{
			$field = 'TEGOED';
			if (array_key_exists($field, $input))
				$record[$field] = isNUM($input[$field], $field);
		}

		if ($ikBenHetZelf || ($l->isBeheerder()))
		{
			$field = 'ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

			if (array_key_exists('NAAM', $input)) {
				$record['NAAM'] = $input['NAAM']; 
			}
			elseif ((array_key_exists('VOORNAAM', $input)) && (array_key_exists('ACHTERNAAM', $input)))
			{
				$record['NAAM'] = "";

				if (array_key_exists('VOORNAAM', $input))
					$record['NAAM'] .= $input['VOORNAAM'] . " "; 

				if (array_key_exists('TUSSENVOEGSEL', $input))
					$record['NAAM'] .= $input['TUSSENVOEGSEL'] . " "; 

				if (array_key_exists('ACHTERNAAM', $input))	
					$record['NAAM'] .= $input['ACHTERNAAM']; 	
			}

			if (array_key_exists('VOORNAAM', $input))
				$record['VOORNAAM'] = $input['VOORNAAM']; 

			if (array_key_exists('TUSSENVOEGSEL', $input))
				$record['TUSSENVOEGSEL'] = $input['TUSSENVOEGSEL']; 

			if (array_key_exists('ACHTERNAAM', $input))
				$record['ACHTERNAAM'] = $input['ACHTERNAAM']; 

			if (array_key_exists('ADRES', $input))
				$record['ADRES'] = $input['ADRES']; 	
				
			if (array_key_exists('POSTCODE', $input))
				$record['POSTCODE'] = $input['POSTCODE']; 	
				
			if (array_key_exists('WOONPLAATS', $input))
				$record['WOONPLAATS'] = $input['WOONPLAATS']; 				

			if (array_key_exists('TELEFOON', $input))
				$record['TELEFOON'] = $input['TELEFOON']; 

			if (array_key_exists('MOBIEL', $input))
				$record['MOBIEL'] = $input['MOBIEL']; 

			if (array_key_exists('NOODNUMMER', $input))
				$record['NOODNUMMER'] = $input['NOODNUMMER']; 

			if (array_key_exists('EMAIL', $input))
				$record['EMAIL'] = $input['EMAIL']; 

			$field = 'CLUBBLAD_POST';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'GEBOORTE_DATUM';
			if (array_key_exists($field, $input))
				$record[$field]= isDATE($input[$field], $field, true);	

			$field = 'MEDICAL';
			if (array_key_exists($field, $input))
				$record[$field]= isDATE($input[$field], $field, true);		

			if ($app_settings['DemoMode'] === false)		// in demo mode mogen we de login naam niet aanpassen
			{
				if (array_key_exists('INLOGNAAM', $input))
					$record['INLOGNAAM'] = $input['INLOGNAAM']; 	
			}

			if (array_key_exists('SLEUTEL1', $input))
				$record['SLEUTEL1'] = $input['SLEUTEL1']; 		
				
			if (array_key_exists('SLEUTEL2', $input))
				$record['SLEUTEL2'] = $input['SLEUTEL2']; 	

			if (array_key_exists('KNVVL_LIDNUMMER', $input))
				$record['KNVVL_LIDNUMMER'] = $input['KNVVL_LIDNUMMER']; 

			if (array_key_exists('BREVET_NUMMER', $input))
				$record['BREVET_NUMMER'] = $input['BREVET_NUMMER']; 

			$ld = $l->lidData();
			if (array_key_exists('WACHTWOORD', $input))
			{
				if (strlen($input['WACHTWOORD']) > 4)
				{
					$loginnaam = (isset($input['INLOGNAAM'])) ? $input['INLOGNAAM'] : $ld->INLOGNAAM;
					$record['WACHTWOORD'] = sha1(strtolower ($loginnaam) . $input['WACHTWOORD']);
				}
				else 
				{
					if (strlen($input['WACHTWOORD']) > 0) {
						throw new Exception("406;Wachtwoord voldoet niet;");
					}
				}
			}
			if (array_key_exists('WACHTWOORD_HASH', $input))
			{
				if (strlen($input['WACHTWOORD_HASH']) > 0)
					$record['WACHTWOORD'] = $input['WACHTWOORD_HASH'];
			}			

			$field = 'PRIVACY';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);	

			// Auth kan niet uitgezet worden voor bijzondere rollen
			$isSpeciaal = false;
			$isSpeciaal =  	$this->specialeRol($record, $dbLid, 'CIMT') ||
							$this->specialeRol($record, $dbLid, 'INSTRUCTEUR') ||  
							$this->specialeRol($record, $dbLid, 'BEHEERDER');
							$this->specialeRol($record, $dbLid, 'DDWV_BEHEERDER') ||  
							$this->specialeRol($record, $dbLid, 'DDWV_CREW');
							$this->specialeRol($record, $dbLid, 'STARTTOREN') ||  
							$this->specialeRol($record, $dbLid, 'ROOSTER') ||  
							$this->specialeRol($record, $dbLid, 'RAPPORTEUR');																					
			$field = 'AUTH';
			if ($isSpeciaal == true) 
			{
				$record[$field] = 1;
			}
			else
			{
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);
			}

			$isSpeciaal = false;
			$isSpeciaal = 	$this->specialeRol($record, $dbLid, 'CIMT') || 
							$this->specialeRol($record, $dbLid, 'INSTRUCTEUR')|| 
							$this->specialeRol($record, $dbLid, 'BEHEERDER');				

			// Email van de daginfo mag alleen als je beheerder, CIMT of instructeur bent
			$field = 'EMAIL_DAGINFO';
			if ($isSpeciaal == false) 
			{
				$record[$field] = 0;
			}
			else
			{
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);
			}
		}
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

		if (isset($record['LIDTYPE_ID']))
			$retVal['LIDTYPE_ID']  = $record['LIDTYPE_ID'] * 1;

		if (isset($record['STATUSTYPE_ID']))
			$retVal['STATUSTYPE_ID']  = $record['STATUSTYPE_ID'] * 1;				
		
		if (isset($record['ZUSTERCLUB_ID']))
			$retVal['ZUSTERCLUB_ID']  = $record['ZUSTERCLUB_ID'] * 1;	

		if (isset($record['BUDDY_ID']))
			$retVal['BUDDY_ID']  = $record['BUDDY_ID'] * 1;

        if (isset($record['BUDDY_ID2']))
            $retVal['BUDDY_ID2']  = $record['BUDDY_ID2'] * 1;

        if (isset($record['TEGOED']))
			$retVal['TEGOED']  = $record['TEGOED'] * 1;	

		// booleans	
		if (isset($record['LIERIST']))
			$retVal['LIERIST']  = $record['LIERIST'] == "1" ? true : false;

		if ((isset($record['LIERIST_IO'])) || ($retVal['LIERIST'] == true))
		{ 
			if ($retVal['LIERIST'] == true) 	// als je lierist bent, ben je niet meer LIO
				$retVal['LIERIST_IO'] = false;
			else
				$retVal['LIERIST_IO']  = $record['LIERIST_IO'] == "1" ? true : false;	
		}
		if (isset($record['STARTLEIDER']))
			$retVal['STARTLEIDER']  = $record['STARTLEIDER'] == "1" ? true : false;

		if (isset($record['INSTRUCTEUR']))
			$retVal['INSTRUCTEUR']  = $record['INSTRUCTEUR'] == "1" ? true : false;

		if (isset($record['CIMT']))
			$retVal['CIMT']  = $record['CIMT'] == "1" ? true : false;

		if (isset($record['DDWV_CREW']))
			$retVal['DDWV_CREW']  = $record['DDWV_CREW'] == "1" ? true : false;
			
		if (isset($record['DDWV_BEHEERDER']))
			$retVal['DDWV_BEHEERDER']  = $record['DDWV_BEHEERDER'] == "1" ? true : false;
			
		if (isset($record['BEHEERDER']))
			$retVal['BEHEERDER']  = $record['BEHEERDER'] == "1" ? true : false;
			
		if (isset($record['STARTTOREN']))
			$retVal['STARTTOREN']  = $record['STARTTOREN'] == "1" ? true : false;
			
		if (isset($record['ROOSTER']))
			$retVal['ROOSTER']  = $record['ROOSTER'] == "1" ? true : false;

		if (isset($record['SLEEPVLIEGER']))
			$retVal['SLEEPVLIEGER']  = $record['SLEEPVLIEGER'] == "1" ? true : false;
			
		if (isset($record['CLUBBLAD_POST']))
			$retVal['CLUBBLAD_POST']  = $record['CLUBBLAD_POST'] == "1" ? true : false;

		if (isset($record['RAPPORTEUR']))
			$retVal['RAPPORTEUR']  = $record['RAPPORTEUR'] == "1" ? true : false;	
			
		if (isset($record['GASTENVLIEGER']))
			$retVal['GASTENVLIEGER']  = $record['GASTENVLIEGER'] == "1" ? true : false;					

		if (isset($record['AUTH']))
			$retVal['AUTH']  = $record['AUTH'] == "1" ? true : false;
			
		if (isset($record['STARTVERBOD']))
			$retVal['STARTVERBOD']  = $record['STARTVERBOD'] == "1" ? true : false;
			
		if (isset($record['PRIVACY']))
			$retVal['PRIVACY']  = $record['PRIVACY'] == "1" ? true : false;

		if (isset($record['PAX']))
			$retVal['PAX']  = $record['PAX'] == "1" ? true : false;

		if (isset($record['EMAIL_DAGINFO']))
			$retVal['EMAIL_DAGINFO']  = $record['EMAIL_DAGINFO'] == "1" ? true : false;	
			
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}
