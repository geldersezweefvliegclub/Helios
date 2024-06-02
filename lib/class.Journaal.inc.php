<?php

class Journaal extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "oper_journaal";
        $this->dbView = "journaal_view";
        $this->Naam = "Journaal";
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
				`DATUM` datetime  NOT NULL default CURRENT_TIMESTAMP,
                `VLIEGTUIG_ID` mediumint UNSIGNED NULL,
                `ROLLEND_ID` mediumint UNSIGNED NULL,
				`TITEL` varchar(250) DEFAULT NULL,
				`OMSCHRIJVING` text DEFAULT NULL,
				`CATEGORIE_ID` mediumint UNSIGNED NULL,
				`STATUS_ID` mediumint UNSIGNED NULL,
				
                `MELDER_ID` mediumint UNSIGNED NULL,
                `TECHNICUS_ID` mediumint UNSIGNED NULL,
                `AFGETEKEND_ID` mediumint UNSIGNED NULL,
                            
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`VLIEGTUIG_ID`),
					INDEX (`STATUS_ID`),
					INDEX (`DATUM`),
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (ROLLEND_ID) REFERENCES ref_types(ID),
					FOREIGN KEY (CATEGORIE_ID) REFERENCES ref_types(ID),
					FOREIGN KEY (STATUS_ID) REFERENCES ref_types(ID),
					FOREIGN KEY (VLIEGTUIG_ID) REFERENCES ref_vliegtuigen(ID),
					FOREIGN KEY (MELDER_ID) REFERENCES ref_leden(ID),
					FOREIGN KEY (TECHNICUS_ID) REFERENCES ref_leden(ID),
					FOREIGN KEY (AFGETEKEND_ID) REFERENCES ref_leden(ID)
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
            m.*,
			`tr`.`OMSCHRIJVING` AS `ROLLEND`,
			`ts`.`OMSCHRIJVING` AS `STATUS`,
			`ts`.`CODE` AS `STATUS_CODE`,
			`tc`.`OMSCHRIJVING` AS `CATEGORIE`,
			`tc`.`CODE` AS `CATEGORIE_CODE`,
			`lm`.`NAAM` AS `MELDER`,
			`lt`.`NAAM` AS `TECHNICUS`,
			`la`.`NAAM` AS `AFGETEKEND`,
			CONCAT(IFNULL(`v`.`REGISTRATIE`,''),' (',IFNULL(`v`.`CALLSIGN`,''),')') AS `REG_CALL`
			
		FROM
			`%s` `m`
			LEFT JOIN `ref_types` `tr` ON (`m`.`ROLLEND_ID` = `tr`.`ID`)
			LEFT JOIN `ref_types` `ts` ON (`m`.`STATUS_ID` = `ts`.`ID`)
			LEFT JOIN `ref_types` `tc` ON (`m`.`CATEGORIE_ID` = `tc`.`ID`)
			LEFT JOIN `ref_leden` `lm` ON (`m`.`MELDER_ID` = `lm`.`ID`)
			LEFT JOIN `ref_leden` `lt` ON (`m`.`TECHNICUS_ID` = `lt`.`ID`)
			LEFT JOIN `ref_leden` `la` ON (`m`.`AFGETEKEND_ID` = `la`.`ID`)
			LEFT JOIN `ref_vliegtuigen` `v`  ON (`m`.`VLIEGTUIG_ID` = `v`.`ID`)
		WHERE
			`m`.`VERWIJDERD` = %d 
		ORDER BY  `m`.`STATUS_ID`, `m`.`DATUM` DESC;";

        parent::DbUitvoeren("DROP VIEW IF EXISTS journaal_view");
        parent::DbUitvoeren(sprintf($query, "journaal_view", $this->dbTable, 0));

        parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_journaal_view");
        parent::DbUitvoeren(sprintf($query, "verwijderd_journaal_view", $this->dbTable, 1));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Journaal.GetObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));

        if ($ID == null)
            throw new Exception("406;Geen ID in aanroep;");

        $conditie = array();
        $conditie['ID'] = isINT($ID, "ID");

        $obj = parent::GetSingleObject($conditie);
        Debug(__FILE__, __LINE__, print_r($obj, true));

        if ($obj == null)
            throw new Exception(sprintf("404;Record niet gevonden (%s, '%s');", $this->Naam, json_encode($conditie)));

        $obj = $this->RecordToOutput($obj);
        return $obj;
    }

    /*
	Haal een dataset op met records als een array uit de database.
	*/
    function GetObjects($params)
    {
        global $app_settings;

        $functie = "Journaal.GetObjects";
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

        foreach ($params as $key => $value)
        {
            switch ($key)
            {
                case "ID" :
                {
                    $id = isINT($value, "ID");
                    $where .= " AND ID=?";
                    $query_params[] = $id;

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

                    $velden = strtoupper($value);
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
                    $query_params[] = $beginDatum;

                    Debug(__FILE__, __LINE__, sprintf("%s: BEGIN_DATUM='%s'", $functie, $beginDatum));
                    break;
                }
                case "EIND_DATUM" :
                {
                    $eindDatum = isDATE($value, "EIND_DATUM");

                    $where .= " AND DATE(DATUM) <= ? ";
                    $query_params[] = $eindDatum;

                    Debug(__FILE__, __LINE__, sprintf("%s: EIND_DATUM='%s'", $functie, $eindDatum));
                    break;
                }
                case "VLIEGTUIG_ID" :
                {
                    isCSV($value, "VLIEGTUIG_ID");
                    $where .= sprintf(" AND VLIEGTUIG_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: VLIEGTUIG_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "LID_ID" :
                {
                    isCSV($value, "LID_ID");
                    $where .= sprintf(" AND LID_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "MELDER_ID" :
                {
                    isCSV($value, "LID_ID");
                    $where .= sprintf(" AND MELDER_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "TECHNICUS_ID" :
                {
                    isCSV($value, "LID_ID");
                    $where .= sprintf(" AND TECHNICUS_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: TECHNICUS_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "STATUS_ID" :
                {
                    isCSV($value, "STATUS_ID");
                    $where .= sprintf(" AND STATUS_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: TECHNICUS_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "CATEGORIE_ID" :
                {
                    isCSV($value, "CATEGORIE_ID");
                    $where .= sprintf(" AND CATEGORIE_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: CATEGORIE_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "ROLLEND_ID" :
                {
                    isCSV($value, "ROLLEND_ID");
                    $where .= sprintf(" AND ROLLEND_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: ROLLEND_ID='%s'", $functie, trim($value)));
                    break;
                }
                case "ROLLEND" :
                {
                    $rollend = isBool($value, "ROLLEND");

                    $where .= " AND ";
                    $where .= ($rollend == 0) ?  "ROLLEND_ID IS NULL" : "ROLLEND_ID IS NOT NULL";

                    Debug(__FILE__, __LINE__, sprintf("%s: ROLLEND='%s'", $functie, $rollend));
                    break;
                }
                case "VLIEGEND" :
                {
                    $vliegend = isBool($value, "VLIEGEND");

                    $where .= " AND ";
                    $where .= ($vliegend == 0) ?  "VLIEGTUIG_ID IS NULL" : "VLIEGTUIG_ID IS NOT NULL";

                    Debug(__FILE__, __LINE__, sprintf("%s: VLIEGEND='%s'", $functie, $vliegend));
                    break;
                }
                case "SELECTIE" :
                {
                    $s = "%" . trim($value) . "%";

                    $where .= " AND ((MELDER LIKE ?) ";      $query_params[] = $s;
                    $where .= "  OR (TITEL LIKE ?) ";        $query_params[] = $s;
                    $where .= "  OR (ROLLEND LIKE ?) ";      $query_params[] = $s;
                    $where .= "  OR (REG_CALL LIKE ?) ";     $query_params[] = $s;
                    $where .= "  OR (OMSCHRIJVING LIKE ?)) ";$query_params[] = $s;

                    Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                }
            }
        }

        $query = "
			SELECT 
				%s
			FROM
				`####journaal_view` " . $where . $orderby;
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
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/
    function AddObject($journaal)
    {
        global $app_settings;

        $functie = "Journaal.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($journaal, true)));

        $l = MaakObject('Login');

        if ($journaal == null)
            throw new Exception("406;Journaal data moet ingevuld zijn;");

        if (!isset($journaal['TITEL']))
            throw new Exception("406;TITEL is verplicht;");

        if (!isset($journaal['OMSCHRIJVING']))
            throw new Exception("406;OMSCHRIJVING is verplicht;");

        if (!isset($journaal['VLIEGTUIG_ID']) && !isset($journaal['ROLLEND_ID']))
            throw new Exception("406;VLIEGTUIG_ID of ROLLEND_ID is verplicht;");

        if (!isset($journaal['STATUS_ID']))
            throw new Exception("406;STATUS_ID is verplicht;");

        if (!isset($journaal['CATEGORIE_ID']))
            throw new Exception("406;CATEGORIE_ID is verplicht;");

        if (!isset($journaal['DATUM']))
            $journaal['DATUM'] = date('Y-m-d');

        if (!isset($journaal['MELDER_ID']))
        {
            $l = MaakObject('Login');
            $journaal['MELDER_ID'] = $l->getUserFromSession();
        }

        // Neem data over uit aanvraag
        $record = $this->RequestToRecord($journaal);
        $id = parent::DbToevoegen($record);

        Debug(__FILE__, __LINE__, sprintf("Journaal toegevoegd id=%d", $id));
        return $id;
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($journaal)
    {
        global $app_settings;

        $functie = "Journaal.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($journaal, true)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT() && (!$l->isTechnicus()))
            throw new Exception("401;Geen rechten;");

        if ($journaal == null)
            throw new Exception("406;Journaal data moet ingevuld zijn;");

        if (!array_key_exists('ID', $journaal))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($journaal['ID'], "ID");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($journaal);

        parent::DbAanpassen($id, $d);
        if (parent::NumRows() === 0)
            throw new Exception(sprintf("404;Record niet gevonden (%s, '%s');", $this->Naam, $id));

        return $this->GetObject($id);
    }

    /*
    Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
    Het veld VERWIJDERD wordt op "1" gezet.
    */
    function VerwijderObject($id, $verificatie = true)
    {
        $functie = "Journaal.VerwijderObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT())
            throw new Exception("401;Geen rechten;");

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
        $functie = "Journaal.HerstelObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        isCSV($id, "ID");
        parent::HerstelVerwijderd($id);
    }

    /*
	Copieer data van request naar velden van het record
	*/
    function RequestToRecord($input)
    {
        $record = array();

        $field = 'ROLLEND_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        $field = 'STATUS_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Types");

        $field = 'CATEGORIE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Types");

        $field = 'MELDER_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Leden");

        $field = 'TECHNICUS_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Leden");

        $field = 'AFGETEKEND_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Leden");

        $field = 'VLIEGTUIG_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Vliegtuigen");

        $field = 'TITEL';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];

        $field = 'OMSCHRIJVING';
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

        if (isset($record['ROLLEND_ID']))
            $retVal['ROLLEND_ID']  = $record['ROLLEND_ID'] * 1;

        if (isset($record['CATEGORIE_ID']))
            $retVal['CATEGORIE_ID']  = $record['CATEGORIE_ID'] * 1;

        if (isset($record['STATUS_ID']))
            $retVal['STATUS_ID']  = $record['STATUS_ID'] * 1;

        if (isset($record['VLIEGTUIG_ID']))
            $retVal['VLIEGTUIG_ID']  = $record['VLIEGTUIG_ID'] * 1;

        if (isset($record['MELDER_ID']))
            $retVal['MELDER_ID']  = $record['MELDER_ID'] * 1;

        if (isset($record['TECHNICUS_ID']))
            $retVal['TECHNICUS_ID']  = $record['TECHNICUS_ID'] * 1;

        if (isset($record['AFGETEKEND_ID']))
            $retVal['AFGETEKEND_ID']  = $record['AFGETEKEND_ID'] * 1;

        // booleans
        if (isset($record['VERWIJDERD']))
            $retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

        return $retVal;
    }
}
