<?php

require_once __DIR__ . '/../ext/vendor/autoload.php';


class Transacties extends Helios
{
    private $mollie;

	function __construct() 
	{
        global $iDeal;

		parent::__construct();
		$this->dbTable = "oper_transacties";
        $this->dbView = "transacties_view";
		$this->Naam = "Transacties";

        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey($iDeal['mollieKey']);
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
				`VLIEGDAG` date NOT NULL,
				`LID_ID` mediumint UNSIGNED NOT NULL,
				`INGEVOERD_ID` mediumint UNSIGNED NOT NULL,
				`TYPE_ID` mediumint UNSIGNED NOT NULL,
                `DDWV` tinyint UNSIGNED NOT NULL DEFAULT '0',
                `BEDRAG` numeric NULL,
                `EENHEDEN` numeric NULL,
                `SALDO_VOOR` numeric NULL,
                `SALDO_NA` numeric NULL,
                `REFERENTIE` varchar(50) DEFAULT NULL,
				`EXT_REF` varchar(50) DEFAULT NULL,
				`OMSCHRIJVING` varchar(150) NOT NULL,
                `BETAALD` tinyint UNSIGNED NOT NULL DEFAULT '0',
                `BETAAL_URL` varchar(250) DEFAULT NULL,
			
				`VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT '0',
				`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				
				CONSTRAINT ID_PK PRIMARY KEY (ID),
					INDEX (`LID_ID`),
					
				FOREIGN KEY (LID_ID) REFERENCES ref_leden(ID),
				FOREIGN KEY (INGEVOERD_ID) REFERENCES ref_leden(ID),
				FOREIGN KEY (TYPE_ID) REFERENCES ref_types(ID)
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
            t.*,
			`l`.`NAAM` AS `NAAM`,
			`i`.`NAAM` AS `INGEVOERD`,
			`types`.`OMSCHRIJVING` AS `TYPE`
		FROM
			`%s` `t`
			LEFT JOIN `ref_types` `types` ON (`t`.`TYPE_ID` = `types`.`ID`)
			LEFT JOIN `ref_leden` `l` ON (`t`.`LID_ID` = `l`.`ID`)
			LEFT JOIN `ref_leden` `i` ON (`t`.`INGEVOERD_ID` = `i`.`ID`)
		WHERE
			`t`.`VERWIJDERD` = %d
		ORDER BY `t`.`LAATSTE_AANPASSING` DESC;";	

		parent::DbUitvoeren("DROP VIEW IF EXISTS transacties_view");							
		parent::DbUitvoeren(sprintf($query, "transacties_view", $this->dbTable, 0));

		parent::DbUitvoeren("DROP VIEW IF EXISTS verwijderd_transacties_view");
		parent::DbUitvoeren(sprintf($query, "verwijderd_transacties_view", $this->dbTable, 1));
	}

    /*
    Haal een enkel record op uit de database
    */
    function GetObject($ID)
    {
        $functie = "Transacties.GetObject";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $ID));

        if ($ID == null)
            throw new Exception("406;Geen IDin aanroep;");

        $conditie = array();
        if ($ID !== null)
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

		$functie = "Transacties.GetObjects";
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

		$l = MaakObject('Login');
		if (!$l->isBeheerder() && !$l->isBeheerderDDWV())
		{
			Debug(__FILE__, __LINE__, sprintf("%s: %s is geen beheerder, beperk query", $functie, $l->getUserFromSession()));

			$where .= sprintf(" AND LID_ID=%d", $l->getUserFromSession());
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
                case "EXT_REF" :
                {
                    $id = isINT($value, "EXT_REF");
                    $where .= " AND EXT_REF=?";
                    array_push($query_params, $id);

                    Debug(__FILE__, __LINE__, sprintf("%s: EXT_REF='%s'", $functie, $id));
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
				case "LID_ID" : 
					{
						$lidID = isINT($value, "LID_ID");
						$where .= " AND LID_ID=?";
						array_push($query_params, $lidID);	
						
						Debug(__FILE__, __LINE__, sprintf("%s: LID_ID='%s'", $functie, $lidID));
						break;                      
					}
                case "VLIEGDAG" :
                {
                    $datum = isDATE($value, "VLIEGDAG");

                    $where .= " AND DATE(VLIEGDAG) = ? ";
                    array_push($query_params, $datum);

                    Debug(__FILE__, __LINE__, sprintf("%s: VLIEGDAG='%s'", $functie, $datum));
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
					}																																				
			}
		}
					
		$query = "
			SELECT 
				%s
			FROM
				`####transacties_view` " . $where . $orderby;
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
	function AddObject($Transactie)
	{
		$functie = "Transacties.AddObject";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($Transactie, true)));

		if (!array_key_exists('LID_ID', $Transactie))
			throw new Exception("406;LID_ID moet ingevuld zijn;");

        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isBeheerderDDWV() && ($Transactie['LID_ID'] != $l->getUserFromSession()))
        {
            throw new Exception("401;Geen rechten;");
        }

		// Neem data over uit aanvraag
		$record = $this->RequestToRecord($Transactie);

		$lObj = MaakObject('Leden');
		$LidData = $lObj->getObject($record['LID_ID']);

		$record['INGEVOERD_ID'] = $l->getUserFromSession();   
		$record['SALDO_VOOR'] = $LidData['TEGOED'] * 1;   
		$record['SALDO_NA'] = $LidData['TEGOED'] * 1 + $record['EENHEDEN'];      

		$id = parent::DbToevoegen($record);

		// Opslaan tegoed bij het lid
		$ld = array();
		$ld['ID'] = $record['LID_ID'];
		$ld['TEGOED'] = $record['SALDO_NA'];
		$lObj->UpdateObject($ld);

		Debug(__FILE__, __LINE__, sprintf("Transactie toegevoegd id=%d, %s", $id, $LidData['NAAM']));
        return $this->GetObject($id);
	}    

	// haal de banken op die iDeal ondersteunen
	function GetBanken()
	{
        Debug(__FILE__, __LINE__, "GetBanken()");
        $banken = array();

        $method = $this->mollie->methods->get(\Mollie\Api\Types\PaymentMethod::IDEAL, ["include" => "issuers"]);
        foreach ($method->issuers() as $issuer)
        {
            $banken[] = array('ID' => $issuer->id, 'NAAM' => $issuer->name);
        }
        return $banken;
	}

    // start de ideal transactie
    function StartIDealTransactie($TransactieData)
    {
        global $iDeal;

        $functie = "Transacties.StartIDealTransactie";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($TransactieData, true)));

        if ($TransactieData == null)
            throw new Exception("406;Transactie data moet ingevuld zijn;");

        if (!array_key_exists('BESTELLING_ID', $TransactieData))
            throw new Exception("406;BESTELLING_ID moet ingevuld zijn;");

        if (!array_key_exists('BANK_ID', $TransactieData))
            throw new Exception("406;BANK_ID moet ingevuld zijn;");

        if (!array_key_exists('LID_ID', $TransactieData))
            throw new Exception("406;LID_ID moet ingevuld zijn;");

        $bestellingID = isINT($TransactieData['BESTELLING_ID'], "BESTELLING_ID");
        $lidID = isINT($TransactieData['LID_ID'], "LID_ID", false, 'Leden');

        $l = MaakObject('Login');
        $lObj = MaakObject('Leden');
        $tObj = MaakObject('Types');
        $LidData = $lObj->getObject($lidID);
        $bestelInfo = $tObj->getObject($bestellingID);

        $record = array();

        $record['BETAALD'] = 0;
        $record['INGEVOERD_ID'] = $l->getUserFromSession();
        $record['LID_ID'] = $lidID;
        $record['EENHEDEN'] = $bestelInfo['EENHEDEN'];
        $record['BEDRAG'] = 1*$bestelInfo['BEDRAG'];
        $record['TYPE_ID'] = $bestellingID;
        $record['OMSCHRIJVING'] = $bestelInfo['OMSCHRIJVING'];

        $id = parent::DbToevoegen($record);

        HeliosLog(__FILE__, __LINE__, sprintf("%s: ---------- Start transactie ----------", $functie));
        HeliosLog(__FILE__, __LINE__, sprintf("%s: %s %s %s",
                $functie, $LidData['NAAM'],
                print_r($bestelInfo, true),
                print_r($TransactieData, true)));

        $payment = $this->mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => sprintf("%0.2f", $bestelInfo['BEDRAG'])
            ],
            "method" => \Mollie\Api\Types\PaymentMethod::IDEAL,
            "description" => $bestelInfo['OMSCHRIJVING'],
            "redirectUrl" => $iDeal['returnUrl'],
            "webhookUrl" => $iDeal['reportUrl'],
            "metadata" => [
                "order_id" => sprintf("%d-%d-%d",$id, $lidID, $record['BEDRAG'] * 100),
            ],
            "issuer" => $TransactieData['BANK_ID'],
        ]);

        $redirectTo = $payment->getCheckoutUrl();
        Debug(__FILE__, __LINE__, sprintf("%s url=%s", $functie, $redirectTo));
        return $redirectTo;
    }

    /*
     Check of betaling ook echt gedaan is
     */
    function ValideerIDealTransactie($PaymentRef)
    {
        global $iDeal;

        $payment = $this->mollie->payments->get($_POST["id"]);
        $orderref = $payment->metadata->order_id;

        list($orderId, $lidId, $bedrag) = explode("-", $orderref);

        $functie = "Transacties.ValideerIDealTransactie";
        HeliosLog(__FILE__, __LINE__, sprintf("%s(%s) orderref=%s", $functie, print_r($PaymentRef, true), $orderref));
        Debug(__FILE__, __LINE__, sprintf("%s(%s) orderref=%s", $functie, print_r($PaymentRef, true), $orderref));

        $dbData = $this->GetObject($orderId);

        $heeftBetaald = false;
        if (($dbData['LID_ID'] != $lidId) && ($dbData['BEDRAG']*100 != $bedrag))
        {
            HeliosLog(__FILE__, __LINE__, sprintf("%s:  orderref onjuist %s %s %s", $functie, $dbData['LID_ID'], $dbData['BEDRAG']*100));
        }
        else if ($payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks()) {
            $heeftBetaald = true;
        }

        $l = MaakObject('Login');
        $l->setSessionUser($dbData['LID_ID']);    // deze functie wordt zonder inloggen aangeroepen, dus vertellen wie we zijn

        if ($heeftBetaald)
        {
            $lObj = MaakObject('Leden');
            $LidData = $lObj->getObject($dbData['LID_ID']);

            // update van transactie record wat aangemaakt is, bij het starten van iDeal
            $record = array();
            $record['BETAALD'] = 1;
            $record['SALDO_VOOR'] = 1*$LidData['TEGOED'];
            $record['SALDO_NA'] = 1*$LidData['TEGOED'] + 1*$dbData['EENHEDEN'];
            $record['EXT_REF'] =  $PaymentRef['id'];
            parent::DbAanpassen($dbData['ID'], $record);

            HeliosLog(__FILE__, __LINE__, sprintf("%s: Transactie : %s", $functie, print_r($dbData, true)));

            // update saldo in profiel
            $ld['ID'] = $dbData['LID_ID'];
            $ld['TEGOED'] = $record['SALDO_NA'];
            $lObj->UpdateObject($ld);

            HeliosLog(__FILE__, __LINE__, sprintf("%s: ---------- Afgerond ----------", $functie));
        }
        else
        {
            HeliosLog(__FILE__, __LINE__, sprintf("%s: Payment result: %s", $functie, $payment->status));
            HeliosLog(__FILE__, __LINE__, sprintf("%s: ---------- ERROR transactie ----------", $functie));

            $record = array();
            $record['REFERENTIE'] = print_r($payment->status, true);
            $record['EXT_REF'] =  $PaymentRef['id'];
            parent::DbAanpassen($dbData['ID'], $record);
            parent::MarkeerAlsVerwijderd($dbData['ID']);
        }
    }

    /*
	Copieer data van request naar velden van het record 
	*/
	function RequestToRecord($input)
	{
		$record = array();

        $field = 'VLIEGDAG';
        if (array_key_exists($field, $input))
            $record[$field] = isDATE($input[$field], $field);

		$field = 'LID_ID';
		if (array_key_exists($field, $input))
			$record[$field] = isINT($input[$field], $field, false, "Leden");

        $field = 'TYPE_ID';
        if (array_key_exists($field, $input))
            $record[$field] = isINT($input[$field], $field, false, "Types");

		$field = 'EENHEDEN';
		if (array_key_exists($field, $input))
			$record[$field] = isNUM($input[$field], $field, true);

        $field = 'DDWV';
        if (array_key_exists($field, $input))
            $record[$field] = isBOOL($input[$field], $field);

		$field = 'BEDRAG';
		if (array_key_exists($field, $input))
			$record[$field] = isNUM($input[$field], $field, true);

		if (array_key_exists('OMSCHRIJVING', $input))
			$record['OMSCHRIJVING'] = $input['OMSCHRIJVING']; 

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

		if (isset($record['LID_ID']))
			$retVal['LID_ID']  = $record['LID_ID'] * 1;

        if (isset($record['TYPE_ID']))
            $retVal['TYPE_ID']  = $record['TYPE_ID'] * 1;
		
		if (isset($record['INGEVOERD_ID']))
			$retVal['INGEVOERD_ID']  = $record['INGEVOERD_ID'] * 1;	

		if (isset($record['INGEVOERD_ID']))
			$retVal['INGEVOERD_ID']  = $record['INGEVOERD_ID'] * 1;	

		if (isset($record['BEDRAG']))
			$retVal['BEDRAG']  = $record['BEDRAG'] * 1;		
			
		if (isset($record['SALDO_VOOR']))
			$retVal['SALDO_VOOR']  = $record['SALDO_VOOR'] * 1;					

		if (isset($record['SALDO_NA']))
			$retVal['SALDO_NA']  = $record['SALDO_NA'] * 1;	

		// booleans					
		if (isset($record['VERWIJDERD']))
			$retVal['VERWIJDERD']  = $record['VERWIJDERD'] == "1" ? true : false;

		if (isset($record['DDWV']))
			$retVal['DDWV']  = $record['DDWV'] == "1" ? true : false;	
			
		if (isset($record['BETAALD']))
			$retVal['BETAALD']  = $record['BETAALD'] == "1" ? true : false;				
        
		return $retVal;
	}
}
