<?php

class Sync extends Helios
{
    function __construct()
    {
        global $app_settings;

        parent::__construct();
        $this->dbTable = "sys_sync";
        $this->dbView = "sync_view";
        $this->Naam = "Sync";

        $this->encryptieMethode = "AES-256-CBC";
        $this->encryptieSleutel = $app_settings['EncryptieSleutel'];
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
				`LID_ID` mediumint  UNSIGNED NULL,
				`STARTLIJST_ID` mediumint  UNSIGNED NULL,
				`VLIEGTUIG_ID` mediumint  UNSIGNED NULL,
				`DATA` varchar(8192) NULL,
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

				CONSTRAINT ID_PK PRIMARY KEY (ID)
				)", $this->dbTable);
        parent::DbUitvoeren($query);
    }

    /*
    Maak database views, als view al bestaat wordt deze overschreven
    */
    function CreateViews()
    {
        // geen views nodig
    }

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Sync.GetObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));

        if ($ID == null)
            throw new Exception("406;Geen ID in aanroep;");

        $conditie = array();
        $conditie['ID'] = isINT($ID, "ID");

        $obj = parent::GetSingleObject($conditie);
        Debug(__FILE__, __LINE__, print_r($obj, true));

        if ($obj == null)
            throw new Exception(sprintf("404;Record niet gevonden (%s, '%s');", $this->Naam, json_encode($conditie)));

        return $obj;
    }

    /*
    Haal een dataset op met records als een array uit de database.
    */
    function GetObjects($params)
    {
        global $app_settings;

        $functie = "Sync.GetObjects";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));

        $query = sprintf("SELECT * FROM `%s`", $this->dbTable);

        parent::DbOpvraag($query);
        $retVal = parent::DbData();
        for ($i = 0; $i < count($retVal); $i++) {
            $retVal[$i] = $this->RecordToOutput($retVal[$i]);
        }
        return $retVal;
    }

    /*
    Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
    Het veld VERWIJDERD wordt op "1" gezet.
    */
    function VerwijderObject($id = null, $verificatie = true)
    {
        $functie = "Sync.VerwijderObject";
        Debug(__FILE__, __LINE__, sprintf("%s('%s', %s)", $functie, $id, (($verificatie === false) ? "False" : $verificatie)));

        $l = MaakObject('Login');
        if ($l->isBeheerder() == false)
            throw new Exception("401;Geen beheerrechten;");

        if ($id == null)
            throw new Exception("406;Geen ID in aanroep;");

        isCSV($id, "ID");
        parent::Elimineer($id);
    }

    /*
    Herstel van een verwijderd record
    */
    function HerstelObject($id)
    {
        // we gooien record echt weg, herstel is niet mogelijk
    }

    /*
    Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function AddObject($Data)
    {
        global $app_settings;

        $functie = "Sync.AddObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($Data, true)));

        if ($app_settings['Sync'] !== true)
            Debug(__FILE__, __LINE__, "Synchronisatie uitgeschakeld;");

        if ($Data == null)
            throw new Exception("406;Data moet ingevuld zijn;");

        if (array_key_exists('ID', $Data)) {
            $id = isINT($Data['ID'], "ID");

            // ID is opgegeven, maar bestaat record?
            try    // Als record niet bestaat, krijgen we een exception
            {
                $this->GetObject($id);
            } catch (Exception $e) {
            }

            if (parent::NumRows() > 0)
                throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));
        }

        // Neem data over uit aanvraag
        $s = $this->RequestToRecord($Data);

        $id = parent::DbToevoegen($s);
        Debug(__FILE__, __LINE__, sprintf("Sync toegevoegd id=%d", $id));

        return $this->GetObject($id);
    }

    /*
    Update van een bestaand record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
    */
    function UpdateObject($Data)
    {
        // als object eenmaal is aangemaakt, kan het niet meer worden gewijzigd
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

        $field = 'VLIEGTUIG_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, true);

        $field = 'LID_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field);

        $field = 'STARTLIJST_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field);

        if (array_key_exists('DATA', $input)) {
            $encrypted = openssl_encrypt(json_encode($input['DATA']), $this->encryptieMethode, $this->encryptieSleutel);

            Debug(__FILE__, __LINE__, sprintf("Encryptie %s", $encrypted));
            if ($encrypted === false) {
                HeliosError(__FILE__, __LINE__, sprintf("Encryptie mislukt %s", openssl_error_string()));
            } else {
                $record['DATA'] = $encrypted;
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
            $retVal['ID'] = $record['ID'] * 1;

        if (isset($record['STARTLIJST_ID']))
            $retVal['STARTLIJST_ID'] = $record['STARTLIJST_ID'] * 1;

        if (isset($record['LID_ID']))
            $retVal['LID_ID'] = $record['LID_ID'] * 1;

        if (isset($record['VLIEGTUIG_ID']))
            $retVal['VLIEGTUIG_ID'] = $record['VLIEGTUIG_ID'] * 1;

        if (isset($record['DATA'])) {
            $decrypted = openssl_decrypt($record['DATA'], $this->encryptieMethode, $this->encryptieSleutel);

            if ($decrypted === false) {
                HeliosError(__FILE__, __LINE__, sprintf("Encryptie mislukt %s", openssl_error_string()));
            } else {
                Debug(__FILE__, __LINE__, sprintf("Decryptie %s", $decrypted));
                $retVal['DATA'] = json_decode($decrypted, true);
            }
        }

        return $retVal;
    }
}
