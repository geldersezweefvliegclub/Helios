<?php

class Facturen extends Helios
{
    function __construct()
    {
        parent::__construct();
        $this->dbTable = "oper_facturen";
        $this->dbView = "facturen_view";
        $this->Naam = "Facturen";
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
				`JAAR` smallint  NOT NULL,
                `LID_ID` mediumint UNSIGNED NULL,
                `NAAM` varchar(255) DEFAULT NULL,
				`LIDNR` varchar(10) DEFAULT NULL,
                `FACTUUR_NUMMER` varchar(50) DEFAULT NULL,
                `OMSCHRIJVING` varchar(50) DEFAULT NULL,
                `GEFACTUREERD` decimal  DEFAULT NULL,
                              
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
                `f`.*
            FROM
                `%s` `f` 
            WHERE
                `f`.`VERWIJDERD` = %d";

        parent::DbUitvoeren("DROP VIEW IF EXISTS facturen_view");
        parent::DbUitvoeren(sprintf($query, "facturen_view", $this->dbTable, 0));
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Facturen.GetObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));

        $l = MaakObject('Login');
        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

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

        $functie = "Facturen.GetObjects";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        $l = MaakObject('Login');
        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

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
                case "JAAR" :
                {
                    $id = isINT($value, "JAAR");
                    $where .= " AND (JAAR=? OR JAAR IS NULL)";
                    $query_params[] = $id;

                    Debug(__FILE__, __LINE__, sprintf("%s: JAAR='%s'", $functie, $id));
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

        $query = "
			SELECT 
				%s
			FROM
				`####facturen_view` " . $where . $orderby;
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

    function NogTeFactureren($jaar, $hash)
    {
        global $app_settings;

        $functie = "Facturen.NogTeFactureren";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $jaar));

        $l = MaakObject('Login');
        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        $velden = sprintf("
                `f`.*,
                `l`.`ID` AS `LID_ID`,
                `l`.`NAAM` AS `NAAM`,
                DATE_FORMAT(FROM_DAYS(DATEDIFF('%d-01-01',`l`.`GEBOORTE_DATUM`)), '%%Y')+0 AS LEEFTIJD,
                `l`.`LIDNR` AS `LIDNR`,
                `l`.`LIDTYPE_ID` AS `LIDTYPE_ID`,
                `t`.`OMSCHRIJVING` AS `LIDMAATSCHAP`,
                `t`.`BEDRAG` AS `CONTRIBUTIE`", $jaar);

        $query_params = array($jaar);

        $query = "
            SELECT 
                %s
            FROM
                `ref_leden` `l` LEFT JOIN
                `ref_types` `t`  ON `l`.`LIDTYPE_ID` = `t`.`ID` LEFT JOIN
                `oper_facturen` `f` ON `l`.`ID` = `f`.`LID_ID` AND `f`.`JAAR` = ?
            WHERE
                `l`.`VERWIJDERD` = 0 AND `l`.`LIDTYPE_ID` IN (602, 603, 604, 605, 606) AND  `f`.`ID` IS NULL 
            ORDER BY
                `t`.`SORTEER_VOLGORDE`, `l`.`NAAM`";

        $retVal = array();

        $retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
        $retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params,  "f.LAATSTE_AANPASSING");
        Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));

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

    /*
	Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
	*/
    function AddObject($factuur)
    {
        global $app_settings;

        $functie = "Facturen.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($factuur, true)));

        $l = MaakObject('Login');
        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($factuur == null)
            throw new Exception("406;Factuur data moet ingevuld zijn;");

        if (!isset($factuur['LID_ID']))
            throw new Exception("406;LID_ID is verplicht;");

        if (!isset($factuur['JAAR']))
            throw new Exception("406;JAAR is verplicht;");

        if (!isset($factuur['GEFACTUREERD']))
            throw new Exception("406;GEFACTUREERD is verplicht;");

        if (!isset($factuur['OMSCHRIJVING']))
            throw new Exception("406;OMSCHRIJVING is verplicht;");

        // Neem data over uit aanvraag
        $record = $this->RequestToRecord($factuur);
        $id = parent::DbToevoegen($record);

        Debug(__FILE__, __LINE__, sprintf("Factuur toegevoegd id=%d", $id));
        return $id;
    }

    function  AanmakenFacturen($data)
    {
        $functie = "Facturen.AanmakenFacturen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($data, true)));

        $l = MaakObject('Login');
        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($data == null)
            throw new Exception("406;Data moet ingevuld zijn;");

        if (!isset($data['JAAR']))
            throw new Exception("406;JAAR is verplicht;");

        if (!array_key_exists('LID_ID', $data))
            throw new Exception("406;LID_ID is verplicht;");

        $jaar = isINT($data['JAAR'],"JAAR");
        $ledenIDs = $data['LID_ID'];

        foreach ($ledenIDs as $ID) {
            isINT($ID,"LID_ID", false, "Leden");
        }

        $functie = "Facturen.AanmakenFacturen";
        Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $jaar, implode(":", $ledenIDs)));

        $typeObj = MaakObject('Types');
        $types = $typeObj->GetObjects(array("GROEP" => 6, "VELDEN" => "ID,OMSCHRIJVING,BEDRAG"));
        $lidmaatschappen = array();
        foreach ($types['dataset'] as $lidmaatschap)
        {
            $lidmaatschappen[$lidmaatschap['ID']] = $lidmaatschap;
        }
        Debug(__FILE__, __LINE__, print_r($lidmaatschappen, true));
        Debug(__FILE__, __LINE__, print_r($types, true));

        $ledenObj = MaakObject('Leden');

        foreach ($ledenIDs as $lidID)
        {
            $lid = $ledenObj->GetObject($lidID);

            if (array_key_exists($lid['LIDTYPE_ID'], $lidmaatschappen) == false)
                throw new Exception("404;Lidtype " .  $lid['NAAM'] . " niet gevonden;");

            $lidmaatschap = $lidmaatschappen[$lid['LIDTYPE_ID']];
            Debug(__FILE__, __LINE__, print_r($lidmaatschap, true));

            if ($lidmaatschap['BEDRAG'] == null)
                throw new Exception("404;Bedrag voor lidtype " .  $lidmaatschap['OMSCHRIJVING'] . " niet ingevuld;");

            $factuur = array();
            $factuur['JAAR'] = $jaar;
            $factuur['LID_ID'] = $lidID;
            $factuur['LIDNR'] = $lid['LIDNR'];
            $factuur['NAAM'] = $lid['NAAM'];
            $factuur['OMSCHRIJVING'] = "Lidmaatschap " . $jaar . " - " . $lidmaatschap['OMSCHRIJVING'];
            $factuur['GEFACTUREERD'] = $lidmaatschap['BEDRAG'];
            $this->AddObject($factuur);
        }
    }

    function UploadFactuur($data)
    {
        $functie = "Facturen.UploadFactuur";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($data, true)));

        $l = MaakObject('Login');

        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($data == null)
            throw new Exception("406;Data moet ingevuld zijn;");

        if (!isset($data['ID']))
            throw new Exception("406;ID is verplicht;");

        $id = isINT($data['ID'],"ID", false, "Facturen");

        $factuur = array(
            'ID' => $id,
            'FACTUUR_NUMMER' => random_int(20000, 90000)
        );
        $this->UpdateObject($factuur);
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($factuur)
    {
        global $app_settings;

        $functie = "Facturen.UpdateObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($factuur, true)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($factuur == null)
            throw new Exception("406;Factuur data moet ingevuld zijn;");

        if (!array_key_exists('ID', $factuur))
            throw new Exception("406;ID moet ingevuld zijn;");

        $id = isINT($factuur['ID'], "ID");

        // Neem data over uit aanvraag
        $d = $this->RequestToRecord($factuur);

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
        $functie = "Facturen.VerwijderObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" :  $verificatie)));

        // schrijven mag alleen door beheerder / CIMT
        $l = MaakObject('Login');

        if (!$l->isBeheerder())
            throw new Exception("401;Geen rechten;");

        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        $id = isINT($id,"ID", false, "Facturen");
        $factuur = $this->GetObject($id);

        if (isset($factuur['FACTUUR_NUMMER']))
            throw new Exception("405;Factuur is reeds geupload;");
        parent::Elimineer($id);
    }

    /*
	Copieer data van request naar velden van het record
	*/
    function RequestToRecord($input)
    {
        $record = array();

        $field = 'JAAR';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false);

        $field = 'LID_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Leden");

        $field = 'LIDNR';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];

        $field = 'FACTUUR_NUMMER';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];

        $field = 'OMSCHRIJVING';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];

        $field = 'NAAM';
        if (array_key_exists($field, $input))
            $record[$field] = $input[$field];

        $field = 'GEFACTUREERD';
        if (array_key_exists($field, $input))
            $record[$field] = isNUM($input[$field], $field, false);

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

        if (isset($record['JAAR']))
            $retVal['JAAR']  = $record['JAAR'] * 1;

        if (isset($record['LID_ID']))
            $retVal['LID_ID']  = $record['LID_ID'] * 1;

        if (isset($record['LIDTYPE_ID']))
            $retVal['LIDTYPE_ID']  = $record['LIDTYPE_ID'] * 1;

        if (isset($record['LEEFTIJD']))
            $retVal['LEEFTIJD']  = $record['LEEFTIJD'] * 1;

        // vermengvuldigen met 1 converteer naar numeric
        if (isset($record['GEFACTUREERD']))
            $retVal['GEFACTUREERD'] = $record['GEFACTUREERD'] * 1;

        // vermengvuldigen met 1 converteer naar numeric
        if (isset($record['CONTRIBUTIE']))
            $retVal['CONTRIBUTIE'] = $record['CONTRIBUTIE'] * 1;

        return $retVal;
    }
}
