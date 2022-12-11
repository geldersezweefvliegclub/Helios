<?php

class DDWV
{

    function GetConfiguratie()
    {
        global $ddwv;
        return $ddwv;
    }


    function dagIsDDWV($datum)
    {
        global $ddwv;

        $functie = "DDWV.dagIsDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $datum));

        if ($ddwv->DDWV == false) {
            Debug(__FILE__, __LINE__, sprintf("%s: GEEN DDWV", $functie));
            return false;
        }

        $d = isDATE($datum, "DATUM");

        // is de dag binnen het DDWV seizoen
        $dateparts = explode('-', $d);
        $dateValue = $dateparts[1] * 100 + $dateparts[2] * 1;

        $datepartsS = explode('-', $ddwv->START);
        $dateValueS = $datepartsS[0] * 100 + $datepartsS[1] * 1;

        $datepartsE = explode('-', $ddwv->EIND);
        $dateValueE = $datepartsE[0] * 100 + $datepartsE[1] * 1;

        Debug(__FILE__, __LINE__, sprintf("%s: DDWV periode %d %d %d", $functie, $dateValue, $dateValueS, $dateValueE));
        if (($dateValue < $dateValueS) || ($dateValue > $dateValueE)) {
            return false;
        }

        // is het een doordeweekse dag
        $weekday = DateTime::createFromFormat('Y-m-d', $d)->format('N');

        Debug(__FILE__, __LINE__, sprintf("%s: weekdag %d %s", $functie, $weekday, ($weekday <= 5) ? "true" : "false"));

        return ($weekday <= 5) ? true : false;
    }

    function AanmeldenLidAfboekenDDWV($aanmelding)
    {
        global $ddwv;

        $functie = "DDWV.AanmeldenLidAfboekenDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($aanmelding, true)));
        Debug(__FILE__, __LINE__, sprintf("%s: DDWV=%s", $functie, print_r($ddwv, true)));

        if (!isset($ddwv)) {
            Debug(__FILE__, __LINE__, sprintf("%s: DDWV variable bestaat niet", $functie));
            return -1;
        }
        if ($ddwv->DDWV == false) {
            Debug(__FILE__, __LINE__, sprintf("%s: DDWV staat UIT", $functie));
            return -1;
        }

        // Check vliegveld
        if ($ddwv->VELD_ID !== $aanmelding['VELD_ID']) {
            Debug(__FILE__, __LINE__, sprintf("%s: Geen DDWV veld", $functie));
            return -1;
        }

        // Bekijk rooster of we DDWV kun uitsluiten
        $di = MaakObject('Rooster');
        try {
            $diObj = $di->GetObject(null, $aanmelding['DATUM']);
        } catch (Exception $exception) //  als er geen rooster is, dan komt er een exceptie
        {
            Debug(__FILE__, __LINE__, sprintf("%s: Rooster bestaat niet", $functie));
            return -1;
        }
        $rl = MaakObject('Leden');

        // op clubdag hoeven de leden niet te betalen, maar DDWV'ers wel
        if ($diObj['CLUB_BEDRIJF'] == true && $rl->isClubVlieger($aanmelding['LID_ID'])) {
            Debug(__FILE__, __LINE__, sprintf("%s: Club bedrijf en club vlieger, dus geen DDWV", $functie));
            return -1;
        }

        $dagen = (strtotime($aanmelding['DATUM']) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
        if ($dagen < 0) {    // aanmelding in het verleden
            $dagen = 0;
        }

        // beslismoment is om 21:00, na 21:00 betaald men het hoogste tarief
        if ($dagen == 1) {
            $nu = explode(":", date("H:i"));
            $hhmm = 100 * $nu[0] + 1 * $nu[1];

            if ($hhmm > 2100) {
                $dagen = 0;
            }
        }
        Debug(__FILE__, __LINE__, sprintf("%s: %s-%s = %d dagen", $functie, $aanmelding['DATUM'], date("Y-m-d"), $dagen));

        $typesObj = MaakObject('Types');
        $tID = (array_key_exists(strval($dagen), $ddwv->TARIEVEN)) ? $ddwv->TARIEVEN[strval($dagen)] : $ddwv->TARIEVEN['default'];
        $tariefInfo = $typesObj->GetObject($tID);

        $dateparts = explode('-', $aanmelding['DATUM']);

        $transactie = array();
        $transactie['DDWV'] = true;
        $transactie['LID_ID'] = $aanmelding['LID_ID'];
        $transactie['TYPE_ID'] = $tID;
        $transactie['EENHEDEN'] = $tariefInfo['EENHEDEN'];
        $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

        $tObj = MaakObject('Transacties');
        $id = $tObj->AddObject($transactie);

        return $id;
    }

    function AfmeldenLidBijboekenDDWV($afmelding)
    {
        global $ddwv;

        $functie = "DDWV.AfmeldenLidBijboekenDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($afmelding, true)));

        if (!isset($afmelding['TRANSACTIE_ID'])) {
            return -1;
        }

        $dagen = (strtotime($afmelding['DATUM']) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
        if ($dagen < 0) {    // afmelden doen we niet voor het verleden
            return -1;
        }

        if ($dagen == 1) {                                          // beslismoment is om 21:00, na 21:00 krijg je niet alle strippen terug
            $nu = explode(":", date("H:i"));
            $hhmm = 100 * $nu[0] + 1 * $nu[1];

            if ($hhmm > 2100) {
                $dagen = 0;
            }
        }
        Debug(__FILE__, __LINE__, sprintf("%s: %s-%s = %d dagen", $functie, $afmelding['DATUM'], date("Y-m-d"), $dagen));

        $typesObj = MaakObject('Types');
        $tID = ($dagen === 0) ? $ddwv->STRIPPEN_RETOUR_OP_VLIEGDAG : $ddwv->STRIPPEN_RETOUR;
        $tariefInfo = $typesObj->GetObject($tID);

        $tObj = MaakObject('Transacties');
        $aanmeldTransactie = $tObj->GetObject($afmelding['TRANSACTIE_ID']);

        $retour_strippen = 0;
        if ($tariefInfo['EENHEDEN'] == 0) { // geen vast tarief, kijken wat er betaald is
            $retour_strippen = -1 * $aanmeldTransactie['EENHEDEN'];
        }
        else {
            $retour_strippen = $tariefInfo['EENHEDEN'];
        }
        $dateparts = explode('-', $afmelding['DATUM']);

        $transactie = array();
        $transactie['DDWV'] = true;
        $transactie['LID_ID'] = $afmelding['LID_ID'];
        $transactie['TYPE_ID'] = $tID;
        $transactie['EENHEDEN'] = $retour_strippen;
        $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

        $id = $tObj->AddObject($transactie);

        return $id;
    }
}
