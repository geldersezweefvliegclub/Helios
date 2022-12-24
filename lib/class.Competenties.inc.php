<?php
class Competenties extends Helios
{
	function __construct() 
	{
		parent::__construct();
		$this->dbTable = "ref_competenties";
		$this->dbView = "competenties_view";
		$this->Naam = "Competenties";
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
				`VOLGORDE` smallint UNSIGNED NULL,
				`LEERFASE_ID` mediumint UNSIGNED NOT NULL,
				`BLOK_ID` mediumint UNSIGNED NULL,
				`BLOK` varchar(7) DEFAULT NULL,
				`ONDERWERP` varchar(75) NOT NULL,
				`DOCUMENTATIE` varchar(75) NULL,  
				`GELDIGHEID` tinyint UNSIGNED NOT NULL DEFAULT 0,   
				`SCORE` tinyint UNSIGNED NOT NULL DEFAULT 0, 
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LEERFASE_ID`), 
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (LEERFASE_ID) REFERENCES ref_types(ID),
					FOREIGN KEY (BLOK_ID) REFERENCES %s(ID)
				)", $this->dbTable, $this->dbTable);
		parent::DbUitvoeren($query);

		if (isset($FillData))
		{
			$inject = "
				(01,  01,  1000, NULL, '1',   'Luchtvaartwetgeving', NULL),
				(02,  02,  1000, NULL, '2',   'Menselijke prestaties ', NULL),
				(03,  03,  1000, NULL, '3',   'Meteorologie', NULL),
				(04,  04,  1000, NULL, '4',   'Communicatie', NULL),
				(05,  05,  1000, NULL, '5',   'Beginselen van het Vliegen', NULL),
				(06,  06,  1000, NULL, '6',   'Operationele Procedures ', NULL),
				(07,  07,  1000, NULL, '7',   'Vliegprestaties & Vluchtplanning', NULL),
				(08,  08,  1000, NULL, '8',   'Algemene kennis van het Luchtvaartuig', NULL),
				(09,  09,  1000, NULL, '9',   'Navigatie', NULL),
				(21,  01,  1001, NULL, '1',   'Veiligheidsbriefing', NULL),
				(22,  02,  1001, NULL, '2',   'Veldbedrijf', NULL),
				(23,  03,  1001, NULL, '3',   'Famililiarisatie met het vliegtuig', NULL),
				(24,  04,  1001, NULL, '4',   'Noodprocedures', NULL),
				(25,  05,  1001, NULL, '5',   'EVO oefening 1: Kennismakingsvlucht', NULL),
				(26,  06,  1001, NULL, '6',   'EVO oefening 2: Werking stuurorganen', NULL),
				(27,  07,  1001, NULL, '7',   'EVO oefening 3: Horizon, snelheid en trim', NULL),
				(28,  08,  1001, NULL, '8',   'EVO oefening 4: Haak- en neveneffecten', NULL),
				(29,  09,  1001, NULL, '9',   'EVO oefening 5: Rechtlijnige vlucht', NULL),
				(30,  10,  1001, NULL, '10',  'EVO oefening 6: Bochten en uitkijkprocedure', NULL),
				(31,  11,  1001, NULL, '11',  'EVO oefening 7: De lierstart (bovenste deel)', NULL),
				(32,  12,  1001, NULL, '12',  'EVO oefening 8: De lierstart (onderste deel)', NULL),
				(33,  13,  1001, NULL, '13',  'EVO oefening 9: De lierstart met zijwind', NULL),
				(34,  14,  1001, NULL, '14',  'EVO oefening 10: Het standaardcircuit', NULL),
				(35,  15,  1001, NULL, '15',  'EVO oefening 11: Rechtlijnige vlucht en koersvlucht met zijwind',  NULL),
				(36,  16,  1001, NULL, '16',  'EVO oefening 12: Het standaardcircuit met zijwind', NULL),
				(37,  17,  1001, NULL, '17',  'EVO oefening 13: De landing', NULL),
				(38,  18,  1001, NULL, '18',  'EVO oefening 14: Veilig thermiekvliegen', NULL),
				(39,  19,  1001, NULL, '19',  'EVO oefening 15: Oefening negatieve G-Krachten', NULL),
				(40,  20,  1001, NULL, '20',  'EVO oefening 16: Symmetrisch overtrek', NULL),
				(41,  21,  1001, NULL, '21',  'EVO oefening 17: Vrille (tolvlucht)', NULL),
				(42,  22,  1001, NULL, '22',  'EVO oefening 18: Spiraalduik', NULL),
				(43,  23,  1001, NULL, '23',  'EVO oefening 19: Oefening kabelbreuk met verkort circuit', NULL),
				(44,  26,  1001, NULL, '24',  'EVO oefening 20: Laag circuit - geimproviseerd circuit', NULL),
				(45,  25,  1001, NULL, '25',  'EVO oefening 21: De sleepstart', NULL),
				(46,  26,  1001, NULL, '26',  'EVO oefening 22: Dalend slepen', NULL),
				(47,  29,  1001, NULL, '27',  'EVO oefening 23: De sleepstart met zijwind', NULL),
				(48,  28,  1001, NULL, '28',  'EVO oefening 24: De eerste solovlucht', NULL),
				(49,  29,  1002, NULL, '29',  'VVO-1 Oefeningen', NULL),
				(50,  30,  1003, NULL, '30',  'Vluchtvoorbereiding', NULL),
				(51,  31,  1003, NULL, '31',  'Overlandvlucht', NULL),

				(101, 01,  1001,  21, '1.1',   'Organisatiestructuur',  'EVO 1.1 + Briefing door zweefvliegvereniging'),
				(102, 02,  1001,  21, '1.2',   'Veiligheidsregels op het veld - uitkijkprocedure',  'EVO 1.0'),
				(103, 03,  1001,  21, '1.3',   'Veiligheidsregels in de lucht',  'EVO 4.0'),
				(104, 04,  1001,  21, '1.4',   'Specifieke lokale veiligheidsprocedures',  'Briefing door zweefvliegvereniging'),
				(105, 05,  1001,  21, '1.5',   'Veiligheidsregels m.b.t. het grondtransport van vliegtuigen',  'EVO 1.2 / VVO1-1.9'),
				(106, 06,  1001,  21, '1.6',   'Noodzakelijke documenten',  'EVO 1.8'),
				(107, 07,  1001,  21, '1.7',   'Het verloop van een vliegdag',  'Briefing door zweefvliegvereniging'),
				(108, 08,  1001,  21, '1.8',   'Het verloop van de opleiding',  'Briefing door zweefvliegvereniging'),
				(109, 09,  1001,  22, '2.1',   'Procedure aanhaken en tiplopen - afbreken start',  'EVO 1.3'),
				(110, 10,  1001,  22, '2.2',   'Kabels uitrijden',  'EVO 1.4 / VVO1-1.6'),
				(111, 11,  1001,  22, '2.3',   'Tijdschrijven en seinen aan de lier',  'EVO 1.5'),
				(112, 12,  1001,  22, '2.4',   'Het weer en minimale zichtomstandigheden',  'EVO 1.6 / VVO1-1.4'),
				(113, 13,  1001,  22, '2.5',   'I\'m safe',  'EVO 1.7 / VVO 1-1.1 / 1.3 / 1.5'),
				(114, 14,  1001,  23, '3.1',   'Karakteristieken van het vliegtuig',  'EVO 1.9'),
				(115, 15,  1001,  23, '3.2',   'Cockpit, instrumenten en uitrusting',  'EVO 1.9B'),
				(116, 16,  1001,  23, '3.3',   'Stuurorganen: knuppel, pedalen, remkleppen, trim, flaps',  'EVO 1.9A, 4.2'),
				(117, 17,  1001,  23, '3.4',   'Ontkoppelmechanisme en landingsgestel',  'EVO 4'),
				(118, 18,  1001,  23, '3.5',   'Checks, procedures en controles',  'EVO 4.2'),
				(119, 19,  1001,  23, '3.6',   'Gebruik radio en transponder',  'VVO 1 2.12'),
				(120, 20,  1001,  24, '4.1',   'Gebruik van parachutes',  'VVO1-2.14'),
				(121, 21,  1001,  24, '4.2',   'Te nemen actie',  'VVO1-2.14'),
				(122, 22,  1001,  24, '4.3',   'Uitstapprocedure',  'VVO1-2.14'),
				(123, 23,  1001,  25, '5.1',   'Kennismakingsvlucht - massa en zwaartepunt',  'EVO 1.9 / 2 / 4.1'),
				(124, 24,  1001,  25, '5.2',   'Instappen - riemen - bereikbaarheid stuurorganen',  'EVO 4.1'),
				(125, 25,  1001,  25, '5.3',   'Cockpit-check',  'EVO 4.2'),
				(126, 26,  1001,  25, '5.4',   'Lokale kenmerken - oefengebied en sectoren',  'Uitleg door instructeur'),
				(127, 27,  1001,  25, '5.5',   'Uitwijkregels',  'EVO 4.0'),
				(128, 28,  1001,  26, '6.1',   'Werking stuurorganen',  'EVO 4.2'),
				(129, 29,  1001,  27, '7.1',   'Snelheid, horizon en trim',  'EVO 4.3'),
				(130, 30,  1001,  28, '8.1',   'Demonstratie haak- en neveneffecten',  'EVO 4.4'),
				(131, 31,  1001,  29, '9.1',   'Rechtuit vliegen',  'EVO 4.5'),
				(132, 32,  1001,  30,'10.1',  'Normale bochten - uitkijkprocedure - piefje',  'EVO 4.7 / VVO1-1.2'),
				(133, 33,  1001,  31,'11.1',  'Normale lierstart (bo, bovenste deel)',  'EVO 4.7 / VVO1-1.2'),
				(134, 34,  1001,  31,'11.2',  'Ontkoppelprocedure',  'EVO 4.11'),
				(135, 35,  1001,  32,'12.1',  'Normale lierstart (on, onderste deel)',  'EVO 4.7 / VVO1-1.2'),
				(136, 36,  1001,  32,'12.2',  'Liertekens bij de hoge of te lage liersnelheid',  'EVO 4.11'),
				(137, 37,  1001,  33,'13.1',  'Lierstart met zijwind',  'EVO 4.12'),
				(138, 38,  1001,  34,'14.1',  'Normaal standaardcircuit',  'EVO 4.9A'),
				(139, 39,  1001,  35,'15.1',  'Rechtlijnige vlucht bij zijwind',  'EVO 4.9A'),
				(140, 40,  1001,  36,'16.1',  'Normaal standaardcircuit met zijwind',  'EVO 4.9A'),
				(141, 41,  1001,  37,'17.1',  'Landing',  'EVO 4.15'),
				(142, 42,  1001,  37,'17.2',  'Landing met zijwind',  'EVO 4.16'),
				(143, 43,  1001,  37,'17.3',  'Landing met harde wind',  'EVO 4.17'),
				(144, 44,  1001,  38,'18.1',  'Invoegen',  'EVO 4.19C'),
				(145, 45,  1001,  38,'18.2',  'Positie ten opzichte van andere vliegtuigen handhaven',  'EVO 4.19C'),
				(146, 46,  1001,  38,'18.3',  'Uitvoegen',  'EVO 4.19C'),
				(147, 47,  1001,  38,'18.4',  'Terugkeer naar het veld',  'EVO 4.19C'),
				(148, 48,  1001,  39,'19.1',  'Oefening negatieve G-krachten',  ''),
				(149, 49,  1001,  40,'20.1',  'Herkennen en herstel van een symmetrische overtrek',  'EVO 4.18'),
				(150, 50,  1001,  40,'20.2',  'Overtrek in landingsconfiguratie',  'EVO 4.18'),
				(151, 51,  1001,  40,'20.3',  'Overtrek in bocht',  'EVO 4.19A'),
				(152, 52,  1001,  41,'21.1',  'Herkennen en herstel van een vrille (to, tolvlucht)',  'EVO 4.20'),
				(153, 53,  1001,  42,'22.1',  'Herkennen en herstel van een spiraalduik',  'EVO 4.20'),
				(154, 54,  1001,  43,'23.1',  'Oefening kabelbreuk',  'EVO 4.21'),
				(155, 55,  1001,  44,'24.1',  'Oefening geimproviseerd circuit',  'EVO 4.23'),
				(156, 56,  1001,  45,'25.1',  'Normale sleepstart inclusief herstel extreme posities',  'EVO 4.11'),
				(157, 57,  1001,  45,'25.2',  'Ontkoppelprocedure',  'EVO 4.11'),
				(158, 58,  1001,  46,'26.1',  'Dalend slepen',  'EVO 4.22'),
				(159, 59,  1001,  47,'27.1',  'Sleepstart met zijwind (op, opsturen)',  'EVO 4.12'),
				(160, 60,  1001,  48,'28.1',  'Eerste solovlucht',  'EVO 4.24'),
				(161, 61,  1002,  49,'29.1',  'Vliegen volgens EVO-standaard',  'Beoordeling door instructeur'),
				(162, 62,  1002,  49,'29.2',  'Langzaam - snel vliegen, overtrek',  'EVO 4.0 / 4.5 / 4.6 - VVO1-2.2'),
				(163, 63,  1002,  49,'29.3A', 'Steile bochten',  'VVO1-2.3'),
				(164, 64,  1002,  49,'29.3B', 'Steile wisselbochten',  'VVO1-2.3'),
				(165, 65,  1002,  49,'29.4',  'Tolvlucht - zelfstandig inzetten en herstel',  'VVO1-2.4'),
				(166, 66,  1002,  49,'29.5',  'Spiraalduik - zelfstandig inzetten en herstel',  'VVO1-2.3'),
				(167, 67,  1002,  49,'29.6',  'Veilig thermiekvliegen',  'VVO1-2.9'),
				(168, 68,  1002,  49,'29.7',  'Doellanden',  'VVO1-2.5'),
				(169, 69,  1002,  49,'29.8',  'Doellanden met zijwind',  'VVO1-2.6'),
				(170, 70,  1002,  49,'29.9',  'Slipvlucht en  nadering',  'VVO1-2.7'),
				(171, 71,  1002,  49,'29.1',  'Slipvlucht met remkleppen',  'VVO1-2.8'),
				(172, 72,  1002,  49,'29.11', 'Vliegen met afgedekte instrumenten',  'VVO1-2.10'),
				(173, 73,  1002,  49,'29.12', 'Mac Cready vliegen',  'VVO1-5.1 / 5.2 / 5.3'),
				(174, 74,  1002,  49,'29.13', 'Aangepast (ge, geimproviseerd circuit)',  'EVO 4.23'),
				(175, 75,  1002,  49,'29.14', 'Overlandcircuit',  'VVO2-4.7'),
				(176, 76,  1002,  49,'29.15', 'Dagelijkse inspectie - boordpapieren',  'VVO1-2.13 / vliegtuighandboek'),
				(177, 77,  1002,  49,'29.16', 'Gebruik radio',  'VVO1-2.12'),
				(178, 78,  1003,  50,'30.1',  'Dagelijkse inspectie inclusief controle boorddocumenten',  'VVO1-2.13 / Vliegtuighandboek'),
				(179, 79,  1003,  50,'30.2',  'Briefing weersomstandigheden',  'Briefing door zweefvliegvereniging'),
				(180, 80,  1003,  50,'30.3',  'Briefing luchtruimbeperkingen - notams',  'Briefing door zweefvliegvereniging'),
				(181, 81,  1003,  50,'30.4',  'Vluchtvoorbereiding',  'VVO2-4.2'),
				(182, 82,  1003,  50,'30.5',  'Montage - demontage / aanhangers',  'VVO2-4.1'),
				(183, 83,  1003,  50,'30.6',  'Snelheidspolaire / MacCready / Sollfahrt',  'VVO2-2.2'),
				(184, 84,  1003,  50,'30.7',  'Final glide',  'VVO2-2.4'),
				(185, 85,  1003,  50,'30.8',  'Overlandnavigatie - procedure \"weg kwijt\"',  'VVO2-4.5'),
				(186, 86,  1003,  50,'30.9',  'Overland veldkeuze',  'VVO2-4.6'),
				(187, 87,  1003,  50,'30.1',  'Procedure buitenlanding',  'VVO2-4.7'),
				(188, 88,  1003,  50,'30.11', 'Het vliegen met introduces.',  'VVO2- 9.2'),
				(189, 89,  1003,  51,'31.1',  'Overlandvlucht',  'Alle voorgaande oefeningen'),
				
				(250, 01,  1004,  NULL, NULL,  'Kruisjeslijst - lokaal',  NULL),
				(251, 02,  1004,  250,  NULL,  'ASK21',  NULL),
				(252, 03,  1004,  250,  NULL,  'LS4',  NULL),
				(253, 04,  1004,  250,  NULL,  'Discus',  NULL),
				(254, 05,  1004,  250,  NULL,  'LS8',  NULL),
				(255, 06,  1004,  250,  NULL,  'Duo Discus',  NULL),
				(256, 07,  1004,  250,  NULL,  'ASG-29',  NULL),
				(257, 08,  1004,  250,  NULL,  'Arcus',  NULL),	

				(260, 01,  1004,  NULL, NULL,  'Kruisjeslijst - overland',  NULL),
				(261, 02,  1004,  260,  NULL,  'ASK21',  NULL),
				(262, 03,  1004,  260,  NULL,  'LS4',  NULL),
				(263, 04,  1004,  260,  NULL,  'Discus',  NULL),
				(264, 05,  1004,  260,  NULL,  'LS8',  NULL),
				(265, 06,  1004,  260,  NULL,  'Duo Discus',  NULL),
				(266, 07,  1004,  260,  NULL,  'ASG-29',  NULL),	
				(267, 08,  1004,  260,  NULL,  'Arcus',  NULL),		
				
				(270, 01,  1004,  NULL, NULL,  'Aantekeningen',  NULL),
				(271, 04,  1004,  270,  NULL,  'Passagiers vliegen',  NULL),
				(272, 01,  1004,  270,  NULL,  'Lieren',  NULL),
				(272, 02,  1004,  270,  NULL,  'Slepen',  NULL),
				(272, 03,  1004,  270,  NULL,  'Zelfstart',  NULL),
				(272, 05,  1004,  270,  NULL,  'Kunstvliegen',  NULL),					

				(280, 01,  1004,  NULL, NULL,  'Overige bevoegdheden',  NULL),
				(281, 02,  1004,  280,  NULL,  'Tiplopen',  'Zie EVO 1.3'),
				(282, 03,  1004,  280,  NULL,  'Lichtgeven + tijdschrijven',  'Zie EVO 1.3'),
				(283, 04,  1004,  280,  NULL,  'Kabels rijden',  'Zie EVO 1.4 / VVO1-1.6 / 16jr + Toestemming bestuur of 18jr'),
				(284, 05,  1004,  280,  NULL,  'Lieren',  'Min 18 jr. + Lierexamen positief afgerond'),
				(285, 06,  1004,  280,  NULL,  'Lier-instructeur',  'Lierist + aangewezen door Comm. Rollend'),
				(286, 07,  1004,  280,  NULL,  'Turbogebruik XLT',  'Let op! Jaarlijkse vragenlijst ook noodzakelijk!'),
				(287, 08,  1004,  280,  NULL,  'DDWV-Vliegen',  'Toestemming van bestuur'),
				(288, 09,  1004,  280,  NULL,  '2-zitter overland',  'Toestemming van bestuur'),
				(289, 07,  1004,  280,  NULL,  'Turboinstructeur',  NULL), 
				
				(300, 01,  1004,  NULL, NULL,  'Jaarchecks',  NULL),
				
				(301, 02,  1004,  300,  NULL,  '2020',  NULL),
				(302, 02,  1004,  301,  NULL,  'Checkvlucht',  NULL),
				(303, 02,  1004,  301,  NULL,  'XLT vragenlijst',  NULL),

				(304, 02,  1004,  300,  NULL,  '2021',  NULL),
				(305, 02,  1004,  304,  NULL,  'Checkvlucht',  NULL),
				(306, 02,  1004,  304,  NULL,  'XLT vragenlijst',  NULL),
				(307, 02,  1004,  304,  NULL,  'Arcus vragenlijst',  NULL),

				(311, 02,  1004,  300,  NULL,  '2022',  NULL),
				(312, 02,  1004,  311,  NULL,  'Checkvlucht',  NULL),
				(313, 02,  1004,  311,  NULL,  'XLT vragenlijst',  NULL),
				(314, 02,  1004,  311,  NULL,  'Arcus vragenlijst',  NULL)";

			$query = sprintf("
					INSERT INTO `%s` (
						`ID`, 
						`VOLGORDE`,
						`LEERFASE_ID`, 
						`BLOK_ID`, 
						`BLOK`, 
						`ONDERWERP`, 
						`DOCUMENTATIE`) 
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
				c.*,
				`t`.`OMSCHRIJVING` AS `LEERFASE`
			FROM
				`%s` `c`    
				LEFT JOIN `ref_types` `t` ON (`c`.`LEERFASE_ID` = `t`.`ID`)
			WHERE
				`c`.`VERWIJDERD` = %d
			ORDER BY 
				LEERFASE_ID, BLOK_ID, VOLGORDE, ID;";				
		
		parent::DbUitvoeren("DROP VIEW IF EXISTS competenties_view");							
		parent::DbUitvoeren(sprintf($query, "competenties_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_competenties_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_competenties_view", $this->dbTable, 1));	
	}

	/*
	Haal een enkel record op uit de database
	*/		
	function GetObject($ID)
	{
		$functie = "Competenties.GetObject";
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

		$functie = "Competenties.GetObjects";
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
				case "LEERFASE_ID" : 
					{
						$leerfaseID = isINT($value, "LEERFASE_ID");
						$where .= " AND LEERFASE_ID=?";	
						array_push($query_params, $leerfaseID);
						
						Debug(__FILE__, __LINE__, sprintf("%s: LEERFASE_ID='%s'", $functie, $leerfaseID));
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
				`####competenties_view` " . $where; // . $orderby;
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
	Haal de progressie kaart op, maar bouw meteen eem boom structuur, dat scheelt werk voor de client. 
	De data is gelijk aan ProgressieKaart
	*/
	function CompetentiesBoom($params)
	{
		$functie = "Competenties.CompetentieBoom";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));	

		include_once('lib/class.Boom.inc.php');
		return Boom::bouwBoom($this->GetObjects($params));		// Ophalen data en boom structuur maken
	}

	/*
	Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
	Het veld VERWIJDERD wordt op "1" gezet.
	*/
	function VerwijderObject($id = null, $verificatie = true)
	{
		$functie = "Competenties.VerwijderObject";
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
		$functie = "Competenties.HerstelObject";
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
	function AddObject($CompetentieData)
	{
		$functie = "Competenties.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($CompetentieData, true)));
		
		if (!$this->heeftDataToegang(null, false))
			throw new Exception("401;Geen schrijfrechten;");
		
		if ($CompetentieData == null)
			throw new Exception("406;Type data moet ingevuld zijn;");
				
		if (array_key_exists('ID', $CompetentieData))
		{
			$id = isINT($CompetentieData['ID'], "ID");
			
			// ID is opgegeven, maar bestaat record?
			try 	// Als record niet bestaat, krijgen we een exception
			{					
				$this->GetObject($id);
			}
			catch (Exception $e) {}			

			if (parent::NumRows() > 0)
				throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
		}

		if (!array_key_exists('LEERFASE_ID', $CompetentieData))
			throw new Exception("406;LEERFASE_ID is verplicht;");
		
		if (!array_key_exists('ONDERWERP', $CompetentieData))
			throw new Exception("406;ONDERWERP is verplicht;");
		
		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($CompetentieData);

		$id = parent::DbToevoegen($t);
		Debug(__FILE__, __LINE__, sprintf("competentie toegevoegd id=%d", $id));

		return $this->GetObject($id);
	}

	/*
	Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/		
	function UpdateObject($CompetentieData)
	{
		$functie = "Competenties.UpdateObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($CompetentieData, true)));
		
		if (!$this->heeftDataToegang(null, false))
			throw new Exception("401;Geen schrijfrechten;");

		if ($CompetentieData == null)
			throw new Exception("406;Type data moet ingevuld zijn;");
				
		if (!array_key_exists('ID', $CompetentieData))
			throw new Exception("406;ID moet ingevuld zijn;");
		
		$id = isINT($CompetentieData['ID'], "ID");
			
		// Neem data over uit aanvraag
		$t = $this->RequestToRecord($CompetentieData);

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

		$field = 'LEERFASE_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, "Types");

		$field = 'BLOK_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true, "Competenties");			

		$field = 'VOLGORDE';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, true);

		if (array_key_exists('ONDERWERP', $input))
			$record['ONDERWERP'] = $input['ONDERWERP']; 

		if (array_key_exists('DOCUMENTATIE', $input))
			$record['DOCUMENTATIE'] = $input['DOCUMENTATIE']; 

		if (array_key_exists('BLOK', $input))
			$record['BLOK'] = $input['BLOK'];

        $field = 'GELDIGHEID';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);

        $field = 'SCORE';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);

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

		if (isset($record['VOLGORDE']))
			$retVal['VOLGORDE']  = $record['VOLGORDE'] * 1;
		
		if (isset($record['LEERFASE_ID']))
			$retVal['LEERFASE_ID']  = $record['LEERFASE_ID'] * 1;	

		if (isset($record['BLOK_ID']))
			$retVal['BLOK_ID']  = $record['BLOK_ID'] * 1;	
			
		// booleans
        if (isset($record['GELDIGHEID']))
            $retVal['GELDIGHEID']  = $record['GELDIGHEID'] == "1" ? true : false;

        if (isset($record['SCORE']))
            $retVal['SCORE']  = $record['SCORE'] == "1" ? true : false;

		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		return $retVal;
	}
}
