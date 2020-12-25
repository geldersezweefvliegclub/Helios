<?php
	class Types extends StartAdmin
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "ref_types";
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
					`GROEP` smallint UNSIGNED NOT NULL,
					`CODE` varchar(5) DEFAULT NULL,
					`EXT_REF` varchar(25) DEFAULT NULL,
					`OMSCHRIJVING` varchar(75) NOT NULL,
					`SORTEER_VOLGORDE` tinyint UNSIGNED DEFAULT NULL,
					`READ_ONLY` tinyint UNSIGNED NOT NULL DEFAULT '0',       
					`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

					CONSTRAINT ID_PK PRIMARY KEY (ID),
						INDEX (`GROEP`), 
						INDEX (`VERWIJDERD`)
					)", $this->dbTable);
			parent::DbUitvoeren($query);

			if (isset($FillData))
			{
				$inject = "
					(101, 1, '14L',     '14L', NULL, 0),
					(102, 1, '32L',     '32L', NULL, 0),
					(103, 1, '04R',     '04R', NULL, 0),
					(104, 1, '22L',     '22L', NULL, 0),
					(105, 1, '14',      '14', NULL, 0),
					(106, 1, '12',      '12', NULL, 0),
					(107, 1, '30R',     '30R', NULL, 0),
					(108, 1, '04L',     '04L', NULL, 0),
					(109, 1, '22R',     '22R', NULL, 0),
					(201, 2, 'N',       'Noord', NULL, 0),
					(202, 2, 'NNO',     'NNO', NULL, 1),
					(203, 2, 'NO',      'Noordoost', NULL, 0),
					(204, 2, 'ONO',     'ONO', NULL, 1),
					(205, 2, 'O',       'Oost', NULL, 0),
					(206, 2, 'OZO',     'OZO', NULL, 1),
					(207, 2, 'ZO',      'Zuidoost', NULL, 0),
					(208, 2, 'ZZO',     'ZZO', NULL, 1),
					(209, 2, 'Z',       'Zuid', NULL, 0),
					(210, 2, 'ZZW',     'ZZW', NULL, 1),
					(211, 2, 'ZW',      'Zuidwest', NULL, 0),
					(212, 2, 'WZV',     'WZW', NULL, 1),
					(213, 2, 'W',       'West', NULL, 0),
					(214, 2, 'WNW',     'WNW', NULL, 1),
					(215, 2, 'NW',      'Noordwest', NULL, 0),
					(216, 2, 'NNW',     'NNW', NULL, 1),
					(301, 3,  NULL,     'Windkracht 1 (1-3 kn)', NULL, 0),
					(302, 3,  NULL,     'Windkracht 2 (4-6 kn)', NULL, 0),
					(303, 3,  NULL,     'Windkracht 3 (7-10 kn)', NULL, 0),
					(304, 3,  NULL,     'Windkracht 4 (11-15 kn)', NULL, 0),
					(305, 3,  NULL,     'Windkracht 5 (16 - 21 kn)', NULL, 0),
					(306, 3,  NULL,     'Windkracht 6 (22 - 27 kn)', NULL, 0),
					(307, 3,  NULL,     'Windkracht 7 (28 - 33 kn)', NULL, 0),
					(308, 3,  NULL,     'Windkracht 8 (34 - 40 kn)', NULL, 0),
					(309, 3,  NULL,     'Windkracht 0 (0 - 1 kn)', NULL, 0),
					(401, 4, 'DIS',     'Discus CS', 4, 0),
					(402, 4, 'LS4',     'LS 4', 3, 0),
					(403, 4, 'LS6',     'LS 6-18 w', NULL, 1),
					(404, 4, 'LS8',     'LS8', 5, 0),
					(405, 4, 'Duo',     'Duo Discus', 7, 0),
					(406, 4, 'ASK21',   'ASK 21', 1, 0),
					(407, 4, 'ASK23',   'ASK 23 B', 2, 1),
					(408, 4, 'ASG29',   'ASG-29', 6, 0),
					(409, 4, 'ARC',   	'Arcus', 6, 0),
					(501, 5, 'slp',     'Slepen', NULL, 0),
					(502, 5, 'slm',     'Slepen (sleepkist)', NULL, 1),
					(506, 5, 'zel',     'Zelfstart (zweefkist)', NULL, 0),
					(507, 5, 'tmg',     'Zelfstart (TMG)', NULL, 0),
					(508, 5, 'vfr',     'Overig motorkisten', NULL, 0),
					(550, 5, 'gezc',    'Lierstart GeZC', NULL, 0),
					(551, 5, 'cct',     'Lierstart CCT', NULL, 0),
					(552, 5, 'zcrd',    'Lierstart ZCD/ZCR', NULL, 0),
					(553, 5, 'gae',     'Lierstart GAE', NULL, 0),
					(600, 6, '0',       'Diverse (Bijvoorbeeld bedrijven- of jongerendag)', NULL, 0),
					(601, 6, '1',       'Erelid', NULL, 0),
					(602, 6, '2',       'Lid', NULL, 0),
					(603, 6, '3',       'Jeugdlid', NULL, 0),
					(606, 6, '6',       'Donateur', NULL, 0),
					(607, 6, 'zus',     'Zusterclub', NULL, 0),
					(608, 6, '8',       '5-rittenkaarthouder', NULL, 0),
					(609, 6, '9',       'Nieuw lid, nog niet verwerkt in ledenadministratie', NULL, 0),
					(610, 6,  NULL,     'Oprotkabel', NULL, 0),
					(611, 6, '9',       'Cursist', NULL, 0),
					(612, 6,  NULL,     'Penningmeester', NULL, 0),
					(625, 6, '9',       'DDWV vlieger', NULL, 0),
					(701, 7,  NULL,     'Club bedrijf', NULL, 0),
					(702, 7,  NULL,     'Kamp + DDWV', NULL, 0),
					(703, 7,  NULL,     'DDWV', NULL, 0),
					(801, 8,  NULL,     'Passagierstart (kosten voor pax)', NULL, 0),
					(802, 8,  NULL,     'Relatiestart', NULL, 0),
					(803, 8,  NULL,     'Start zusterclub', NULL, 0),
					(804, 8,  NULL,     'Oprotkabel', NULL, 0),
					(805, 8,  NULL,     'Normale GeZC start', NULL, 0),
					(806, 8,  NULL,     'Proefstart privekist eenzitter', NULL, 0),
					(807, 8,  NULL,     'Privestart', NULL, 0),
					(809, 8,  NULL,     'Instructie of checkvlucht', NULL, 0),
					(810, 8,  NULL,     'Solostart met tweezitter', NULL, 0),
					(811, 8, 'dis',     'Invliegen, Dienststart', NULL, 0),
					(812, 8,  NULL,     'Donateursstart', NULL, 0),
					(813, 8,  NULL,     '5- of 10-rittenkaarthouder', NULL, 0),
					(814, 8, 'mid',     'DDWV: Midweekvliegen', NULL, 0),
					(815, 8,  NULL,     'Sleepkist, Dienststart', NULL, 0),
					(901, 9, 'EHTL',    'Terlet', NULL, 0),
					(902, 9, 'EHDL',    'Deelen', NULL, 0),
					(903, 9, 'EHSB',    'Soesterberg', NULL, 0),

					(1001,10, 'EVO',    'Elementaire vliegopleiding', NULL, 0),
					(1002,10, 'VVO-1',  'Voortgezette vliegopleiding 1', NULL, 0),
					(1003,10, 'VVO-2',  'Overlandvliegen', NULL, 0),
					(1004,10, 'GeZC',   'GeZC vinkjeslijst', NULL, 0),

					(1550, 15, 'GeZC',  'GeZC', NULL, 0),
					(1551, 15, 'CCT',   'CCT', NULL, 0),
					(1552, 15, 'ZCRD',  'ZCD/ZCR', NULL, 0),
					(1553, 15, 'GAE',   'GAE', NULL, 0);";
				
				$query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`GROEP`, 
							`CODE`, 
							`OMSCHRIJVING`, 
							`SORTEER_VOLGORDE`, 
							`VERWIJDERD`) 
						VALUES
							%s;", $this->dbTable, $inject);
			
				parent::DbUitvoeren($query);

				$inject = "
					(1101, 11,  'Instructeur', 'INSTRUCTEUR', 0),
					(1102, 11,  'Lierist', 'LIERIST', 0),
					(1103, 11,  'Startleider', 'STARTLEIDER', 0),
					(1104, 11,  'DDWV Crew', 'DDWV_CREW', 0),
					(1105, 11,  'DDWV Beheerder', 'DDWV_BEHEERDER', 0),
					(1106, 11,  'Beheerder', 'BEHEERDER', 0),
					(1107, 11,  'Starttoren', 'STARTTOREN', 0)";

				$query = sprintf("
					INSERT INTO `%s` (
						`ID`, 
						`GROEP`, 
						`OMSCHRIJVING`, 
						`EXT_REF`, 
						`VERWIJDERD`) 
					VALUES
						%s;", $this->dbTable, $inject);
				
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
					types.*
				FROM
					`%s` `types`
				WHERE
					`types`.`VERWIJDERD` = %d
				ORDER BY 
					SORTEER_VOLGORDE, ID;";		
							
			parent::DbUitvoeren("DROP VIEW IF EXISTS types_view");							
			parent::DbUitvoeren(sprintf($query, "types_view", $this->dbTable, 0));

			parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_types_view");
			parent::DbUitvoeren(sprintf($query, "verwijderd_types_view", $this->dbTable, 1));
		}

		/*
		Haal een enkel record op uit de database
		*/		
		function GetObject($ID, $heeftVerwijderd = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Types.GetObject(%s)", $ID));	

			if ($ID == null)
				throw new Exception("406;Geen ID in aanroep;");
		
			$conditie = array();
			$conditie['ID'] = isINT($ID, "ID");

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
			$functie = "Types.GetObjects";
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
					case "GROEP" : 
						{
							$groep = isINT($value, "GROEP");
							$where .= " AND GROEP=?";	
							array_push($query_params, $groep);
							
							Debug(__FILE__, __LINE__, sprintf("%s: GROEP='%s'", $functie, $groep));
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
					`types_view` " . $where; // . $orderby;
			
			$retVal = array();

			$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
			$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);
			Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

			$query = "
				SELECT 
					%s
				FROM
					`types_view` " . $where . $orderby;

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
		function VerwijderObject($id = null, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Types.VerwijderObject('%s', %s)", $id, (($verificatie === false) ? "False" :  $verificatie)));
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($id === null)
				throw new Exception("406;Geen ID in aanroep;");
			
			isCSV($ID, "id");										
			parent::MarkeerAlsVerwijderd($id, $verificatie);
		}		

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function AddObject($TypeData)
		{
			Debug(__FILE__, __LINE__, sprintf("Types.AddObject(%s)", print_r($TypeData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
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

			if (!array_key_exists('GROEP', $TypeData))
				throw new Exception("406;Groep is verplicht;");
			
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
			Debug(__FILE__, __LINE__, sprintf("Types.UpdateObject(%s)", print_r($TypeData, true)));
			
			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
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

			$field = 'GROEP';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

			$field = 'SORTEER_VOLGORDE';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field, true);

			$field = 'READ_ONLY';
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
	}
?>