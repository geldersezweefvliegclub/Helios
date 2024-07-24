<?php

class Agenda extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "oper_agenda";
        $this->dbView = "agenda_view";
        $this->Naam = "Agenda";
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
				`TIJD` time DEFAULT NULL,
				`KORT` text DEFAULT NULL,
				`OMSCHRIJVING` text DEFAULT NULL,
				`OPENBAAR` tinyint UNSIGNED NOT NULL DEFAULT '1',
				            
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`DATUM`)
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
            a.*

		FROM
			`%s` `a`
		WHERE
			`a`.`VERWIJDERD` = %d 
		ORDER BY  `a`.`DATUM`, `a`.`TIJD`;";

        parent::DbUitvoeren("DROP VIEW IF EXISTS agenda_view");
        parent::DbUitvoeren(sprintf($query, "agenda_view", $this->dbTable, 0));

        parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_agenda_view");
        parent::DbUitvoeren(sprintf($query, "verwijderd_agenda_view", $this->dbTable, 1));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Agenda.GetObject";
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

        $functie = "Agenda.GetObjects";
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
				`####agenda_view` " . $where . $orderby;
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
    function AddObject($agenda)
    {
        global $app_settings;

        $functie = "Agenda.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($agenda, true)));

        $l = MaakObject('Login');

        if ($agenda == null)
            throw new Exception("406;Agenda data moet ingevuld zijn;");

        if (!isset($agenda['DATUM']))
            throw new Exception("406;DATUM is verplicht;");

        if (!isset($agenda['KORT']))
            throw new Exception("406;KORT is verplicht;");

        // Neem data over uit aanvraag
        $record = $this->RequestToRecord($agenda);
        $id = parent::DbToevoegen($record);

        Debug(__FILE__, __LINE__, sprintf("Journaal toegevoegd id=%d", $id));
        return $id;
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($agenda)
    {
        global $app_settings;

        $functie = "Agenda.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($agenda, true)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isCIMT() && (!$l->isTechnicus()))
            throw new Exception("401;Geen rechten;");

        if ($agenda == null)
            throw new Exception("406;Journaal data moet ingevuld zijn;");

        if (!array_key_exists('ID', $agenda))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($agenda['ID'], "ID");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($agenda);

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
        $functie = "Agenda.VerwijderObject";
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
        $functie = "Agenda.HerstelObject";
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

        $field = 'DATUM';
        if (array_key_exists($field, $input))
            $record[$field] = isDATE($input[$field], $field);

        $field = 'TIJD';
        if (array_key_exists($field, $input))
            $record[$field] = isTIME($input[$field], $field, true);

        $field = 'OPENBAAR';
        if (array_key_exists($field, $input)) {
            $record[$field] = isBOOL($input[$field], $field, true);
        }

        $field = 'KORT';
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

        if (isset($record['TIJD']))
            $retVal['TIJD'] = substr($record['TIJD'], 0, 5);    // alleen hh:mm

        // booleans
        if (isset($record['OPENBAAR']))
            $retVal['OPENBAAR'] = $record['OPENBAAR'] == "1" ? true : false;

        if (isset($record['VERWIJDERD']))
            $retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

        return $retVal;
    }
}
