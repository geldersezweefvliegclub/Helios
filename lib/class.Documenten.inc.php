<?php

class Documenten extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "documenten";
        $this->dbView = "documenten_view";
        $this->Naam = "Documenten";
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
				`VOLGORDE` tinyint UNSIGNED DEFAULT NULL,
				`GROEP_ID` mediumint UNSIGNED NOT NULL,
				`TEKST` varchar(250) DEFAULT NULL,
                `URL` varchar(250) DEFAULT NULL,
                `LID_ID` mediumint UNSIGNED NULL,
                
                `LEGE_REGEL` tinyint UNSIGNED NOT NULL DEFAULT '0',
                `ONDERSTREEP` tinyint UNSIGNED NOT NULL DEFAULT '0',
                `BOVEN` tinyint UNSIGNED NOT NULL DEFAULT '0',
                
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`GROEP_ID`),
					INDEX (`VERWIJDERD`),

					FOREIGN KEY (GROEP_ID) REFERENCES ref_types(ID)
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
            d.*,
			`t`.`OMSCHRIJVING` AS `GROEP`
		FROM
			`%s` `d`
			LEFT JOIN `ref_types` `t` ON (`d`.`GROEP_ID` = `t`.`ID`)
		WHERE
			`d`.`VERWIJDERD` = %d 
		ORDER BY `t`.`SORTEER_VOLGORDE`, `d`.`VOLGORDE`;";

        parent::DbUitvoeren("DROP VIEW IF EXISTS documenten_view");
        parent::DbUitvoeren(sprintf($query, "documenten_view", $this->dbTable, 0));

        parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_documenten_view");
        parent::DbUitvoeren(sprintf($query, "verwijderd_documenten_view", $this->dbTable, 1));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Documenten.GetObject";
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

        $functie = "Documenten.GetObjects";
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
                case "GROEPEN" :
                {
                    isCSV($value, "GROUPEN");
                    $where .= sprintf(" AND GROUPEN IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: GROUPEN='%s'", $functie, trim($value)));
                    break;
                }
                case "LID_ID" :
                {
                    isCSV($value, "LID_ID");
                    $where .= sprintf(" AND LID_ID IN(%s)", trim($value));

                    Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, trim($value)));
                    break;
                }
                default:
                {
                    throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
                }
            }
        }

        if (!array_key_exists('LID_ID', $params))
            $where .= " AND LID_ID is null ";

        $query = "
			SELECT 
				%s
			FROM
				`####documenten_view` " . $where . $orderby;
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
    function AddObject($document)
    {
        global $app_settings;

        $functie = "Documenten.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($document, true)));

        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT())
            throw new Exception("401;Geen rechten;");

        if ($document == null)
            throw new Exception("406;Document data moet ingevuld zijn;");

        if ((!array_key_exists('LEGE_REGEL', $document)) || ($document['LEGE_REGEL'] === false)) {
            if (!array_key_exists('TEKST', $document))
                throw new Exception("406;TEKST is verplicht;");
        }

        // Neem data over uit aanvraag
        $record = $this->RequestToRecord($document);

        if (isset($document['DOC_NAAM']) && isset($document['BASE64_DOC']))
        {
            if (isset($document['LID_ID'])) {
                $subdir = base64_encode(password_hash($document['LID_ID'], PASSWORD_BCRYPT, ['cost' => 12]));
                $directory = $app_settings['BaseDir'] . "documenten/" .  $subdir . "/";
                $filenaam = $directory . $document['LID_ID'] . "_" . $document['DOC_NAAM'];

                $record['URL'] = sprintf("%s://%s/documenten/%s/%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME'], $subdir,
                    $document['LID_ID'] . "_" . $document['DOC_NAAM']);
            }
            else {
                $directory = $app_settings['BaseDir'] . "documenten/";
                $filenaam = $directory . $document['DOC_NAAM'];
                $record['URL'] = sprintf("%s://%s/documenten/%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME'],
                    $document['DOC_NAAM']);

            }

            if (!is_dir($directory))
            {
                if (file_exists($directory))
                    throw new Exception(sprintf("500;LID_ID (%s) bestaat als bestand;", $document['LID_ID']));

                if (!mkdir($directory))
                    throw new Exception(sprintf("500;LID_ID (%s) kan niet aangemaakt worden als directory;", $document['LID_ID']));
            }


            Debug(__FILE__, __LINE__, sprintf("filenaam = %s", $filenaam));

            if (($document['OVERSCHRIJVEN'] === false) && (file_exists($filenaam))) {
                Debug(__FILE__, __LINE__, "bestand bestaat al");
                throw new Exception(sprintf("500;Bestand bestaat al (%s);", $document['DOC_NAAM']));
            }

            $bestand = base64_decode($document['BASE64_DOC']);
            file_put_contents($filenaam, $bestand);
        }
        $id = parent::DbToevoegen($record);

        Debug(__FILE__, __LINE__, sprintf("Document toegevoegd id=%d", $id));
        return $id;
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($document)
    {
        global $app_settings;

        $functie = "Documenten.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($document, true)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT())
            throw new Exception("401;Geen rechten;");

        if ($document == null)
            throw new Exception("406;Document data moet ingevuld zijn;");

        if (!array_key_exists('ID', $document))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($document['ID'], "ID");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($document);

        if (isset($document['DOC_NAAM']) && isset($document['BASE64_DOC']))
        {
            if (isset($document['LID_ID'])) {
                $subdir = base64_encode(password_hash($document['LID_ID'], PASSWORD_BCRYPT, ['cost' => 12]));
                $directory = $app_settings['BaseDir'] . "documenten/" .  $subdir . "/";
                $filenaam = $directory . $document['LID_ID'] . "_" . $document['DOC_NAAM'];

                $record['URL'] = sprintf("%s://%s/documenten/%s/%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME'], $subdir,
                    $document['LID_ID'] . "_" . $document['DOC_NAAM']);
            }
            else {
                $directory = $app_settings['BaseDir'] . "documenten/";
                $filenaam = $directory . $document['DOC_NAAM'];
                $record['URL'] = sprintf("%s://%s/documenten/%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME'],
                    $document['DOC_NAAM']);

            }
            Debug(__FILE__, __LINE__, sprintf("filenaam = %s", $filenaam));

            if (($document['OVERSCHRIJVEN'] === false) && (file_exists($filenaam))) {
                Debug(__FILE__, __LINE__, "bestand bestaat al");
                throw new Exception(sprintf("500;Bestand bestaat al (%s);", $document['DOC_NAAM']));
            }

            $bestand = base64_decode($document['BASE64_DOC']);
            file_put_contents($filenaam, $bestand);
        }

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
        $functie = "Documenten.VerwijderObject";
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
        $functie = "Documenten.HerstelObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s')", $functie, $id));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT())
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

        $field = 'GROEP_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Types");

        $field = 'VOLGORDE';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true);

        $field = 'LID_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true, "Leden");

        if (array_key_exists('TEKST', $input))
            $record['TEKST'] = $input['TEKST'];

        if (array_key_exists('URL', $input))
            $record['URL'] = $input['URL'];

        $field = 'LEGE_REGEL';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);

        if ($record['LEGE_REGEL'] == 1)     // lege regel, geen TEKST en geen URL
        {
            $record['URL'] = null;
            $record['TEKST'] = null;
        }

        $field = 'ONDERSTREEP';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);

        $field = 'BOVEN';
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

        if (isset($record['GROEP_ID']))
            $retVal['GROEP_ID']  = $record['GROEP_ID'] * 1;

        if (isset($record['LID_ID']))
            $retVal['LID_ID']  = $record['LID_ID'] * 1;

        // booleans
        if (isset($record['VERWIJDERD']))
            $retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

        if (isset($record['LEGE_REGEL']))
            $retVal['LEGE_REGEL']  = $record['LEGE_REGEL'] == "1" ? true : false;

        if (isset($record['ONDERSTREEP']))
            $retVal['ONDERSTREEP']  = $record['ONDERSTREEP'] == "1" ? true : false;

        if (isset($record['LEGE_REGEL']))
            $retVal['BOVEN']  = $record['BOVEN'] == "1" ? true : false;

        return $retVal;
    }
}
