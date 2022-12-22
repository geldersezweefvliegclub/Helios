<?php
class DagRapporten extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "oper_dagrapporten";
        $this->dbView = "dagrapport_view";
        $this->Naam = "Dag rapport";
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
				`VELD_ID` mediumint UNSIGNED NOT NULL,
				`INGEVOERD_ID` mediumint UNSIGNED NOT NULL,
				`INCIDENTEN` text DEFAULT NULL,  
				`VLIEGBEDRIJF` text DEFAULT NULL,
				`METEO` text DEFAULT NULL,
				`DIENSTEN` text DEFAULT NULL,
				`VERSLAG` text DEFAULT NULL,
				`ROLLENDMATERIEEL` text DEFAULT NULL,
				`VLIEGENDMATERIEEL` text DEFAULT NULL,
				
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`), 
					INDEX (`VERWIJDERD`),
					
				FOREIGN KEY (VELD_ID) REFERENCES ref_types(ID),
				FOREIGN KEY (INGEVOERD_ID) REFERENCES ref_leden(ID)
			)", $this->dbTable);
        parent::DbUitvoeren($query);

        if (isset($FillData))
        {
            $inject = array(
                "1, '####-04-28', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "2, '####-04-29', 902, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "3, '####-04-30', 902, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "4, '####-05-01', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "5, '####-05-02', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "6, '####-05-03', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "7, '####-05-04', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                "8, '####-05-05', 901, '%s', '%s', '%s', '%s', '%s', '%s', '%s'");

            $inject = str_replace("####", strval(date("Y")), $inject);		// aanwezigheid in dit jaar

            $i = 0;
            foreach ($inject as $record)
            {
                $fields = sprintf($record,
                    parent::fakeText(),
                    parent::fakeText(),
                    parent::fakeText(),
                    parent::fakeText(),
                    parent::fakeText(),
                    parent::fakeText(),
                    parent::fakeText());

                $query = sprintf("
						INSERT INTO `%s` (
							`ID`, 
							`DATUM`, 
							`VELD_ID`,  
							`INCIDENTEN`, 
							`VLIEGBEDRIJF`, 
							`METEO`, 
							`DIENSTEN`, 
							`VERSLAG`, 
							`ROLLENDMATERIEEL`, 
							`VLIEGENDMATERIEEL`) 
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
			dr.*,
			`l`.`NAAM` AS `INGEVOERD`,
			`T_Veld`.`CODE` AS `VELD_CODE`,
			`T_Veld`.`OMSCHRIJVING` AS  `VELD_OMS`		
		FROM
			`%s` `dr`
			LEFT JOIN `ref_types` `T_Veld` ON (`dr`.`VELD_ID` = `T_Veld`.`ID`)
			LEFT JOIN `ref_leden` `l` ON (`dr`.`INGEVOERD_ID` = `l`.`ID`)
		WHERE
			`dr`.`VERWIJDERD` = %d
		ORDER BY DATUM DESC;";

        parent::DbUitvoeren("DROP VIEW IF EXISTS dagrapport_view");
        parent::DbUitvoeren(sprintf($query, "dagrapport_view", $this->dbTable, 0));

        parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_dagrapport_view");
        parent::DbUitvoeren(sprintf($query, "verwijderd_dagrapport_view", $this->dbTable, 1));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "DagRapporten.GetObject";
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

        $functie = "DagRapporten.GetObjects";
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

        // Als ingelogde gebruiker geen bijzonder functie heeft, worden beperkte dataset opgehaald
        $l = MaakObject('Login');
        if (($l->isBeheerder() == true) || ($l->isInstaller() == true) || ($l->isInstructeur() == true) || ($l->isCIMT() == true))
        {
            // geen beperkingen voor deze gebruikers
        }
        else if (($l->isBeheerderDDWV() == true) || ($l->isDDWVCrew() == true))
        {
            // DagRapport voor DDWV is alleen op DDWV dagen bechikbaar
            $where .= " AND (DATUM IN (select DATUM from oper_rooster WHERE DDWV = 1))";

            if ($l->isDDWVCrew() == true)
            {
                // DDWV crew mag alleen DDWV dagen zien waar ze zelf dienst hadden
                $where .= sprintf(" AND (DATUM IN (select DATUM from oper_diensten WHERE LID_ID = %d))", $l->getUserFromSession());
            }
        }
        else
        {
            throw new Exception("401;Gebruiker mag dagrapport niet opvragen;");
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
				`####dagrapport_view`" . $where . $orderby;
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
        $functie = "DagRapporten.VerwijderObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s, %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

        if (!$this->heeftDataToegang())
            throw new Exception("401;Geen schrijfrechten;");

        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        isCSV($id, "ID");

        $l = MaakObject('Login');

        if (!$this->heeftDataToegang(null, false))
            throw new Exception("401;Geen schrijfrechten;");

        parent::MarkeerAlsVerwijderd($id, $verificatie);
    }

    /*
    Herstel van een verwijderd record
    */
    function HerstelObject($id)
    {
        $functie = "DagRapporten.HerstelObject";
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
    function AddObject($DagRapportData)
    {
        $functie = "DagRapporten.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($DagRapportData, true)));

        if ($DagRapportData == null)
            throw new Exception("406;DagRapportData data moet ingevuld zijn;");

        $where = "";
        $nieuw = true;
        if (array_key_exists('ID', $DagRapportData))
        {
            $id = isINT($DagRapportData['ID'], "ID");

            // ID is opgegeven, maar bestaat record?
            try 	// Als record niet bestaat, krijgen we een exception
            {
                $this->GetObject($id, null);
            }
            catch (Exception $e) {}

            if (parent::NumRows() > 0)
                throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
        }

        if (!array_key_exists('DATUM', $DagRapportData))
            throw new Exception("406;Datum is verplicht;");

        if (!array_key_exists('VELD_ID', $DagRapportData))
            throw new Exception("406;VELD_ID is verplicht;");

        $dagRapportDatum = isDATE($DagRapportData['DATUM'], "DATUM");

        if (!$this->heeftDataToegang($DagRapportData['DATUM']))
            throw new Exception("401;Geen schrijfrechten;");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($DagRapportData);

        $l = MaakObject('Login');
        $d['INGEVOERD_ID'] = $l->getUserFromSession();

        $id = parent::DbToevoegen($d);
        Debug(__FILE__, __LINE__, sprintf("DagRapport toegevoegd id=%d", $id));

        return $this->GetObject($id);
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($DagRapportData)
    {
        $functie = "DagRapporten.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, json_encode($DagRapportData)));

        if ($DagRapportData == null)
            throw new Exception("406;DagRapport data moet ingevuld zijn;");

        if (!array_key_exists('ID', $DagRapportData))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($DagRapportData['ID'], "ID");
        $di = $this->GetObject($id);

        if (!$this->heeftDataToegang($di['DATUM']))
            throw new Exception("401;Geen schrijfrechten;");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($DagRapportData);

        parent::DbAanpassen($id, $d);
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

        $field = 'VELD_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Types");

        if (array_key_exists('INCIDENTEN', $input))
            $record['INCIDENTEN'] = $input['INCIDENTEN'];

        if (array_key_exists('VLIEGBEDRIJF', $input))
            $record['VLIEGBEDRIJF'] = $input['VLIEGBEDRIJF'];

        if (array_key_exists('METEO', $input))
            $record['METEO'] = $input['METEO'];

        if (array_key_exists('DIENSTEN', $input))
            $record['DIENSTEN'] = $input['DIENSTEN'];

        if (array_key_exists('VERSLAG', $input))
            $record['VERSLAG'] = $input['VERSLAG'];

        if (array_key_exists('ROLLENDMATERIEEL', $input))
            $record['ROLLENDMATERIEEL'] = $input['ROLLENDMATERIEEL'];

        if (array_key_exists('VLIEGENDMATERIEEL', $input))
            $record['VLIEGENDMATERIEEL'] = $input['VLIEGENDMATERIEEL'];

        if (array_key_exists('INGEVOERD', $input))
            throw new Exception("405;INGEVOERD kan niet extern gezet worden;");

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
            $retVal['VERWIJDERD'] = $record['VERWIJDERD'] == "1" ? true : false;

        return $retVal;
    }
}
