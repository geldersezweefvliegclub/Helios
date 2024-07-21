<?php


class eboekhouden
{
    private static $sessionID = null;
    private static $relaties = null;

    public static function getSessionID()
    {
        global $eBoekhouden_settings;

        Debug(__FILE__, __LINE__, "getSessionID()");
        $client = new SoapClient($eBoekhouden_settings['SoapBaseUrl']);

        $params = array(
            "Username" => $eBoekhouden_settings['Username'],
            "SecurityCode1" => $eBoekhouden_settings['SecurityCode1'],
            "SecurityCode2" => $eBoekhouden_settings['SecurityCode2']
        );
        $response = $client->__soapCall("OpenSession", [$params]);
        self::checkforerror($response, "OpenSessionResult");
        self::$sessionID = $response->OpenSessionResult->SessionID;

        Debug(__FILE__, __LINE__, sprintf("SessionID = %s", self::$sessionID));
    }

    public static function closeSession()
    {
        global $eBoekhouden_settings;

        Debug(__FILE__, __LINE__, "closeSession()");

        try {
            $client = new SoapClient($eBoekhouden_settings['SoapBaseUrl']);

            $params = array(
                "SessionID" => self::$sessionID
            );
            $response = $client->__soapCall("CloseSession", array($params));
        }
        catch (SoapFault $soapFault) {
            Debug(__FILE__, __LINE__, sprintf("Error (%s)", $soapFault));
        }
    }

    private static function checkforerror($rawresponse, $sub)
    {
        $errorMsg = $rawresponse->$sub->ErrorMsg;
        $LastErrorCode = isset($errorMsg->LastErrorCode) ? $errorMsg->LastErrorCode : '';
        $LastErrorDescription = isset($errorMsg->LastErrorDescription) ? $errorMsg->LastErrorDescription : '';

        if ($LastErrorCode <> '') {
            Debug(__FILE__, __LINE__, sprintf("Error %s:%s", $LastErrorCode, $LastErrorDescription));
            throw new Exception(sprintf("Error %s:%s", $LastErrorCode, $LastErrorDescription));
        }
    }

    public static function opvragenRelaties($code = "")
    {
        global $eBoekhouden_settings;

        Debug(__FILE__, __LINE__, "opvragenRelaties()");
        $client = new SoapClient($eBoekhouden_settings['SoapBaseUrl']);

        $params = array(
            "SecurityCode2" => $eBoekhouden_settings['SecurityCode2'],
            "SessionID" => self::$sessionID,
            "cFilter" => array(
                "ID" => 0,
                "Code" => $code,        // debiteurnummer
                "Trefwoord" => ""
            )

        );
        $response = $client->__soapCall("GetRelaties", [$params]);
        self::checkforerror($response, "GetRelatiesResult");

        // omzetten naar named array
        self::$relaties = array();
        foreach ($response->GetRelatiesResult->Relaties->cRelatie as $relatie)
        {
            if ($relatie->Def1 !== "")
                self::$relaties[$relatie->Def1] = $relatie;
            else
                self::$relaties[$relatie->KvkNummer] = $relatie;
        }
    }

    private static function AddRelatie($relatie)
    {
        global $eBoekhouden_settings;

        Debug(__FILE__, __LINE__, sprintf("AddRelatie(%s)", print_r($relatie, true)));
        $client = new SoapClient($eBoekhouden_settings['SoapBaseUrl']);

        $params = array(
            "SecurityCode2" => $eBoekhouden_settings['SecurityCode2'],
            "SessionID" => self::$sessionID,
            "oRel" => $relatie
        );
        $response = $client->__soapCall("AddRelatie", [$params]);
        self::checkforerror($response, "AddRelatieResult");
    }

    private static function UpdateRelatie($relatie)
    {
        global $eBoekhouden_settings;

        Debug(__FILE__, __LINE__, sprintf("UpdateRelatie(%s)", print_r($relatie, true)));
        $client = new SoapClient($eBoekhouden_settings['SoapBaseUrl']);

        $params = array(
            "SecurityCode2" => $eBoekhouden_settings['SecurityCode2'],
            "SessionID" => self::$sessionID,
            "oRel" => $relatie
        );
        $response = $client->__soapCall("UpdateRelatie", [$params]);
        self::checkforerror($response, "UpdateRelatieResult");
    }

    public static function verwijderLid($lid)
    {
        $lid['NAAM'] = "ZZ " . $lid['NAAM'] . " (verwijderd)";
        self::updateLid($lid);
    }

    public static function updateLid($lid)
    {
        global $eBoekhouden_settings;

        if (!isset($lid['LIDNR']))
            return;

        if (self::$sessionID == null)
        {
            self::getSessionID();
        }

        if (self::$relaties == null)
        {
            self::opvragenRelaties();
        }

        try {
            if (array_key_exists($lid['ID'], self::$relaties)) {

                $relatie = self::$relaties[$lid['ID']];
                $updateNodig = false;

                if ($relatie->Code != $lid['LIDNR']) {
                    $relatie->Code = $lid['LIDNR'];
                    $updateNodig = true;
                }

                if ($relatie->Bedrijf != $lid['NAAM']) {
                    $relatie->Bedrijf = $lid['NAAM'];
                    $updateNodig = true;
                }

                if ($relatie->Def2 != $lid['VOORNAAM']) {
                    $relatie->Def2 = $lid['VOORNAAM'];
                    $updateNodig = true;
                }

                if ($relatie->Adres != $lid['ADRES']) {
                    $relatie->Adres = $lid['ADRES'];
                    $updateNodig = true;
                }

                if ($relatie->Postcode != $lid['POSTCODE']) {
                    $relatie->Postcode = $lid['POSTCODE'];
                    $updateNodig = true;
                }

                if ($relatie->Plaats != $lid['WOONPLAATS']) {
                    $relatie->Plaats = $lid['WOONPLAATS'];
                    $updateNodig = true;
                }

                if ($relatie->Telefoon != $lid['TELEFOON']) {
                    $relatie->Telefoon = $lid['TELEFOON'];
                    $updateNodig = true;
                }

                if ($relatie->GSM != $lid['MOBIEL']) {
                    $relatie->GSM = $lid['MOBIEL'];
                    $updateNodig = true;
                }

                if ($relatie->Email != $lid['EMAIL']) {
                    $relatie->Email = $lid['EMAIL'];
                    $updateNodig = true;
                }

                if ($relatie->Def1 != $lid['ID']) {
                    $relatie->Def1 = $lid['ID'];
                    $updateNodig = true;
                }

                if ($updateNodig) {
                    self::UpdateRelatie($relatie);
                }

            } else {
                $relatie = new stdClass();
                $relatie->ID = 0;
                $relatie->AddDatum = date('Y-m-d');
                $relatie->Code = $lid['LIDNR'];
                $relatie->Bedrijf = $lid['NAAM'];
                $relatie->Def2 = $lid['VOORNAAM'];
                $relatie->Adres = $lid['ADRES'];
                $relatie->Postcode = $lid['POSTCODE'];
                $relatie->Plaats = $lid['WOONPLAATS'];
                $relatie->Telefoon = $lid['TELEFOON'];
                $relatie->GSM = $lid['MOBIEL'];
                $relatie->Email = $lid['EMAIL'];
                $relatie->KvkNummer = $lid['ID'];
                $relatie->Def1 = $lid['ID'];
                $relatie->Gb_ID = 0;
                $relatie->GeenEmail = 0;
                $relatie->NieuwsbriefgroepenCount = 0;
                $relatie->BP = "P";                 // indicatie dat het een particulier is
                $relatie->LA = "1";                 // indicatie dat het een lid is en geen relatie

                self::AddRelatie($relatie);
            }
        }
        catch (Exception $e)
        {
            Debug(__FILE__, __LINE__, sprintf("Error (%s)", $e->getMessage()));
            // email
            $mail = emailInit();

            $mail->Subject = 'Fout sync e-boekhouden ' . $lid['NAAM'];
            $mail->isHTML(true);                                  		//Set email format to HTML
            $mail->Body    = sprintf("Fout sync e-boekhouden: %s", $e->getMessage());

            $mail->addAddress($eBoekhouden_settings['ErrorEmail']['TO'],$eBoekhouden_settings['ErrorEmail']['NAAM']);
            $mail->SetFrom($eBoekhouden_settings['ErrorEmail']['from'], $eBoekhouden_settings['ErrorEmail']['name']);

            if(!$mail->Send()) {
                print_r($mail);
            }
        }
    }
}