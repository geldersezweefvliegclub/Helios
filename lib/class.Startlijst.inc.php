<?php

class Startlijst extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "oper_startlijst";
        $this->dbView = "startlijst_view";
        $this->Naam = "Startlijst";
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

        $query = sprintf("
			CREATE TABLE `%s` (
				`ID` mediumint  UNSIGNED NOT NULL AUTO_INCREMENT,
				`DATUM` date NOT NULL,
				`DAGNUMMER` tinyint UNSIGNED NOT NULL DEFAULT 0,
				`VLIEGTUIG_ID` mediumint UNSIGNED NOT NULL,
				`STARTTIJD` time  DEFAULT NULL,
				`LANDINGSTIJD` time DEFAULT NULL,
				`STARTMETHODE_ID` mediumint UNSIGNED DEFAULT NULL,
				`VLIEGER_ID` mediumint UNSIGNED DEFAULT NULL,
				`INZITTENDE_ID` mediumint UNSIGNED DEFAULT NULL,
				`VLIEGERNAAM` varchar(50) DEFAULT NULL,
				`INZITTENDENAAM` varchar(50) DEFAULT NULL,
				`SLEEPKIST_ID` mediumint UNSIGNED DEFAULT NULL,
				`SLEEP_HOOGTE` smallint UNSIGNED DEFAULT NULL,
				`VELD_ID` mediumint UNSIGNED DEFAULT NULL,
				`BAAN_ID` mediumint UNSIGNED DEFAULT NULL,
				`OPMERKINGEN` text DEFAULT NULL,
				`EXTERNAL_ID` text DEFAULT NULL,
				`PAX` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`CHECKSTART` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`INSTRUCTIEVLUCHT` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`), 
					INDEX (`VLIEGTUIG_ID`), 
					INDEX (`VLIEGER_ID`), 
					INDEX (`INZITTENDE_ID`), 
					INDEX (`VERWIJDERD`),

				FOREIGN KEY (VLIEGTUIG_ID) REFERENCES ref_vliegtuigen(ID),		
				FOREIGN KEY (STARTMETHODE_ID) REFERENCES ref_types(ID),	
				FOREIGN KEY (VLIEGER_ID) REFERENCES ref_leden(ID),	
				FOREIGN KEY (INZITTENDE_ID) REFERENCES ref_leden(ID),	
				FOREIGN KEY (SLEEPKIST_ID) REFERENCES ref_vliegtuigen(ID),	
				FOREIGN KEY (VELD_ID) REFERENCES ref_types(ID),
				FOREIGN KEY (BAAN_ID) REFERENCES ref_types(ID)
			)", $this->dbTable);
        parent::DbUitvoeren($query);

        if (isset($FillData)) {
            $inject = array(
                "'1',  	 '@@@@+0', '1', '200', '10:12:00', '10:27:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'2',  	 '@@@@+0', '2', '218', '11:59:00', '13:07:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'3',  	 '@@@@+0', '3', '211', '14:21:00', '14:30:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'4',  	 '@@@@+2', '1', '201', '14:59:00', '15:12:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'5',  	 '@@@@+2', '2', '200', '17:02:00', '17:53:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'6',  	 '@@@@+3', '1', '211', '12:07:00', '12:34:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'7',  	 '@@@@+3', '2', '200', '14:50:00', '18:15:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'8',  	 '@@@@+4', '1', '217', '10:12:00', '18:45:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'9',  	 '@@@@+5', '1', '200', '10:42:00', '11:27:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'10', 	 '@@@@+5', '2', '200', '11:32:00', '11:36:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'11',   '@@@@+5', '3', '200', '12:21:00', '12:45:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'12',   '@@@@+5', '4', '200', '13:59:00', '14:54:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'13',   '@@@@+6', '1', '200', '13:07:00', '18:01:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'14',   '@@@@+7', '1', '200', '10:22:00', '11:27:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'15', 	 '@@@@+7', '2', '200', '12:31:00', '14:38:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'16',   '@@@@+7', '3', '200', '15:41:00', '15:55:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",
                "'17',   '@@@@+7', '4', '200', '16:59:00', '17:04:00', '550', '10858',  NULL  , NULL,NULL,'Vorig jaar'",

                "'20',   '****', '1', '200', '08:22:00', '09:27:00', '550', '10858',  NULL  , NULL,NULL,'< 3 maanden geleden'",
                "'21', 	 '****', '2', '200', '10:51:00', '14:38:00', '550', '10858',  NULL  , NULL,NULL,'< 3 maanden geleden'",
                "'22',   '****', '3', '200', '14:48:00', '15:55:00', '550', '10858',  NULL  , NULL,NULL,'< 3 maanden geleden'",
                "'23',   '****', '4', '200', '16:59:00', '17:04:00', '550', '10858',  NULL  , NULL,NULL,'< 3 maanden geleden'",

                "'30',   '####-05-01', '1', '200', '10:02:00', '10:09:00', '550', '10265', '10115', NULL,NULL,'####-05'",
                "'31',   '####-05-01', '2', '201', '10:29:00', '10:40:00', '550', '10265',  NULL  , NULL,NULL,'####-05'",
                "'32',   '####-05-01', '3', '200', '10:25:00', '10:35:00', '550', '10115', '10265', NULL,NULL,'####-05'",
                "'33',   '####-05-01', '4', '200', '10:59:00', '11:12:00', '550', '10855', '10115', NULL,NULL,'####-05'",
                "'34',   '####-05-01', '5', '208', '12:02:00', '12:22:00', '550', '10855', '10265', NULL,NULL,'####-05'",
                "'35',   '####-05-01', '6', '211', '16:00:00', '17:30:00', '550', '10855',  NULL  , NULL,NULL,'####-05'",
                "'36',   '####-05-01', '7', '200', '19:04:00',  NULL     , '550', '10265', '10855', NULL,NULL,'####-05'",
                "'37',   '####-05-01', '8', '211', '11:45:00', '19:20:00', '550', '10115',  NULL  , NULL,NULL,'####-05'",

                "'40',  '####-05-02', '1', '211', '13:22:00', '14:00:00', '550', '10001',  NULL  , NULL,NULL,'####-05'",
                "'41',  '####-05-02', '2', '218', '10:27:00', '11:35:00', '550', '10001', '10470', NULL,NULL,'####-05'",
                "'42',  '####-05-02', '3', '218', '13:33:00', '17:42:00', '550', '10470',  NULL  , NULL,NULL,'####-05'",
                "'43',  '####-05-02', '4', '211', '11:30:00', '11:39:00', '550',  NULL  ,  NULL  , NULL,NULL,'####-05'",

                "'50',  '####-05-03', '1', '217', '11:58:00', '12:04:00', '550', '10213',  NULL  , NULL,NULL, '####-05 chute ging open vlieger ontkoppeld'",
                "'51',  '####-05-03', '2', '215', '11:45:00', '17:46:00', '550', '10213',  NULL  , NULL,NULL, '####-05'",

                "'60',  '####-05-04', '1', '201', '10:00:00', '11:10:00', '550', '10063',  NULL  , NULL,NULL,'####-05'",
                "'61',  '####-05-04', '2', '201', '12:02:00', '12:08:00', '550', '10063',  NULL  , NULL,NULL,'####-05'",
                "'62',  '####-05-04', '3', '200', '12:16:00', '14:27:00', '550', '10858',  NULL  , NULL,NULL,'####-05'",
                "'63',  '####-05-04', '4', '216', '12:28:00', '12:32:00', '550', '10632',  NULL  , NULL,NULL,'####-05'",
                "'64',  '####-05-04', '5', '201', '14:22:00', '14:30:00', '550', '10063',  NULL  , NULL,NULL,'####-05'",
                "'65',  '####-05-04', '6', '201', '15:25:00', '15:42:00', '550', '10063',  NULL  , NULL,NULL,NULL",
                "'66',  '####-05-04', '7', '200',  NULL     ,  NULL     , '550',  NULL  , '10858', 'Marius de Bok' ,NULL,'####-05'",
                "'67',  '####-05-04', '8', '200',  NULL     ,  NULL     , '550',  NULL  ,  NULL  , 'Orm de Aap', 'Mister Maraboe','####-05'");


            $date = new DateTime();            // vandaag
            $date->modify('-364 day');        // 1 jaar geleden (52*7)
            $inject = str_replace("@@@@+0", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 1 dag
            $inject = str_replace("@@@@+1", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 2 dagen
            $inject = str_replace("@@@@+2", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 3 dagen
            $inject = str_replace("@@@@+3", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 4 dagen
            $inject = str_replace("@@@@+4", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 5 dagen
            $inject = str_replace("@@@@+5", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 6 dagen
            $inject = str_replace("@@@@+6", date_format($date, 'Y-m-d'), $inject);
            $date->modify('+1 day');        // 1 jaar geleden + 7 dagen
            $inject = str_replace("@@@@+7", date_format($date, 'Y-m-d'), $inject);

            $date = new DateTime();            // vandaag
            if ($date->format("m") == "5")    // Mei is ongelukkige maand, we hebben dan namelijk al test data
                $date->modify('-42 day');    // 1,5 maand geleden (6*7), zitten nog binnen de 3 maanden (recency) van huidige datum

            $inject = str_replace("****", date_format($date, 'Y-m-d'), $inject);

            // Starts in dit jaar
            $inject = str_replace("####", strval(date("Y")), $inject);

            $i = 0;
            foreach ($inject as $record) {
                $query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`DAGNUMMER`, 
							`VLIEGTUIG_ID`, 
							`STARTTIJD`, 
							`LANDINGSTIJD`, 
							`STARTMETHODE_ID`, 
							`VLIEGER_ID`, 
							`INZITTENDE_ID`, 
							`VLIEGERNAAM`, 
							`INZITTENDENAAM`, 
							`OPMERKINGEN`)
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

        $query = "CREATE VIEW `%s` AS
			SELECT 
				`sl`.`ID`,
				`sl`.`DATUM`,
				`sl`.`DAGNUMMER`,
				`sl`.`VLIEGTUIG_ID`,
				time_format(`sl`.`STARTTIJD`,'%%H:%%i') AS `STARTTIJD`,
				time_format(`sl`.`LANDINGSTIJD`,'%%H:%%i') AS `LANDINGSTIJD`,
				`sl`.`STARTMETHODE_ID`,
				`sl`.`VLIEGER_ID`,
				`sl`.`INZITTENDE_ID`,
				`sl`.`VLIEGERNAAM`,
				`sl`.`INZITTENDENAAM`,
				`sl`.`SLEEPKIST_ID`,
				CONCAT(IFNULL(`sv`.`REGISTRATIE`,''),' (',IFNULL(`sv`.`CALLSIGN`,''),')') AS `SLEEPKIST`,
				`sl`.`SLEEP_HOOGTE`,
				`sl`.`PAX`,
				`sl`.`CHECKSTART`,
				`sl`.`INSTRUCTIEVLUCHT`,
				`sl`.`VELD_ID`,
				`sl`.`BAAN_ID`,
				`vt`.`OMSCHRIJVING` AS `VLIEGTUIGTYPE`,
				`sl`.`OPMERKINGEN`,
				`sl`.`EXTERNAL_ID`,
				`sl`.`VERWIJDERD`,
				`sl`.`LAATSTE_AANPASSING`,

				`v`.`REGISTRATIE`   AS `REGISTRATIE`,
				`v`.`CALLSIGN`      AS `CALLSIGN`,
				`v`.`CLUBKIST`      AS `CLUBKIST`,
				`v`.`TYPE_ID`      	AS `VLIEGTUIG_TYPE_ID`,
				CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`,
				CASE WHEN `sl`.`DATUM` = cast(current_timestamp() AS date) 
					THEN 
						time_format(timediff(ifnull(`sl`.`LANDINGSTIJD`,curtime()),`sl`.`STARTTIJD`),'%%H:%%i') 
					ELSE 
						CASE WHEN `sl`.`LANDINGSTIJD` IS NOT NULL
							THEN 
								time_format(timediff(`sl`.`LANDINGSTIJD`,`sl`.`STARTTIJD`),'%%H:%%i') 
							ELSE 
								'' 
							END 
					END               			AS `DUUR`,
				`vl`.`NAAM`           			AS `VLIEGERNAAM_LID`,
				`il`.`NAAM`           			AS `INZITTENDENAAM_LID`,
				`vl`.`LIDTYPE_ID`     			AS `VLIEGER_LIDTYPE_ID`,
				`il`.`LIDTYPE_ID`     			AS `INZITTENDE_LIDTYPE_ID`,
				`di`.`DDWV` OR `rooster`.`DDWV` AS `DDWV`,
				`sm`.`OMSCHRIJVING`   			AS `STARTMETHODE`,
				`veld`.`OMSCHRIJVING` 			AS `VELD`, 
				`baan`.`CODE` 					AS `BAAN` 
			FROM 
				`%s` `sl` 
				LEFT JOIN `ref_leden`       `vl`      ON `sl`.`VLIEGER_ID` = `vl`.`ID` 
				LEFT JOIN `ref_leden`       `il`      ON `sl`.`INZITTENDE_ID` = `il`.`ID` 
				LEFT JOIN `ref_vliegtuigen` `v`       ON `sl`.`VLIEGTUIG_ID` = `v`.`ID` 
				LEFT JOIN `ref_vliegtuigen` `sv`      ON `sl`.`SLEEPKIST_ID` = `sv`.`ID` 
				LEFT JOIN `ref_types`       `veld`    ON `sl`.`VELD_ID` = `veld`.`ID` 
				LEFT JOIN `ref_types`       `vt`      ON `v`.`TYPE_ID` = `vt`.`ID` 
				LEFT JOIN `ref_types`       `baan`    ON `sl`.`BAAN_ID` = `baan`.`ID` 
				LEFT JOIN `ref_types`       `sm`      ON `sl`.`STARTMETHODE_ID` = `sm`.`ID` 
				LEFT JOIN `oper_daginfo`    `di`      ON `sl`.`DATUM` = `di`.`DATUM` 
				LEFT JOIN `oper_rooster`    `rooster` ON `sl`.`DATUM` = `rooster`.`DATUM` 
			WHERE
				`sl`.`VERWIJDERD` = %d
			ORDER BY 
				DATUM DESC, DAGNUMMER;";

        parent::DbUitvoeren("DROP VIEW IF EXISTS startlijst_view");
        parent::DbUitvoeren(sprintf($query, "startlijst_view", $this->dbTable, 0));

        parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_startlijst_view");
        parent::DbUitvoeren(sprintf($query, "verwijderd_startlijst_view", $this->dbTable, 1));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID = null)
    {
        $functie = "Startlijst.GetObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));

        if ($ID == null)
            throw new Exception("406;Geen ID in aanroep;");

        $conditie = array();
        $conditie['ID'] = isINT($ID, "ID");

        $obj = parent::GetSingleObject($conditie);
        Debug(__FILE__, __LINE__, print_r($obj, true));

        if ($obj == null)
            throw new Exception("404;Record niet gevonden;");

        // Controle of de gebruiker deze data wel mag ophalen
        $l = MaakObject('Login');
        if (!$this->heeftDataToegang($obj['DATUM'])) {
            // is ingelogde gebruiker de vlieger of inzittende? Nee, dan geen toegang
            if (($obj['VLIEGER_ID'] != $l->getUserFromSession()) && ($obj['INZITTENDE_ID'] != $l->getUserFromSession()))
                throw new Exception("401;Geen leesrechten voor dit record;");

        }

        $obj = $this->RecordToOutput($obj);
        return $obj;
    }

    /*
    Haal een dataset op met records als een array uit de database.
    */
    function GetObjects($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetObjects";
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

        // Als ingelogde gebruiker geen bijzonder functie heeft, worden alleen zijn vluchten opgehaald
        $l = MaakObject('Login');

        if ($l->isInstaller()) {
            // als installer mogen we alleen laatste aanpassing ophalen
            $alleenLaatsteAanpassing = true;
        } elseif (!$this->heeftDataToegang() && !$l->isStarttoren() && !$l->isRapporteur()) {
            $w = sprintf("(VLIEGER_ID = '%d') OR (INZITTENDE_ID = '%d')", $l->getUserFromSession(), $l->getUserFromSession());

            // Startlijst voor beheerder DDWV is ook op DDWV dagen bechikbaar
            if ($l->isBeheerderDDWV() == true)
                $w .= " OR (DDWV = 1)";

            // Startlijst voor DDWV crew is mogen ook DDWV dagen zien waar ze zelf dienst hadden
            if ($l->isDDWVCrew() == true) {
                $w .= sprintf(" OR ((DATUM IN (select DATUM from oper_diensten WHERE LID_ID = %d))", $l->getUserFromSession());
                $w .= " AND (DDWV = 1))";
            }

            $where .= sprintf(" AND (%s)", $w);
        }

        if ($l->isStarttoren() == true)
            $where .= sprintf(" AND DATUM = '%s'", date("Y-m-d"));        // starttoren mag alleen vandaag opvragen


        foreach ($params as $key => $value) {
            switch ($key) {
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
                    if (strpos($value, ';') !== false)
                        throw new Exception("405;VELDEN is onjuist;");

                    $velden = $value;
                    Debug(__FILE__, __LINE__, sprintf("%s: VELDEN='%s'", $functie, $velden));
                    break;
                }
                case "SORT" :
                {
                    if (strpos(strtoupper($value), 'UPDATE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos(strtoupper($value), 'DELETE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos($value, ';') !== false)
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

                    if ($max > 0) {
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
                case "SELECTIE" :
                {
                    $where .= " AND ((VLIEGERNAAM_LID LIKE ?) ";
                    $where .= " OR  (INZITTENDENAAM_LID LIKE ?) ";
                    $where .= " OR  (VLIEGERNAAM LIKE ?) ";
                    $where .= " OR  (INZITTENDENAAM LIKE ?) ";
                    $where .= " OR  (REG_CALL LIKE ?))";

                    $s = "%" . trim($value) . "%";
                    array_push($query_params, $s);
                    array_push($query_params, $s);
                    array_push($query_params, $s);
                    array_push($query_params, $s);
                    array_push($query_params, $s);

                    Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));
                    break;
                }
                case "STARTMETHODE_ID" :
                {
                    $sm_id = isINT($value, "STARTMETHODE_ID");
                    $where .= " AND STARTMETHODE_ID = ?";
                    array_push($query_params, $sm_id);

                    Debug(__FILE__, __LINE__, sprintf("%s: STARTMETHODE_ID='%s'", $functie, $sm_id));
                    break;
                }
                case "LID_ID" :
                {
                    $lidID = isINT($value, "LID_ID");
                    $where .= " AND ((VLIEGER_ID = ?) OR (INZITTENDE_ID = ?))";
                    array_push($query_params, $lidID);
                    array_push($query_params, $lidID);

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
                    break;
                }
                case "VLIEGTUIG_ID" :
                {
                    $vliegtuigID = isINT($value, "VLIEGTUIG_ID");
                    $where .= " AND (VLIEGTUIG_ID = ?)";
                    array_push($query_params, $vliegtuigID);

                    Debug(__FILE__, __LINE__, sprintf("%s: VLIEGTUIG_ID='%s'", $functie, $vliegtuigID));
                    break;
                }
                case "OPEN_STARTS" :
                {
                    $openStarts = isBOOL($value, "OPEN_STARTS");

                    if ($openStarts)
                        $where .= " AND (LANDINGSTIJD IS NULL OR VLIEGER_ID IS NULL)";

                    Debug(__FILE__, __LINE__, sprintf("%s: OPEN_STARTS='%s'", $functie, $openStarts));
                    break;
                }
                case "DDWV" :
                {
                    $alleenDDWV = isBOOL($value, "DDWV");
                    Debug(__FILE__, __LINE__, sprintf("%s: DDWV='%s'", $functie, $alleenVerwijderd));

                    if ($alleenDDWV) {
                        $where .= " AND (DDWV=1)";
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
				`####startlijst_view`" . $where . $orderby;
        $query = str_replace("####", ($alleenVerwijderd ? "verwijderd_" : ""), $query);

        $retVal = array();

        $retVal['totaal'] = $this->Count($query, $query_params);        // totaal aantal of record in de database
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query, $query_params);
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        } else {
            if ($limit > 0) {
                if ($start < 0)                // Is niet meegegeven, dus start op 0
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

            for ($i = 0; $i < count($retVal['dataset']); $i++) {
                $retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);
            }
            return $retVal;
        }
        return null;  // Hier komen we nooit :-)
    }

    /*
    Haal een dataset op met logboek records als een array uit de database.
    */
    function GetLogboek($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetLogboek";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        $where = ' WHERE 1=1 ';
        $orderby = " ORDER BY DATUM DESC, STARTTIJD DESC";
        $alleenLaatsteAanpassing = false;
        $hash = null;
        $limit = -1;
        $start = -1;
        $lidID = null;
        $velden = "*";
        $query_params = array();

        foreach ($params as $key => $value) {
            switch ($key) {
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
                case "SORT" :
                {
                    if (strpos(strtoupper($value), 'UPDATE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos(strtoupper($value), 'DELETE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos($value, ';') !== false)
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

                    if ($max > 0) {
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
                case "LID_ID" :
                {
                    $lidID = isINT($value, "LID_ID");

                    // privacy check
                    $l = MaakObject('Login');

                    if ($value != $l->getUserFromSession() && !$l->isRapporteur()) {
                        if (!$this->heeftDataToegang())
                            throw new Exception("401;Gebruiker mag geen logboek van ander lid opvragen;");
                    }
                    $where .= sprintf(" AND ((VLIEGER_ID = '%d') OR (INZITTENDE_ID = '%d'))", $lidID, $lidID);

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
                    break;
                }
                case "VLIEGTUIG_ID" :
                {
                    $vliegtuigID = isINT($value, "VLIEGTUIG_ID");
                    $where .= sprintf(" AND (VLIEGTUIG_ID = '%d')", $vliegtuigID);

                    Debug(__FILE__, __LINE__, sprintf("%s: VLIEGTUIG_ID='%s'", $functie, $vliegtuigID));
                    break;
                }

                case "JAAR" :
                {
                    $jaar = isINT($value, "JAAR");

                    $where .= " AND DATE(DATUM) >= ? ";
                    array_push($query_params, "$jaar-01-01");
                    $where .= " AND DATE(DATUM) <= ? ";
                    array_push($query_params, "$jaar-12-31");

                    Debug(__FILE__, __LINE__, sprintf("%s: JAAR='%s'", $functie, $jaar));
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                    break;
                }
            }
        }


        if ($lidID === null)    /* niet meegegeven in parameters, dus default waarde gebruiken */ {
            $l = MaakObject('Login');
            $where .= sprintf(" AND ((VLIEGER_ID = '%d') OR (INZITTENDE_ID = '%d'))", $l->getUserFromSession(), $l->getUserFromSession());
        }

        $query = "
			SELECT 
				%s
			FROM
				`startlijst_view`" . $where . $orderby;

        $retVal = array();

        $retVal['totaal'] = $this->Count($query, $query_params);        // total amount of records in the databaseß
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query, $query_params);
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        } else {
            if ($limit > 0) {
                if ($start < 0)                // Is niet meegegeven, dus start op 0
                    $start = 0;

                $query .= sprintf(" LIMIT %d , %d ", $start, $limit);
            }
            $velden = "					
				ID,
				DATUM,
				REG_CALL,
				VLIEGTUIG_ID,
				STARTTIJD,
				LANDINGSTIJD,
				DUUR,
				IF (`VLIEGERNAAM` IS NULL, `VLIEGERNAAM_LID` , CONCAT(`VLIEGERNAAM_LID`, '(', `VLIEGERNAAM`, ')')) AS `VLIEGERNAAM`,
				IF (`INZITTENDENAAM` IS NULL, `INZITTENDENAAM_LID`, CONCAT(`INZITTENDENAAM_LID`, '(', `INZITTENDENAAM`, ')')) AS `INZITTENDENAAM`,
				VLIEGER_ID AS  VLIEGER_ID,
				INZITTENDE_ID AS INZITTENDE_ID,
				STARTMETHODE, VELD, PAX, INSTRUCTIEVLUCHT, CHECKSTART, VLIEGTUIGTYPE, OPMERKINGEN AS OPMERKINGEN";

            $rquery = sprintf($query, $velden);
            parent::DbOpvraag($rquery, $query_params);
            $retVal['dataset'] = parent::DbData();

            $retVal['hash'] = hash("crc32", json_encode($retVal));
            Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));

            if ($retVal['hash'] == $hash)
                throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

            for ($i = 0; $i < count($retVal['dataset']); $i++) {
                $retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);
            }
            return $retVal;
        }
        return null;  // Hier komen we nooit :-)
    }

    /*
    Haal het logboek van het vliegtuig op
    */
    function GetLogboekTotalen($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetLogboekTotalen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        $where = "";
        $limit = -1;
        $start = -1;
        $vliegtuigID = -1;
        $velden = "*";
        $query_params = array();
        $alleenLaatsteAanpassing = false;
        $hash = null;

        foreach ($params as $key => $value) {
            switch ($key) {
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
                case "JAAR" :
                {
                    $jaar = isINT($value, "JAAR");
                    $where .= sprintf("AND YEAR(DATUM)=%d ", $jaar);
                    break;
                }
                case "LID_ID" :
                {
                    $lidID = isINT($value, "LID_ID");

                    // privacy check
                    $l = MaakObject('Login');

                    if ($value != $l->getUserFromSession()) {
                        if (!$this->heeftDataToegang() && !$l->isRapporteur()) {
                            throw new Exception("401;Gebruiker mag geen logboek van ander lid opvragen;");
                        }
                    }

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                    break;
                }
            }
        }

        if ($lidID === null)    /* niet meegegeven in parameters, dus default waarde gebruiken */ {
            $lidID = $l->getUserFromSession();
        }

        $isInstructeur = false;
        $l = MaakObject('Leden');
        $lid = $l->getObject($lidID);
        if ($lid['INSTRUCTEUR'] == 1)        // Voor instructeurs tellen ook de instructie starts mee
        {
            $where .= sprintf(" AND ((VLIEGER_ID = '%d') OR ((INZITTENDE_ID = %d) AND INSTRUCTIEVLUCHT=1))", $lidID, $lidID);
            $isInstructeur = true;
        } else {
            $where .= sprintf(" AND (VLIEGER_ID = '%d')", $lidID);
        }

        if (!array_key_exists('JAAR', $params))
            $where .= sprintf("AND YEAR(DATUM)=%d ", date("Y"));

        $query = "
			SELECT 
				%s 
			FROM
				startlijst_view slv INNER JOIN 
				ref_vliegtuigen as rv ON slv.VLIEGTUIG_ID = rv.ID
			WHERE 
				STARTTIJD is not null AND LANDINGSTIJD is not null " . $where . "
			ORDER BY 
				rv.CLUBKIST DESC, rv.VOLGORDE, slv.REG_CALL";

        $retVal = array();
        $retVal['totaal'] = $this->Count($query, $query_params);        // totaal aantal of record in de database
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query, $query_params, 'slv.LAATSTE_AANPASSING');
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        }

        $rquery = sprintf($query, $velden);
        parent::DbOpvraag($rquery, $query_params);
        $logboek = parent::DbData();

        $retVal['hash'] = hash("crc32", json_encode($logboek));
        Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));

        if ($retVal['hash'] == $hash)
            throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

        $vliegtuigen = array();
        $startMethode = array();
        $instructieStarts = 0;
        $instructieUren = 0;
        $totaalAantalStarts = 0;
        $totaallVliegtijd = 0;

        for ($i = 0; $i < count($logboek); $i++) {

            $REG_CALL = $logboek[$i]['REG_CALL'];        // REG_CALL uit vlucht

            $t = explode(":", $logboek[$i]['DUUR']);
            $vliegtijd = $t[0] * 60 + $t[1] * 1;        // Vliegtijd in minuten

            $totaallVliegtijd += $vliegtijd;
            $totaalAantalStarts++;

            if (!isset($vliegtuigen[$REG_CALL])) {
                $vliegtuigen[$REG_CALL]['REG_CALL'] = $REG_CALL;
                $vliegtuigen[$REG_CALL]['STARTS'] = 1;
                $vliegtuigen[$REG_CALL]['VLIEGTIJD'] = $vliegtijd;
            } else {
                $vliegtuigen[$REG_CALL]['STARTS']++;
                $vliegtuigen[$REG_CALL]['VLIEGTIJD'] += $vliegtijd;
            }

            $START_METHODE = $logboek[$i]['STARTMETHODE'];            // Startmethode uit vlucht

            if (!isset($startMethode[$START_METHODE])) {
                $startMethode[$START_METHODE]['METHODE'] = $START_METHODE;
                $startMethode[$START_METHODE]['AANTAL'] = 1;
            } else {
                $startMethode[$START_METHODE]['AANTAL']++;
            }

            if (($isInstructeur) && ($logboek[$i]['INZITTENDE_ID'] == $lidID)) {
                $instructieStarts++;
                $instructieUren += $vliegtijd;
            }
        }

        $keys = array_keys($vliegtuigen);
        for ($i = 0; $i < count($vliegtuigen); $i++) {
            $key = $keys[$i];
            $hh = $vliegtuigen[$key]['VLIEGTIJD'] / 60;
            $mm = $vliegtuigen[$key]['VLIEGTIJD'] % 60;

            $vliegtuigen[$key]['VLIEGTIJD'] = sprintf("%d:%02d", intval($hh), $mm);
        }

        $retVal['starts'] = array_values($startMethode);
        $retVal['vliegtuigen'] = array_values($vliegtuigen);

        $hh = $totaallVliegtijd / 60;
        $mm = $totaallVliegtijd % 60;

        $retVal['jaar']['STARTS'] = $totaalAantalStarts;
        $retVal['jaar']['INSTRUCTIE_STARTS'] = $instructieStarts;
        $retVal['jaar']['INSTRUCTIE_UREN'] = intval($instructieUren / 60) . ":" . sprintf("%02d", $instructieUren % 60);
        $retVal['jaar']['VLIEGTIJD'] = sprintf("%d:%02d", intval($hh), $mm);

        return $retVal;
    }

    /*
    Haal het logboek van het vliegtuig op
    */
    function GetVliegtuigLogboek($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetVliegtuigLogboek";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        if (!array_key_exists('ID', $params))
            throw new Exception("406;ID ontbreekt in aanroep;");

        $where = "";
        $limit = -1;
        $start = -1;
        $vliegtuigID = -1;
        $velden = "*";
        $query_params = array();
        $alleenLaatsteAanpassing = false;
        $hash = null;

        foreach ($params as $key => $value) {
            switch ($key) {
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

                    if ($max > 0) {
                        $limit = $max;
                        Debug(__FILE__, __LINE__, sprintf("%s: LIMIT='%s'", $functie, $limit));
                    }
                    break;
                }
                case "ID" :
                {
                    $vliegtuigID = isINT($value, "ID");
                    $where .= sprintf("AND VLIEGTUIG_ID=%d ", $vliegtuigID);
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

        $privacyCheck = true;

        $l = MaakObject('Login');

        if ($l->isBeheerder())
            $privacyCheck = false;

        if ($l->isBeheerderDDWV())
            $privacyCheck = false;

        if ($l->isStarttoren())
            $privacyCheck = false;

        if ($l->isRapporteur())
            $privacyCheck = false;

        if ($l->isInstructeur() || $l->isInstructeur())
            $privacyCheck = false;

        if ($privacyCheck == true) {
            // Club vliegtuigen, mag iedereen zien
            $rv = MaakObject('Vliegtuigen');
            $rvObj = $rv->GetObject($vliegtuigID);

            if ($rvObj['CLUBKIST'] == true)
                $privacyCheck = false;
        }

        if ($privacyCheck == true) {
            // controleer op deze gebruiker in de laatste 6 maanden gevlogen heeft op deze kist
            $query = sprintf("
					SELECT 
						count(*) as aantal
					FROM 
						oper_startlijst 
					WHERE
						STARTTIJD IS NOT NULL	AND						
						VLIEGTUIG_ID = %s 		AND 
						VLIEGER_ID = %s 		AND 
						DATUM >  NOW()- INTERVAL 6 MONTH", $vliegtuigID, $l->getUserFromSession());

            parent::DbOpvraag($query);
            $vluchten = parent::DbData();

            if (intval($vluchten[0]["aantal"]) == 0) {
                // Nee, dus geen toegang tot logboek
                throw new Exception("406;Niet gemachtigd om logboek te bekijken;");
            }
        }

        if ((!array_key_exists('BEGIN_DATUM', $params)) && (!array_key_exists('EIND_DATUM', $params)))
            $where .= sprintf(" AND `DATUM` >= '%s-01-01'", date("Y"));

        parent::DbOpvraag("
			SELECT COUNT(DISTINCT(DATUM)) AS totaal FROM
				startlijst_view slv
			WHERE 
				STARTTIJD is not null AND LANDINGSTIJD is not null " . $where, $query_params);

        $dagen = parent::DbData();

        $retVal = array();
        $retVal['totaal'] = 1 * $dagen[0]['totaal'];
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing("SELECT %s FROM
											startlijst_view slv
										WHERE 
											STARTTIJD is not null AND LANDINGSTIJD is not null " . $where, $query_params);
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        } else {
            $query = "
			SELECT
				DATUM, 
				COUNT(*) AS VLUCHTEN,
				(SELECT 
					COUNT(*) 
				FROM 
					startlijst_view 
				WHERE
					STARTTIJD is not null AND LANDINGSTIJD is not null AND
					VLIEGTUIG_ID = slv.VLIEGTUIG_ID AND 
					DATUM = slv.DATUM AND 
					STARTMETHODE_ID >= 550) AS LIERSTARTS,
				(SELECT 
					COUNT(*) 
				FROM 
					startlijst_view
				WHERE 
					STARTTIJD is not null AND LANDINGSTIJD is not null AND
					VLIEGTUIG_ID = slv.VLIEGTUIG_ID AND 
					DATUM = slv.DATUM AND 
					STARTMETHODE_ID =  501) AS SLEEPSTARTS,
				SEC_TO_TIME(SUM(TIME_TO_SEC(STR_TO_DATE(DUUR, '%H:%i') ))) AS VLIEGTIJD,
				REG_CALL
			FROM 
				startlijst_view slv
			WHERE 
				STARTTIJD is not null AND LANDINGSTIJD is not null " . $where . "
			GROUP BY 
				DATUM
			ORDER BY DATUM DESC";

            if ($limit > 0) {
                if ($start < 0)                // Is niet meegegeven, dus start op 0
                    $start = 0;

                $query .= sprintf(" LIMIT %d , %d ", $start, $limit);
            }

            // $rquery = sprintf($query, $velden);
            parent::DbOpvraag($query, $query_params);
            $retVal['dataset'] = parent::DbData();

            $retVal['hash'] = hash("crc32", json_encode($retVal));
            Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));

            if ($retVal['hash'] == $hash)
                throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

            for ($i = 0; $i < count($retVal['dataset']); $i++) {
                if (!is_null($retVal['dataset'][$i]['VLIEGTIJD']))
                    $retVal['dataset'][$i]['VLIEGTIJD'] = substr($retVal['dataset'][$i]['VLIEGTIJD'], 0, 5);    // alleen hh:mm

                $retVal['dataset'][$i] = $this->RecordToOutput($retVal['dataset'][$i]);
            }
        }
        return $retVal;
    }

    /*
    Haal het logboek van het vliegtuig op
    */
    function GetVliegtuigLogboekTotalen($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetVliegtuigLogboekTotalen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        if (!array_key_exists('ID', $params))
            throw new Exception("406;ID ontbreekt in aanroep;");

        $where = "";
        $limit = -1;
        $start = -1;
        $vliegtuigID = -1;
        $velden = "*";
        $query_params = array();
        $alleenLaatsteAanpassing = false;
        $hash = null;

        foreach ($params as $key => $value) {
            switch ($key) {
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
                case "JAAR" :
                {
                    $jaar = isINT($value, "JAAR");
                    $where .= sprintf("AND YEAR(DATUM)=%d ", $jaar);
                    break;
                }
                case "ID" :
                {
                    $vliegtuigID = isINT($value, "ID");
                    $where .= sprintf("AND VLIEGTUIG_ID=%d ", $vliegtuigID);
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                    break;
                }
            }
        }

        $privacyCheck = true;

        $l = MaakObject('Login');

        if ($l->isBeheerder())
            $privacyCheck = false;

        if ($l->isBeheerderDDWV())
            $privacyCheck = false;

        if ($l->isStarttoren())
            $privacyCheck = false;

        if ($l->isRapporteur())
            $privacyCheck = false;

        if ($l->isInstructeur() || $l->isInstructeur())
            $privacyCheck = false;

        if ($privacyCheck == true) {
            // Club vliegtuigen, mag iedereen zien
            $rv = MaakObject('Vliegtuigen');
            $rvObj = $rv->GetObject($vliegtuigID);

            if ($rvObj['CLUBKIST'] == true)
                $privacyCheck = false;
        }

        if ($privacyCheck == true) {
            // controleer op deze gebruiker in de laatste 6 maanden gevlogen heeft op deze kist
            $query = sprintf("
					SELECT 
						count(*) as aantal
					FROM 
						oper_startlijst 
					WHERE
						STARTTIJD IS NOT NULL	AND						
						VLIEGTUIG_ID = %s 		AND 
						VLIEGER_ID = %s 		AND 
						STR_TO_DATE(DATUM, '%%Y-%%m-%%d') > NOW()- INTERVAL 6 MONTH", $vliegtuigID, $l->getUserFromSession());

            parent::DbOpvraag($query);
            $vluchten = parent::DbData();

            if (intval($vluchten[0]["aantal"]) == 0) {
                // Nee, dus geen toegang tot logboek
                throw new Exception("406;Niet gemachtigd om logboek te bekijken;");
            }
        }

        if (!array_key_exists('JAAR', $params))
            $where .= sprintf("AND YEAR(DATUM)=%d ", date("Y"));

        $query = "SELECT %s FROM
					startlijst_view slv
				WHERE 
					STARTTIJD is not null AND LANDINGSTIJD is not null " . $where;

        $retVal = array();
        $retVal['totaal'] = $this->Count($query);        // totaal aantal of record in de database
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query);
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        } else {
            $query = "
			SELECT
				MONTH(DATUM) AS MAAND, 
				COUNT(*) AS VLUCHTEN,
				(SELECT 
					COUNT(*) 
				FROM 
					startlijst_view 
				WHERE
					STARTTIJD is not null AND LANDINGSTIJD is not null AND
					VLIEGTUIG_ID = slv.VLIEGTUIG_ID AND 
					MONTH(DATUM) = MONTH(slv.DATUM) AND 
					YEAR(DATUM) = YEAR(slv.DATUM) AND 
					STARTMETHODE_ID >= 550) AS LIERSTARTS,
				(SELECT 
					COUNT(*) 
				FROM 
					startlijst_view
				WHERE 
					STARTTIJD is not null AND LANDINGSTIJD is not null AND
					VLIEGTUIG_ID = slv.VLIEGTUIG_ID AND 
					MONTH(DATUM) = MONTH(slv.DATUM) AND 
					YEAR(DATUM) = YEAR(slv.DATUM) AND 
					STARTMETHODE_ID =  501) AS SLEEPSTARTS,
				SEC_TO_TIME(SUM(TIME_TO_SEC(STR_TO_DATE(DUUR, '%H:%i') ))) AS VLIEGTIJD,
				REG_CALL
			FROM 
				startlijst_view slv
			WHERE 
				STARTTIJD is not null AND LANDINGSTIJD is not null " . $where . "
			GROUP BY 
				MONTH (DATUM)
			ORDER BY DATUM DESC";

            if ($limit > 0) {
                if ($start < 0)                // Is niet meegegeven, dus start op 0
                    $start = 0;

                $query .= sprintf(" LIMIT %d , %d ", $start, $limit);
            }

            parent::DbOpvraag($query, $query_params);

            // lege data maken
            $maanden = array();
            for ($i = 1; $i <= 12; $i++)        // 12 maanden
            {
                $maanden[$i] = array(
                    'MAAND' => "$i",
                    'VLUCHTEN' => "0",
                    'LIERSTARTS' => "0",
                    'SLEEPSTARTS' => "0",
                    'VLIEGTIJD' => '00:00:00',
                    'REG_CALL' => ''
                );
            }

            $totalen = array(
                'VLUCHTEN' => "0",
                'LIERSTARTS' => "0",
                'SLEEPSTARTS' => "0",
                'VLIEGTIJD' => '00:00:00',
            );

            foreach (parent::DbData() as $maand) {
                $maanden[$maand['MAAND']] = $maand;

                $totalen['VLUCHTEN'] += $maand['VLUCHTEN'];
                $totalen['LIERSTARTS'] += $maand['LIERSTARTS'];
                $totalen['SLEEPSTARTS'] += $maand['SLEEPSTARTS'];

                $m = explode(":", $maand['VLIEGTIJD']);
                $t = explode(":", $totalen['VLIEGTIJD']);

                // optellen sec
                $sec = $t[2] + $m[2];
                if ($sec >= 60) {
                    $sec = $sec % 60;
                    $t[1]++;
                }

                // optellen min
                $min = $t[1] + $m[1];
                if ($min >= 60) {
                    $min = $min % 60;
                    $t[0]++;
                }

                // optellen uren
                $uren = $t[0] + $m[0];
                $totalen['VLIEGTIJD'] = sprintf("%02d:%02d:%02d", $uren, $min, $sec);
            }
            $retVal['dataset'] = array_values($maanden);
            $retVal['totaal'] = 1 * count($maanden);
            $retVal['totalen'] = $totalen;

        }

        for ($i = 0; $i < count($retVal['dataset']); $i++) {
            if (isset($retVal['dataset'][$i]['MAAND']))
                $retVal['dataset'][$i]['MAAND'] = $retVal['dataset'][$i]['MAAND'] * 1;

            if (isset($retVal['dataset'][$i]['VLUCHTEN']))
                $retVal['dataset'][$i]['VLUCHTEN'] = $retVal['dataset'][$i]['VLUCHTEN'] * 1;

            if (isset($retVal['dataset'][$i]['LIERSTARTS']))
                $retVal['dataset'][$i]['LIERSTARTS'] = $retVal['dataset'][$i]['LIERSTARTS'] * 1;

            if (isset($retVal['dataset'][$i]['SLEEPSTARTS']))
                $retVal['dataset'][$i]['SLEEPSTARTS'] = $retVal['dataset'][$i]['SLEEPSTARTS'] * 1;
        }
        $retVal['hash'] = hash("crc32", json_encode($retVal));
        Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));

        if ($retVal['hash'] == $hash)
            throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

        return $retVal;
    }

    /*
    Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
    Het veld VERWIJDERD wordt op "1" gezet.
    */
    function VerwijderObject($id, $verificatie = true)
    {
        $functie = "Startlijst.VerwijderObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" : $verificatie)));
        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        $start = $this->GetObject(isCSV($id, "ID"));
        $volleToegang = $this->heeftDataToegang($start['DATUM']);
        if (!$volleToegang) {
            // We mogen alleen vluchten van onszelf verwijderen
            $l = MaakObject('Login');
            if (($start['VLIEGER_ID'] != $l->getUserFromSession()) && ($start['VLIEGER_ID'] != $l->getUserFromSession()))
                throw new Exception("401;Geen schrijfrechten;");
        }

        parent::MarkeerAlsVerwijderd($id, $verificatie);
    }

    /*
    Herstel van een verwijderd record
    */
    function HerstelObject($id)
    {
        $functie = "Startlijst.HerstelObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        $start = $this->GetObject(isCSV($id, "ID"));
        $volleToegang = $this->heeftDataToegang($start['DATUM']);
        if (!$volleToegang) {
            // We mogen alleen vluchten van onszelf verwijderen
            $l = MaakObject('Login');
            if (($start['VLIEGER_ID'] != $l->getUserFromSession()) && ($start['VLIEGER_ID'] != $l->getUserFromSession()))
                throw new Exception("401;Geen schrijfrechten;");
        }
        parent::HerstelVerwijderd($id);
    }

    /*
    Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function AddObject($StartlijstData)
    {
        $functie = "Startlijst.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($StartlijstData, true)));

        if ($StartlijstData == null)
            throw new Exception("406;StartlijstData data moet ingevuld zijn;");

        $where = "";
        $nieuw = true;
        if (array_key_exists('ID', $StartlijstData)) {
            $id = isINT($StartlijstData['ID'], "ID");

            // ID is opgegeven, maar bestaat record?
            try    // Als record niet bestaat, krijgen we een exception
            {
                $this->GetObject($id);
            } catch (Exception $e) {
            }

            if (parent::NumRows() > 0)
                throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
        }

        if (!array_key_exists('VLIEGTUIG_ID', $StartlijstData))
            throw new Exception("406;VLIEGTUIG_ID is verplicht;");

        if (!array_key_exists('DATUM', $StartlijstData))
            throw new Exception("406;DATUM is verplicht;");

        $l = MaakObject('Login');
        $volleToegang = $this->heeftDataToegang($StartlijstData['DATUM']);
        if (!$volleToegang) {
            $nieuweVlieger = array_key_exists('VLIEGER_ID', $StartlijstData) ? $StartlijstData['VLIEGER_ID'] : -1;
            $nieuweInzittende = array_key_exists('INZITTENDE_ID', $StartlijstData) ? $StartlijstData['INZITTENDE_ID'] : $nieuweVlieger;

            // We mogen alleen vluchten voor onszelf invoeren
            if (($nieuweVlieger != $l->getUserFromSession()) && ($nieuweInzittende != $l->getUserFromSession())) {
                Debug(__FILE__, __LINE__, sprintf("Startlijst.UpdateObject nieuweVlieger=%s nieuweInzittende=%s", $nieuweVlieger, $nieuweInzittende));
                throw new Exception(sprintf("405;VLIEGER_ID of INZITTENDE_ID moet LID_ID(%s) bevatten;", $l->getUserFromSession()));
            }
        }

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($StartlijstData);
        $d['DAGNUMMER'] = $this->NieuwDagNummer($d['DATUM']);

        $this->StartLandingTijdenValidatie($d);

        $id = parent::DbToevoegen($d);
        Debug(__FILE__, __LINE__, sprintf("Start toegevoegd id=%d", $id));

        $record = $this->GetObject($id);
        try {
            $this->AanmeldenVliegtuig($record);

            if (isset($record['STARTTIJD']))
                $this->Aanmeldingen($record);
        } catch (Exception $exception) {
        }
        return $record;
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($StartlijstData)
    {
        $functie = "Startlijst.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($StartlijstData, true)));

        if ($StartlijstData == null)
            throw new Exception("406;Start data moet ingevuld zijn;");

        if (!array_key_exists('ID', $StartlijstData))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($StartlijstData['ID'], "ID");
        $bestaandeStart = $this->GetObject($id);

        $l = MaakObject('Login');
        $volleToegang = $this->heeftDataToegang($bestaandeStart['DATUM']);
        if (!$volleToegang) {

            // Geen schrijf rechten, maar eigen starts mag je altijd aanpassen
            if (($bestaandeStart['VLIEGER_ID'] != $l->getUserFromSession()) && ($bestaandeStart['INZITTENDE_ID'] != $l->getUserFromSession())) {
                throw new Exception("401;Geen schrijfrechten;");
            } else // we hebben geen schrijfrechten, maar zijn vlieger of inzittende
            {
                $nieuweVlieger = array_key_exists('VLIEGER_ID', $StartlijstData) ? $StartlijstData['VLIEGER_ID'] : $bestaandeStart['VLIEGER_ID'];
                $nieuweInzittende = array_key_exists('INZITTENDE_ID', $StartlijstData) ? $StartlijstData['INZITTENDE_ID'] : $bestaandeStart['INZITTENDE_ID'];

                // We mogen onszelf niet van de vlucht halen
                if (($nieuweVlieger != $l->getUserFromSession()) && ($nieuweInzittende != $l->getUserFromSession())) {
                    Debug(__FILE__, __LINE__, sprintf("Startlijst.UpdateObject nieuweVlieger=%s nieuweInzittende=%s", $nieuweVlieger, $nieuweInzittende));
                    throw new Exception(sprintf("405;VLIEGER_ID of INZITTENDE_ID moet LID_ID(%s) bevatten;", $l->getUserFromSession()));
                }
            }
        }

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($StartlijstData);
        if (($l->isStarttoren() == true) && (array_key_exists('DATUM', $d))) {
            if (isDATE($d['DATUM']) != date("Y-m-d"))        // starttoren mag alleen vandaag invoeren
                throw new Exception("401;Geen schrijfrechten;");
        }

        // Als datum aangepast is, dan nieuw dagnummer toekennen
        if (array_key_exists("DATUM", $d) && ($bestaandeStart['DATUM'] != $d['DATUM'])) {
            $d['DAGNUMMER'] = $this->NieuwDagNummer($d['DATUM']);
        }

        $this->StartLandingTijdenValidatie($d);

        parent::DbAanpassen($id, $d);
        if (parent::NumRows() === 0)
            throw new Exception("404;Record niet gevonden;");

        $record = $this->GetObject($id);
        try {
            $this->AanmeldenVliegtuig($record);

            if (isset($record['STARTTIJD']))
                $this->Aanmeldingen($record);
        } catch (Exception $exception) {
        }
        return $record;
    }

    // example
    //	{
    //	STARTS_DRIE_MND: "2"
    //  STARTS_VORIG_JAAR: "36"
    //  STARTS_DIT_JAAR: "2"
    //  STARTS_INSTRUCTIE: "45"
    //  UREN_DRIE_MND: "1:42"
    //  UREN_VORIG_JAAR: "1:42"
    //  UREN_DIT_JAAR: "27:31"
    //  UREN_INSTRUCTIE: "68:45"
    //  STATUS_BAROMETER: "onbekend"
    //  STARTS_BAROMETER: "38"
    //  UREN_BAROMETER: "29:13"
    //	}

    function GetRecency($vliegerID, $datum = null)
    {
        $functie = "Startlijst.GetRecency";
        Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $vliegerID, $datum));

        if ($vliegerID == null)
            throw new Exception("406;VLIEGER_ID moet ingevuld zijn;");

        $l = MaakObject('Login');
        if ($vliegerID != $l->getUserFromSession()) {
            if (!$this->heeftDataToegang() && !$l->isRapporteur())
                throw new Exception("401;Geen leesrechten;");
        }

        $dateTime = new DateTime();
        if ($datum !== null) {
            $dateTime = new DateTime($datum);
        }
        $vliegerID = isINT($vliegerID, "VLIEGER_ID");

        $l = MaakObject('Leden');
        if ($l->isPermissie("INSTRUCTEUR", $vliegerID))        // Voor instructeurs tellen ook de instructie starts mee
        {
            $InstructieSQL = sprintf("(INZITTENDE_ID = %s)", $vliegerID);
        } else {
            $InstructieSQL = "(1 = 0)";            // dit is nooit waar, en dat is prima
        }

        $retVal['STARTS_DRIE_MND'] = 0;
        $retVal['STARTS_VORIG_JAAR'] = 0;
        $retVal['STARTS_DIT_JAAR'] = 0;
        $retVal['STARTS_INSTRUCTIE'] = -1;

        $retVal['UREN_DRIE_MND'] = 0;
        $retVal['UREN_DIT_JAAR'] = 0;
        $retVal['UREN_VORIG_JAAR'] = 0;
        $retVal['UREN_INSTRUCTIE'] = 0;
        $retVal['STATUS_BAROMETER'] = 'onbekend';    // andere mogelijkheden: rood/geel/groen
        $retVal['STARTS_BAROMETER'] = 0;
        $retVal['UREN_BAROMETER'] = 0;

        $where = sprintf("DATUM > '%d-01-01' AND DATUM <= '%s' AND STARTTIJD IS NOT NULL AND LANDINGSTIJD IS NOT NULL  ", $dateTime->format("Y") - 1, $dateTime->format("Y-m-d"));
        $where .= sprintf(" AND ((VLIEGER_ID = %s) OR %s)", $vliegerID, $InstructieSQL);

        $query = "
			SELECT
				*
			FROM
				startlijst_view
			WHERE
				%s ORDER BY DATUM DESC";

        parent::DbOpvraag(sprintf($query, $where));

        foreach (parent::DbData() as $vlucht) {
            $diff = abs(strtotime($dateTime->format("Y-m-d")) - strtotime($vlucht['DATUM'])) / (60 * 60 * 24);    // dif in dagen

            if ($diff < (13 * 7)) // laaste drie maanden = 13 weken
            {
                $retVal['STARTS_DRIE_MND']++;
                $retVal['UREN_DRIE_MND'] += intval(substr($vlucht['DUUR'], 0, 2)) * 60 + intval(substr($vlucht['DUUR'], 3, 2));
            }

            if ($diff <= (26 * 7)) // laaste 6 maanden = 26 weken
            {
                $retVal['STARTS_BAROMETER']++;
                $retVal['UREN_BAROMETER'] += intval(substr($vlucht['DUUR'], 0, 2)) * 60 + intval(substr($vlucht['DUUR'], 3, 2));
            }

            if (substr($vlucht['DATUM'], 0, 4) == Date("Y"))    // Dit jaar
            {
                $retVal['STARTS_DIT_JAAR']++;
                $retVal['UREN_DIT_JAAR'] += intval(substr($vlucht['DUUR'], 0, 2)) * 60 + intval(substr($vlucht['DUUR'], 3, 2));;
            } else    // Vorig jaar
            {
                $retVal['STARTS_VORIG_JAAR']++;
                $retVal['UREN_VORIG_JAAR'] += intval(substr($vlucht['DUUR'], 0, 2)) * 60 + intval(substr($vlucht['DUUR'], 3, 2));
            }
        }

        // uitrekenen barameter status
        $y1 = $retVal['UREN_BAROMETER'] / 60;        // variable heeft minuten, dus delen door 60
        $y2 = $retVal['STARTS_BAROMETER'] * 25 / 35;

        $gem = ($y1 + $y2) / 2;        // snijpunt van witte lijn in het plaatje

        if ($gem < 10)
            $retVal['STATUS_BAROMETER'] = 'rood';
        else if ($gem < 20)
            $retVal['STATUS_BAROMETER'] = 'geel';
        else
            $retVal['STATUS_BAROMETER'] = 'groen';

        // Nu de instructie vluchten
        if ($l->isPermissie("INSTRUCTEUR", $vliegerID))        // Alleen nodig voor instructeurs
        {
            $retVal['STARTS_INSTRUCTIE'] = 0;
            $fromDateTime = new DateTime($dateTime->format("Y-m-d"));
            $fromDateTime->modify('-3 year');

            $where = sprintf("DATUM > '%s' AND DATUM <= '%s' AND STARTTIJD IS NOT NULL AND LANDINGSTIJD IS NOT NULL AND INSTRUCTIEVLUCHT=1 AND (INZITTENDE_ID = %s) ",
                $fromDateTime->format("Y-m-d"),
                $dateTime->format("Y-m-d"),
                $vliegerID);

            parent::DbOpvraag(sprintf($query, $where));

            foreach (parent::DbData() as $vlucht) {
                $retVal['STARTS_INSTRUCTIE']++;
                $retVal['UREN_INSTRUCTIE'] += intval(substr($vlucht['DUUR'], 0, 2)) * 60 + intval(substr($vlucht['DUUR'], 3, 2));
            }
        }

        // tijden staan in minuten, moet naar hh:mm
        $retVal['WAARDE'] = intval($gem * 10) / 10;        // 1 cijfer achter de komma
        $retVal['UREN_DRIE_MND'] = intval($retVal['UREN_DRIE_MND'] / 60) . ":" . sprintf("%02d", $retVal['UREN_DRIE_MND'] % 60);
        $retVal['UREN_DIT_JAAR'] = intval($retVal['UREN_DIT_JAAR'] / 60) . ":" . sprintf("%02d", $retVal['UREN_DIT_JAAR'] % 60);
        $retVal['UREN_VORIG_JAAR'] = intval($retVal['UREN_VORIG_JAAR'] / 60) . ":" . sprintf("%02d", $retVal['UREN_VORIG_JAAR'] % 60);
        $retVal['UREN_BAROMETER'] = intval($retVal['UREN_BAROMETER'] / 60) . ":" . sprintf("%02d", $retVal['UREN_BAROMETER'] % 60);
        $retVal['UREN_INSTRUCTIE'] = intval($retVal['UREN_INSTRUCTIE'] / 60) . ":" . sprintf("%02d", $retVal['UREN_INSTRUCTIE'] % 60);

        return $retVal;
    }

    /*
    Vliegdagen
    */
    function GetVliegDagen($params)
    {
        global $app_settings;

        $functie = "Startlijst.GetVliegDagen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        $where = ' WHERE 1=1 ';
        $orderby = "ORDER BY DATUM";
        $beginDatum = null;
        $eindDatum = null;
        $limit = -1;
        $start = -1;
        $lidID = null;
        $hash = null;
        $query_params = array();
        $alleenLaatsteAanpassing = false;

        // Als ingelogde gebruiker geen bijzonder functie heeft, worden alleen zijn vliegdagen opgehaald
        $l = MaakObject('Login');

        if (!$this->heeftDataToegang() && !$l->isRapporteur()) {
            $w = sprintf("(VLIEGER_ID = '%d') OR (INZITTENDE_ID = '%d')", $l->getUserFromSession(), $l->getUserFromSession());

            // Startlijst voor beheerder DDWV is ook op DDWV dagen bechikbaar
            if ($l->isBeheerderDDWV() == true)
                $w .= " OR (DATUM IN (select DATUM from oper_rooster WHERE DDWV = 1))";

            // Startlijst voor DDWV crew is mogen ook DDWV dagen zien waar ze zelf dienst hadden
            if ($l->isDDWVCrew() == true) {
                $w .= sprintf(" OR ((DATUM IN (select DATUM from oper_diensten WHERE LID_ID = %d))", $l->getUserFromSession());
                $w .= " AND (DATUM IN (select DATUM from oper_rooster WHERE DDWV = 1)))";
            }

            $where .= sprintf(" AND (%s)", $w);

            if ($l->isStarttoren() == true)
                $where .= sprintf(" AND DATUM = '%s'", date("Y-m-d"));        // starttoren mag alleen vandaag opvragen			}
        }

        foreach ($params as $key => $value) {
            switch ($key) {
                case "SORT" :
                {
                    if (strpos(strtoupper($value), 'UPDATE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos(strtoupper($value), 'DELETE') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    if (strpos($value, ';') !== false)
                        throw new Exception("405;SORT is onjuist;");

                    $orderby = sprintf(" ORDER BY %s ", $value);

                    Debug(__FILE__, __LINE__, sprintf("%s: SORT='%s'", $functie, $value));
                    break;
                }
                case "MAX" :
                {
                    $max = isINT($value, "MAX");

                    if ($max > 0) {
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

                case "LID_ID" :
                {
                    $lidID = isINT($value, "LID_ID");
                    $where = $where . sprintf(" AND ((VLIEGER_ID = '%d') OR (INZITTENDE_ID = '%d'))", $lidID, $lidID);

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                    break;
                }
            }
        }


        if ((is_null($beginDatum)) && (is_null($eindDatum))) {
            $where .= " AND DATE(DATUM) >= ? ";
            array_push($query_params, date("Y") . "-01-01");
            $where .= " AND DATE(DATUM) <= ? ";
            array_push($query_params, date("Y") . "-12-31");
        }

        $query = "
			SELECT 
				%s 
			FROM
				`startlijst_view` " . $where . " GROUP BY DATUM " . $orderby;

        $velden = "DATUM, COUNT(*) AS STARTS, SEC_TO_TIME(SUM(TIME_TO_SEC(STR_TO_DATE(DUUR, '%H:%i') ))) AS VLIEGTIJD";

        $retVal = array();

        $retVal['totaal'] = $this->Count("SELECT COUNT(*) AS totaal FROM (" . $query . ") AS d", $query_params);        // wijkt af ivm de GROUP BY die opgenomen is in de query
        $retVal['laatste_aanpassing'] = $this->LaatsteAanpassing($query, $query_params);
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

        if ($alleenLaatsteAanpassing) {
            $retVal['dataset'] = null;
            return $retVal;
        } else {
            if ($limit > 0)
                $query .= sprintf(" LIMIT 0 , %d ", $limit);

            $rquery = sprintf($query, $velden);
            parent::DbOpvraag($rquery, $query_params);
            $retVal['dataset'] = parent::DbData();

            $retVal['hash'] = hash("crc32", json_encode($retVal));
            Debug(__FILE__, __LINE__, sprintf("HASH=%s", $retVal['hash']));

            if ($retVal['hash'] == $hash)
                throw new Exception(sprintf("%d;Dataset ongewijzigd;", $app_settings['dataNotModified']));

            for ($i = 0; $i < count($retVal['dataset']); $i++) {
                if (isset($retVal['dataset'][$i]['STARTS']))
                    $retVal['dataset'][$i]['STARTS'] = $retVal['dataset'][$i]['STARTS'] * 1;

                if (!is_null($retVal['dataset'][$i]['VLIEGTIJD']))
                    $retVal['dataset'][$i]['VLIEGTIJD'] = substr($retVal['dataset'][$i]['VLIEGTIJD'], 0, 5);    // alleen hh:mm
            }
            return $retVal;
        }
        return null;  // Hier komen we nooit :-)
    }

    /*
    Controleer of start en landingstijden juist zijn
    */
    function StartLandingTijdenValidatie($StartlijstData)
    {
        $functie = "Startlijst.StartLandingTijdenValidatie";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($StartlijstData, true)));

        if (!array_key_exists('STARTTIJD', $StartlijstData) && !array_key_exists('LANDINGSTIJD', $StartlijstData))
            return; // Starttijd en landingstijd zijn niet ingegeven, er is niets te doen


        if (array_key_exists('STARTTIJD', $StartlijstData) && array_key_exists('LANDINGSTIJD', $StartlijstData)) {
            if (($StartlijstData['STARTTIJD'] == null) && ($StartlijstData['LANDINGSTIJD'] != null))
                throw new Exception("406;Starttijd ontbreekt;");


            if (($StartlijstData['STARTTIJD'] != null) && ($StartlijstData['LANDINGSTIJD'] != null)) {
                $hms = explode(":", $StartlijstData['STARTTIJD']);
                $s = $hms[0] * 60 + $hms[1];


                $hms = explode(":", $StartlijstData['LANDINGSTIJD']);
                $l = $hms[0] * 60 + $hms[1];

                if ($l <= $s)
                    throw new Exception(sprintf("409;Landingstijd %s moet later zijn dan de starttijd %s;",
                        $StartlijstData['STARTTIJD'], $StartlijstData['LANDINGSTIJD']));

                return;        // er is niets meer te doen start & landingstijden zijn ingegeven en goed.
            }
        }

        // Er is eerder een start ingevoerd
        if (array_key_exists('ID', $StartlijstData)) {
            $start = $this->GetObject($StartlijstData['ID']);

            if (array_key_exists('STARTTIJD', $StartlijstData))
                $start['STARTTIJD'] = $StartlijstData['STARTTIJD'];

            if (array_key_exists('LANDINGSTIJD', $StartlijstData))
                $start['LANDINGSTIJD'] = $StartlijstData['LANDINGSTIJD'];

            if (($start['STARTTIJD'] == null) && ($start['LANDINGSTIJD'] != null))
                throw new Exception("406;Starttijd ontbreekt;");

            if (($start['STARTTIJD'] != null) && ($start['LANDINGSTIJD'] != null)) {
                $hms = explode(":", $start['STARTTIJD']);
                $s = $hms[0] * 60 + $hms[1];

                $hms = explode(":", $start['LANDINGSTIJD']);
                $l = $hms[0] * 60 + $hms[1];

                if ($l <= $s)
                    throw new Exception(sprintf("409;Landingstijd %s moet later zijn dan de starttijd %s;",
                        $start['STARTTIJD'], $start['LANDINGSTIJD']));
            }
        }
    }

    /*
    Aanmelden van vliegtuig voor de specifieke vliegdag
    */
    function AanmeldenVliegtuig($startData)
    {
        $functie = "Startlijst.AanmeldenVliegtuig";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($startData, true)));

        $record = array();
        $record['DATUM'] = $startData['DATUM'];

        $aVliegtuigen = MaakObject('AanwezigVliegtuigen');
        $record['VLIEGTUIG_ID'] = $startData['VLIEGTUIG_ID'];
        $record['VELD_ID'] = $startData['VELD_ID'];
        $aVliegtuigen->Aanmelden($record);
    }

    /*
    Aanmelden van vlieger / inzittende / vliegtuig voor de vliegdag
    */
    function Aanmeldingen($startData)
    {
        $functie = "Startlijst.Aanmeldingen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($startData, true)));

        $login = MaakObject('Login');
        $refLeden = MaakObject('Leden');

        $record = array();
        $record['DATUM'] = $startData['DATUM'];
        $record['VELD_ID'] = $startData['VELD_ID'];

        $aLeden = MaakObject('AanwezigLeden');
        if (array_key_exists('VLIEGER_ID', $startData)) {
            if (isINT($startData['VLIEGER_ID']) !== false) {
                Debug(__FILE__, __LINE__, sprintf("%s VLIEGER_ID=%s", $functie, $startData['VLIEGER_ID']));
                if (($this->heeftDataToegang($startData['DATUM'])) || ($startData['VLIEGER_ID'] == $login->getUserFromSession())) {
                    $rlObj = $refLeden->GetObject($startData['VLIEGER_ID'], true);
                    Debug(__FILE__, __LINE__, sprintf("%s LIDTYPE_ID=%s", $functie, $rlObj['LIDTYPE_ID']));

                    switch ($rlObj['LIDTYPE_ID']) {
                        case "600":
                            break;    // Diverse, niet aanmelden
                        case "607":
                            break;    // Zusterclub, niet aanmelden
                        case "609":
                            break;    // Nieuw lid, niet aanmelden
                        case "610":
                            break;    // Oprotkabel, niet aanmelden
                        case "612":
                            break;    // Penningmeester, niet aanmelden
                        default:
                            $record['LID_ID'] = $startData['VLIEGER_ID'];
                            $record['VELD_ID'] = $startData['VELD_ID'];

                            $refVliegtuigen = MaakObject('Vliegtuigen');
                            $rvObj = $refVliegtuigen->GetObject($startData['VLIEGTUIG_ID']);

                            if ($rvObj['CLUBKIST'] == 1)
                                $record['VOORKEUR_VLIEGTUIG_TYPE'] = $rvObj['TYPE_ID'];
                            else
                                $record['OVERLAND_VLIEGTUIG_ID'] = $startData['VLIEGTUIG_ID'];

                            $aLeden->Aanmelden($record);
                            break;
                    }
                }
            }
        }

        if (array_key_exists('INZITTENDE_ID', $startData)) {
            if (isINT($startData['INZITTENDE_ID']) !== false) {
                Debug(__FILE__, __LINE__, sprintf("%s INZITTENDE_ID=%s", $functie, $startData['INZITTENDE_ID']));
                if (($this->heeftDataToegang($startData['DATUM'])) || ($startData['INZITTENDE_ID'] == $login->getUserFromSession())) {
                    $rlObj = $refLeden->GetObject($startData['INZITTENDE_ID'], true);
                    Debug(__FILE__, __LINE__, sprintf("%s LIDTYPE_ID=%s", $functie, $rlObj['LIDTYPE_ID']));

                    switch ($rlObj['LIDTYPE_ID']) {
                        case "600":
                            break;    // Diverse, niet aanmelden
                        case "607":
                            break;    // Zusterclub, niet aanmelden
                        case "609":
                            break;    // Nieuw lid, niet aanmelden
                        case "610":
                            break;    // Oprotkabel, niet aanmelden
                        case "612":
                            break;    // Penningmeester, niet aanmelden
                        default:
                            //  geen vliegtuig en type zetten voor de inzittende
                            unset($record['OVERLAND_VLIEGTUIG_ID']);
                            unset($record['VOORKEUR_VLIEGTUIG_TYPE']);

                            $record['LID_ID'] = $startData['INZITTENDE_ID'];
                            $record['VELD_ID'] = $startData['VELD_ID'];
                            $aLeden->Aanmelden($record);
                            break;
                    }
                }
            }
        }

        // Als er losse namen zijn ingevoerd, dan opslaan als gast voor de vliegdag
        // maakt het invoeren voor de volgende starts makkelijker
        if (array_key_exists('VLIEGER_NAAM', $startData)) {
            if (isset($startData['VLIEGER_NAAM'])) {
                $g = MaakObject('Gasten');
                $gasten = $g->GetObjects(array(
                    'BEGIN_DATUM' => $startData['DATUM'],
                    'EIND_DATUM' => $startData['DATUM']
                ));
                $gevonden = false;
                foreach ($gasten['dataset'] as $gast) {
                    if ($gast == $startData['VLIEGER_NAAM']) {
                        $gevonden = true;
                        break;
                    }
                }
                if (!$gevonden) {
                    $record = array();
                    $record['DATUM'] = $startData['DATUM'];
                    $record['NAAM'] = $startData['VLIEGER_NAAM'];
                    $record['VELD_ID'] = $startData['VELD_ID'];

                    if (array_key_exists('OPMERKINGEN', $startData))
                        $record['OPMERKINGEN'] = $startData['OPMERKINGEN'];

                    $g->AddObject($record);
                }
            }
        }

        if (array_key_exists('INZITTENDE_NAAM', $startData)) {
            if (isset($startData['INZITTENDE_NAAM'])) {
                $g = MaakObject('Gasten');
                $gasten = $g->GetObjects(array(
                    'BEGIN_DATUM' => $startData['DATUM'],
                    'EIND_DATUM' => $startData['DATUM']
                ));
                $gevonden = false;
                foreach ($gasten['dataset'] as $gast) {
                    if ($gast == $startData['INZITTENDE_NAAM']) {
                        $gevonden = true;
                        break;
                    }
                }
                if (!$gevonden) {
                    $record = array();
                    $record['DATUM'] = $startData['DATUM'];
                    $record['NAAM'] = $startData['INZITTENDE_NAAM'];
                    $record['VELD_ID'] = $startData['VELD_ID'];

                    if (array_key_exists('OPMERKINGEN', $startData))
                        $record['OPMERKINGEN'] = $startData['OPMERKINGEN'];

                    $g->AddObject($record);
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // Bepaal het volgnummer van de dag
    function NieuwDagNummer($datum)
    {
        $functie = "Startlijst.NieuwDagNummer";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $datum));

        parent::DbOpvraag("
				SELECT 
					DAGNUMMER + 1 AS NIEUW_DAGNUMMER
				FROM 
					oper_startlijst
				WHERE 
					((DATUM = '" . $datum . "')) 
				ORDER BY 
					DAGNUMMER DESC
				LIMIT 1;");

        $dagnr = parent::DbData();
        if (count($dagnr) > 0)
            return $dagnr[0]['NIEUW_DAGNUMMER'];
        else
            return 1;
    }

    /*
    Als er een intructeur achterin zit, is het een instructie vlucht
    */
    function InstructieVlucht($input, $record)
    {
        $functie = "Startlijst.InstructieVlucht";
        Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, print_r($input, true), print_r($record, true)));


        $retVal = $record;

        $zitplaatsen = -1;
        if (array_key_exists('VLIEGTUIG_ID', $record)) {
            $rv = MaakObject('Vliegtuigen');
            $rvObj = $rv->GetObject($record['VLIEGTUIG_ID']);
            $zitplaatsen = $rvObj['ZITPLAATSEN'] * 1;
        }

        if ($zitplaatsen == 1) {
            $retVal['INSTRUCTIEVLUCHT'] = 0;
        } else {
            $field = 'INSTRUCTIEVLUCHT';
            if (array_key_exists($field, $input)) {
                $retVal[$field] = isBOOL($input[$field], $field, true);

                $inzittende_id = -1;
                if (array_key_exists('INZITTENDE_ID', $record)) {
                    $inzittende_id = $record['INZITTENDE_ID'];
                } else if (array_key_exists('ID', $record)) {
                    $start = $this->GetObject($record['ID']);
                    $inzittende_id = $start['INZITTENDE_ID'];
                }


                if ($inzittende_id > 0) {
                    $refLeden = MaakObject('Leden');
                    $lid = $refLeden->GetObject($inzittende_id, true);

                    if (($lid['INSTRUCTEUR'] != true) && ($retVal['INSTRUCTIEVLUCHT'] == 1)) {
                        throw new Exception("409;Instructie vlucht kan alleen door instructeur worden gedaan");
                    }
                }
            }
        }
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($record, true)));
        return $retVal;
    }

    /*
    Copieer data van request naar velden van het record
    */
    function RequestToRecord($input)
    {
        $record = array();
        $l = MaakObject('Login');

        $field = 'ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field);

        $field = 'DATUM';
        if (array_key_exists($field, $input))
            $record[$field] = isDATE($input[$field], $field);

        $field = 'DAGNUMMER';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field);

        $field = 'VLIEGTUIG_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Vliegtuigen");

        $field = 'STARTTIJD';
        if (array_key_exists($field, $input))
            $record[$field] = isTIME($input[$field], $field, true);

        $field = 'LANDINGSTIJD';
        if (array_key_exists($field, $input))
            $record[$field] = isTIME($input[$field], $field, true);

        $field = 'STARTMETHODE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'VLIEGER_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, 'Leden');

        $field = 'INZITTENDE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, 'Leden');


        if (array_key_exists('VLIEGERNAAM', $input))
            $record['VLIEGERNAAM'] = $input['VLIEGERNAAM'];

        if (array_key_exists('INZITTENDENAAM', $input))
            $record['INZITTENDENAAM'] = $input['INZITTENDENAAM'];

        $field = 'SLEEPKIST_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Vliegtuigen");

        $field = 'SLEEP_HOOGTE';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true);

        $field = 'PAX';
        if (array_key_exists($field, $input)) {
            $record[$field] = isBOOL($input[$field], $field, true);
        }

        if (($l->isBeheerder()) || ($l->isCIMT()) || ($l->isInstructeur() == true)) {
            $field = 'CHECKSTART';
            if (array_key_exists($field, $input))
                $record[$field] = isBOOL($input[$field], $field, true);
        }

        $field = 'VELD_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, 'Types');

        $field = 'BAAN_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, 'Types');

        if (array_key_exists('OPMERKINGEN', $input))
            $record['OPMERKINGEN'] = $input['OPMERKINGEN'];

        if (array_key_exists('EXTERNAL_ID', $input))
            $record['EXTERNAL_ID'] = $input['EXTERNAL_ID'];


        $record = $this->InstructieVlucht($input, $record);

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
            $retVal['ID'] = $record['ID'] * 1;

        if (isset($record['DAGNUMMER']))
            $retVal['DAGNUMMER'] = $record['DAGNUMMER'] * 1;

        if (isset($record['VLIEGTUIG_ID']))
            $retVal['VLIEGTUIG_ID'] = $record['VLIEGTUIG_ID'] * 1;

        if (isset($record['STARTMETHODE_ID']))
            $retVal['STARTMETHODE_ID'] = $record['STARTMETHODE_ID'] * 1;

        if (isset($record['VLIEGER_ID']))
            $retVal['VLIEGER_ID'] = $record['VLIEGER_ID'] * 1;

        if (isset($record['INZITTENDE_ID']))
            $retVal['INZITTENDE_ID'] = $record['INZITTENDE_ID'] * 1;

        if (isset($record['SLEEPKIST_ID']))
            $retVal['SLEEPKIST_ID'] = $record['SLEEPKIST_ID'] * 1;

        if (isset($record['SLEEP_HOOGTE']))
            $retVal['SLEEP_HOOGTE'] = $record['SLEEP_HOOGTE'] * 1;

        if (isset($record['VELD_ID']))
            $retVal['VELD_ID'] = $record['VELD_ID'] * 1;

        if (isset($record['BAAN_ID']))
            $retVal['BAAN_ID'] = $record['BAAN_ID'] * 1;

        if (isset($record['VLIEGER_LIDTYPE_ID']))
            $retVal['VLIEGER_LIDTYPE_ID'] = $record['VLIEGER_LIDTYPE_ID'] * 1;

        if (isset($record['INZITTENDE_LIDTYPE_ID']))
            $retVal['INZITTENDE_LIDTYPE_ID'] = $record['INZITTENDE_LIDTYPE_ID'] * 1;

        if (isset($record['VLIEGTUIG_TYPE_ID']))
            $retVal['VLIEGTUIG_TYPE_ID'] = $record['VLIEGTUIG_TYPE_ID'] * 1;

        if (isset($record['STARTTIJD']))
            $retVal['STARTTIJD'] = substr($record['STARTTIJD'], 0, 5);    // alleen hh:mm

        if (isset($record['LANDINGSTIJD']))
            $retVal['LANDINGSTIJD'] = substr($record['LANDINGSTIJD'], 0, 5);    // alleen hh:mm

        // booleans
        if (isset($record['VERWIJDERD']))
            $retVal['VERWIJDERD'] = $record['VERWIJDERD'] == "1" ? true : false;

        if (isset($record['CLUBKIST']))
            $retVal['CLUBKIST'] = $record['CLUBKIST'] == "1" ? true : false;

        if (isset($record['DDWV']))
            $retVal['DDWV'] = $record['DDWV'] == "1" ? true : false;

        if (isset($record['CHECKSTART']))
            $retVal['CHECKSTART'] = $record['CHECKSTART'] == "1" ? true : false;

        if (isset($record['PAX']))
            $retVal['PAX'] = $record['PAX'] == "1" ? true : false;

        if (isset($record['INSTRUCTIEVLUCHT']))
            $retVal['INSTRUCTIEVLUCHT'] = $record['INSTRUCTIEVLUCHT'] == "1" ? true : false;

        return $retVal;
    }
}
