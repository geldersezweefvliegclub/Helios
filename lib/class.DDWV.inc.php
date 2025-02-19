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

    function AanmeldenLidAfboekenDDWV($aanmelding, $startData)
    {
        global $ddwv;

        $functie = "DDWV.AanmeldenLidAfboekenDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, print_r($aanmelding, true), print_r($startData, true)));
        Debug(__FILE__, __LINE__, sprintf("%s: DDWV=%s", $functie, print_r($ddwv, true)));

        return -1;      // vanaf 2025 geen strippen boeken

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

        if ($diObj['DDWV'] == false) {
            Debug(__FILE__, __LINE__, sprintf("%s: Geen DDWV dag", $functie));
            return -1;
        }

        $rl = MaakObject('Leden');
        $lid = $rl->GetObject($aanmelding['LID_ID']);

        // op clubdag hoeven de leden niet te betalen, maar DDWV'ers wel
        if ($diObj['CLUB_BEDRIJF'] == true && $rl->isClubVlieger(null, $lid)) {
            Debug(__FILE__, __LINE__, sprintf("%s: Club bedrijf en club vlieger, dus geen DDWV", $functie));
            return -1;
        }

        if ($startData != null)
        {
            /*******  UITGEZET OP VERZOEK VAN DE DDWV BEHEERDER Mei 2024 *******/
            // Bij invoeren start, wordt er wel aangemeld, maar geen strippen afgeschreven
            return -1;

            // VANAF HIER STAAT DE CODE DUS UIT
            // Kijken of zelfstart abonnement van toepassing is
            if ($startData['STARTMETHODE_ID'] == 506)       // 506 = zelfstart
            {
                if (isset($startData['VLIEGER_ID']))
                {
                    $vlieger = $rl->GetObject($startData['VLIEGER_ID']);
                    if ($vlieger['ZELFSTART_ABONNEMENT'])
                    {
                        Debug(__FILE__, __LINE__, sprintf("%s: Vlieger zelfstart afgekocht", $functie));
                        return -1;
                    }
                }

                if (isset($startData['INZITTENDE_ID']))
                {
                    $inzittende = $rl->GetObject($startData['INZITTENDE_ID']);
                    if ($inzittende['ZELFSTART_ABONNEMENT'])
                    {
                        Debug(__FILE__, __LINE__, sprintf("%s: Inzittende zelfstart afgekocht", $functie));
                        return -1;
                    }
                }
            }

            // Als de vlieger of de inzittende betaald heeft, dan is het goed. Geen strippen afschrijven
            if (isset($startData['VLIEGER_ID'])) {
                try {
                    $aLeden = MaakObject('AanwezigLeden');
                    $aLeden->GetObject(null, $startData['VLIEGER_ID'], $startData['DATUM'], false);

                    Debug(__FILE__, __LINE__, sprintf("%s: Vlieger is al aangemeld", $functie));
                    return -1;
                }
                catch(Exception $exception)     // als vlieger niet aangemeld is, komt er een exception
                {}
            }

            if (isset($startData['INZITTENDE_ID'])) {
                $rv = MaakObject('Vliegtuigen');
                $rvObj = $rv->GetObject($startData['VLIEGTUIG_ID']);

                if ($rvObj['ZITPLAATSEN'] == 2)     // alleen bij tweezitters
                {
                    try {
                        $aLeden = MaakObject('AanwezigLeden');
                        $aLeden->GetObject(null, $startData['INZITTENDE_ID'], $startData['DATUM'], false);

                        Debug(__FILE__, __LINE__, sprintf("%s: Inzittende is al aangemeld", $functie));
                        return -1;
                    }
                    catch(Exception $exception)     // als inzittende niet aangemeld is, komt er een exception
                    {}
                }
            }
            // TOT HIER
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
        $transactie['VLIEGDAG'] = $aanmelding['DATUM'];
        $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

        $tObj = MaakObject('Transacties');
        $record= $tObj->AddObject($transactie);

        return $record['ID'];
    }

    function AfmeldenLidBijboekenDDWV($afmelding)
    {
        global $ddwv;

        $functie = "DDWV.AfmeldenLidBijboekenDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($afmelding, true)));

        return -1;      // vanaf 2025 geen strippen boeken

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
        $transactie['VLIEGDAG'] = $aanmeldTransactie['VLIEGDAG'];
        $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

        $id = $tObj->AddObject($transactie);

        return $id;
    }

    /*
     Toetsen of we een DDWV dag hebben, hash is nodig om te voorkomen dat het illegaal wordt aangeroepen wordt
    */
    function ToetsingDDWV($Datum, $Hash)
    {
        global $ddwv;

        $functie = "DDWV.ToetsingDDWV";
        Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $Datum, $Hash));

        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isBeheerderDDWV())
            throw new Exception("401;Geen rechten;");

        isDATE($Datum, "DATUM");

        $rObj = MaakObject('Rooster');
        $rooster = $rObj->GetObject(null, $Datum);

        if ($Hash != sha1(json_encode($rooster)))
            throw new Exception("405;Hash incorrect;");

        if ($rooster['DDWV'] == false)
            throw new Exception("405;Geen DDWV Dag;");

        $al = MaakObject('AanwezigLeden');
        $aanmeldingen = $al->GetObjects(array('BEGIN_DATUM' => $Datum, 'EIND_DATUM' => $Datum));

        $typeBedrijf = "annuleren";

        if ($aanmeldingen['totaal'] >= $rooster['MIN_SLEEPSTART'])
            $typeBedrijf = "slepen";
    
        if ($aanmeldingen['totaal'] >= $rooster['MIN_LIERSTART'])
            $typeBedrijf = "lieren";

        if ($rooster['CLUB_BEDRIJF'] == true)
            $typeBedrijf = "club";

        $tObj = MaakObject('Transacties');
        $dateparts = explode('-', $Datum);

        if ($typeBedrijf == "annuleren")
        {
            // betaalde strippen terug storten
            foreach ($aanmeldingen['dataset'] as  $aanmelding)
            {
                Debug(__FILE__, __LINE__, sprintf("%s: aanmelding %s", $functie, print_r($aanmelding, true)));

                if (isset($aanmelding['TRANSACTIE_ID'])) {  // Alleen terug betalen als er een link is met een betaal transactie
                    $transactie = $tObj->GetObject($aanmelding['TRANSACTIE_ID']);
                    $betaald = $transactie['EENHEDEN'];

                    $transactie = array();
                    $transactie['DDWV'] = true;
                    $transactie['LID_ID'] = $aanmelding['LID_ID'];
                    $transactie['TYPE_ID'] = $ddwv->ANNULEREN_VLIEGDAG;
                    $transactie['EENHEDEN'] = -1 * $betaald;
                    $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

                    $id = $tObj->AddObject($transactie);

                    // verwijderen van link tussen aanmelden en transactie. Voorkom hiermee dat twee keer terug geboekt kan worden
                    $a = array();
                    $a['ID'] = $aanmelding['ID'];
                    $a['TRANSACTIE_ID'] = null;
                    $al->UpdateObject($a);
                }
            }
            $r = array();
            $r['ID'] = $rooster['ID'];
            $r['DDWV'] = false;
            $rObj->UpdateObject($r);
        }
        else
        {
            // 501 = slepen
            // 550 = lieren
            $startmethode_id = ($typeBedrijf == "slepen") ? 501 : 550;     // club bedrijf is ook lieren

            $di = MaakObject('Daginfo');

            // als er geen daginfo record is, dan komt er een exceptie
            try {
                $diObj = $di->GetObject(null, $Datum);

                $diObj['STARTMETHODE_ID'] = $startmethode_id;
                $diObj['CLUB_BEDRIJF'] = $typeBedrijf == "club";
                $diObj['VELD_ID'] = 901;                            // 901 = Terlet
                $diObj['DDWV'] = true;
                $di->UpdateObject($diObj);

            } catch (Exception $e) {    // exception als dagInfo nog niet bestaat, dan toevoegen
                $diObj['STARTMETHODE_ID'] = $startmethode_id;
                $diObj['CLUB_BEDRIJF'] = $typeBedrijf == "club";
                $diObj['DATUM'] = $Datum;
                $diObj['VELD_ID'] = 901;                            // 901 = Terlet
                $diObj['DDWV'] = true;
                $di->AddObject($diObj);
            }
        }
        return $typeBedrijf;
    }

    function UitbetalenCrew($data)
    {
        global $ddwv;

        $functie = "DDWV.UitbetalenCrew";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($data, true)));

        $l = MaakObject('Login');

        if (!$l->isBeheerder() && !$l->isBeheerderDDWV())
            throw new Exception("401;Geen rechten;");

        if ($data == null)
            throw new Exception("406;Uitbetaal data moet ingevuld zijn;");

        if (!array_key_exists('DATUM', $data))
            throw new Exception("406;DATUM is verplicht;");

        if (!array_key_exists('DIENSTEN', $data))
            throw new Exception("406;DIENSTEN is verplicht;");

        isDATE($data['DATUM'], "DATUM");

        $rObj = MaakObject('Rooster');
        $rooster = $rObj->GetObject(null, $data['DATUM']);

        if ($rooster['DDWV'] == false)
            throw new Exception("406;Geen DDWV dag;");

        if ($rooster['CLUB_BEDRIJF'] == true)
            throw new Exception("406;Club bedrijf;");

        $typeObj = MaakObject('Types');
        $tarief = $typeObj->GetObject($ddwv->CREW_VERGOEDING);

        $tObj = MaakObject('Transacties');
        $dObj = MaakObject('Diensten');
        $diensten = $dObj->GetObjects(array('BEGIN_DATUM' => $data['DATUM'], 'EIND_DATUM' => $data['DATUM'], 'VELDEN' => "DV.ID,DV.DATUM,LID_ID,TYPE_DIENST_ID,ROOSTER_ID"));

        $dateparts = explode('-', $data['DATUM']);
        $dienstenIDs = explode(',', $data['DIENSTEN']);

        foreach ($diensten['dataset'] as $dienst)
        {
            Debug(__FILE__, __LINE__, sprintf("%s: dienst %s", $functie, print_r($dienst, true)));

            if (in_array($dienst['ID'], $dienstenIDs)) {
                $transactie = array();
                $transactie['DDWV'] = true;
                $transactie['LID_ID'] = $dienst['LID_ID'];
                $transactie['TYPE_ID'] = $tarief['ID'];
                $transactie['EENHEDEN'] = $tarief['EENHEDEN'];
                $transactie['OMSCHRIJVING'] = sprintf(", vliegdag %02d-%02d-%d", $dateparts[2], $dateparts[1], $dateparts[0]);

                $id = $tObj->AddObject($transactie);


                $dienst['UITBETAALD'] = true;
                $dObj->UpdateObject($dienst);

                Debug(__FILE__, __LINE__, sprintf("%s: uitbetaald id=%d %s", $functie, $id, print_r($transactie, true)));
            }
        }
    }

    function MaakTransacties($datum)
    {
        global $ddwv;

        $functie = "Startlijst.MaakTransacties";
        Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $datum));

        $l = MaakObject('Login');

        if (!$l->isBeheerder() &&  !$l->isBeheerderDDWV())
            throw new Exception("401;Gebruiker mag geen transacties aanmaken;");

        isDATE($datum, "DATUM");            // validatie of datum goed ingevoerd is

        $rObj = MaakObject('Rooster');
        $rooster = $rObj->GetObject(null, $datum);

        if ($rooster['DDWV'] === false)
            throw new Exception("401;Datum is geen DDWV dag;");

        Debug(__FILE__, __LINE__, sprintf("%s: rooster %s", $functie, print_r($rooster, true)));

        $sObj = MaakObject('Startlijst');

        $startlijst = $sObj->GetObjects(array('BEGIN_DATUM' => $datum, 'EIND_DATUM' => $datum));
        Debug(__FILE__, __LINE__, sprintf("%s: startlijst %s", $functie, print_r($startlijst, true)));

        $starts = array();
        foreach ($startlijst['dataset'] as $start)
        {
            if ($start['VELD_ID'] === $ddwv->VELD_ID)
            {
                if ($rooster['CLUB_BEDRIJF'] !== true)         // geen club bedrijf, iedereen betaald
                {
                    if (!key_exists($start['VLIEGER_ID'], $starts))
                        $starts[$start['VLIEGER_ID']] = array();

                    array_push($starts[$start['VLIEGER_ID']], $start);
                }
                else
                {
                    $rl = MaakObject('Leden');
                    if (!$rl->isClubVlieger($start['VLIEGER_ID']))  // geen club vlieger, dus betalen
                    {
                        if (!key_exists($start['VLIEGER_ID'], $starts))
                            $starts[$start['VLIEGER_ID']] = array();

                        array_push($starts[$start['VLIEGER_ID']], $start);
                    }
                }
            }
        }

        Debug(__FILE__, __LINE__, sprintf("%s: starts %s", $functie, print_r($starts, true)));

        $aObj = MaakObject('AanwezigLeden');
        $alleAanmeldingen = $aObj->GetObjects(array('BEGIN_DATUM' => $datum, 'EIND_DATUM' => $datum));

        Debug(__FILE__, __LINE__, sprintf("%s: alle aanmeldingen %s", $functie, print_r($alleAanmeldingen, true)));

        $aanmeldingen = array();
        foreach ($alleAanmeldingen['dataset'] as $aanmelding)
        {
            if ($aanmelding['VELD_ID'] == $ddwv->VELD_ID)
            {
                $aanmeldingen[$aanmelding['LID_ID']] = $aanmelding;     // omzetten naar array met lid_id als key
            }
        }
        Debug(__FILE__, __LINE__, sprintf("%s: aanmeldingen %s", $functie, print_r($aanmeldingen, true)));

        if (count($starts) === 0 && count($aanmeldingen) === 0)
            throw new Exception("401;Geen DDWV infomatie aanwezig;");


        $typeObj = MaakObject('Types');
        $types = $typeObj->GetObjects(array('GROEP' => 20));

        foreach ($types['dataset'] as $type)
            $tarieven[$type['ID']] = $type;     // omzetten naar array met type_id als key

        Debug(__FILE__, __LINE__, sprintf("%s: tarieven %s", $functie, print_r($tarieven, true)));

        // nu we alles weten, kunnen we beginnen met het verwerken
        $trObj = MaakObject('Transacties');

        // beginnen met de starts
        foreach ($starts as $vlieger)
        {
            $teBetalen = 0;
            $aantal_lierstarts = 0;
            foreach ($vlieger as $start)
            {
                if ($start['STARTMETHODE_ID'] == 550)       // 550 = lieren
                    $aantal_lierstarts++;
            }

            $rl = MaakObject('Leden');
            $lid = $rl->GetObject($start['VLIEGER_ID']);

            if (!$rl->isClubVlieger(null, $lid))  // geen club vlieger, dus lidmaatschap betalen
            {
                // heeft lid al 3x betaald?
                $jaar = date('Y', strtotime($datum)); // Extract year from the date

                $beginDatum = $jaar . "-01-01";
                $eindDatum = $jaar . "-12-31";
                $transacties = $trObj->GetObjects(array('LID_ID' => $lid['ID'], 'BEGIN_DATUM' => $beginDatum, 'EIND_DATUM' => $eindDatum));

                $tarief = $tarieven[2018];          // 2018 = Indivueel lidmaatschap

                $aantal_lidmaatschap = 0;
                foreach ($transacties as $t)
                {
                    if ($start['TYPE_ID'] === $tarief['ID'])
                        $aantal_lidmaatschap++;
                }

                if ($aantal_lidmaatschap < 3)
                {
                    $transactie = array();
                    $transactie['DDWV'] = true;
                    $transactie['LID_ID'] = $lid['ID'];
                    $transactie['TYPE_ID'] = $tarief['ID'];
                    $transactie['EENHEDEN'] = 1;
                    $transactie['VLIEGDAG'] = $datum;
                    $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
                    $transactie['BEDRAG'] = $tarief['BEDRAG'];

                    $id = $trObj->AddObject($transactie, false);
                    $teBetalen += $tarief['BEDRAG'];
                }
            }

            // basis tarief, geld voor iedereen
            $tarief = $tarieven[2020];          // 2020 = basis tarief

            $transactie = array();
            $transactie['DDWV'] = true;
            $transactie['LID_ID'] = $lid['ID'];
            $transactie['TYPE_ID'] = $tarief['ID'];
            $transactie['EENHEDEN'] = 1;
            $transactie['VLIEGDAG'] = $datum;
            $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
            $transactie['BEDRAG'] = $tarief['BEDRAG'];

            $id = $trObj->AddObject($transactie, false);
            $teBetalen += $tarief['BEDRAG'];

            if ($aantal_lierstarts > 0)
            {
                $tarief = $tarieven[2021];          // 2021 = eerste lierstart

                $transactie = array();
                $transactie['DDWV'] = true;
                $transactie['LID_ID'] = $lid['ID'];
                $transactie['TYPE_ID'] = $tarief['ID'];
                $transactie['EENHEDEN'] = 1;
                $transactie['VLIEGDAG'] = $datum;
                $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
                $transactie['BEDRAG'] = $tarief['BEDRAG'];;

                $id = $trObj->AddObject($transactie, false);
                $teBetalen += $tarief['BEDRAG'];
            }

            if ($aantal_lierstarts > 2)
            {
                $tarief = $tarieven[2022];          // 2022 = extra lierstarts

                $transactie = array();
                $transactie['DDWV'] = true;
                $transactie['LID_ID'] = $lid['ID'];
                $transactie['TYPE_ID'] = $tarief['ID'];
                $transactie['EENHEDEN'] = 1;
                $transactie['VLIEGDAG'] = $datum;
                $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
                $transactie['BEDRAG'] = $tarief['BEDRAG'];

                $id = $trObj->AddObject($transactie, false);
                $teBetalen += $tarief['BEDRAG'];
            }

            if ($lid['TEGOED'] > 0)
            {
                $strippen = floor($teBetalen / 5);         // en strip heeft de waarde van 5 euro

                if ($strippen > $lid['TEGOED'])
                    $strippen = $lid['TEGOED'];

                $tarief = $tarieven[2010];          // 2010 = afboeken voorraad strippen

                $transactie = array();
                $transactie['DDWV'] = true;
                $transactie['LID_ID'] = $lid['ID'];
                $transactie['TYPE_ID'] = $tarief['ID'];
                $transactie['EENHEDEN'] = -1 * $strippen;
                $transactie['VLIEGDAG'] = $datum;
                $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
                $transactie['BEDRAG'] = -1 * $strippen * 5;

                $id = $trObj->AddObject($transactie, true);
            }
        }

        // nu nog aanmeldingen die niet gestart zijn
        foreach ($aanmeldingen as $aanmelding)
        {

            if (key_exists($aanmelding['LID_ID'], $starts))
                continue;

            Debug(__FILE__, __LINE__, sprintf("%s: aanmelding, maar niet gestart %s", $functie, print_r($aanmelding, true)));

            $lid = $rl->GetObject($aanmelding['LID_ID']);

            // basis tarief, geld voor iedereen
            $tarief = $tarieven[2020];          // 2020 = basis tarief

            $transactie = array();
            $transactie['DDWV'] = true;
            $transactie['LID_ID'] = $lid['ID'];
            $transactie['TYPE_ID'] = $tarief['ID'];
            $transactie['EENHEDEN'] = 1;
            $transactie['VLIEGDAG'] = $datum;
            $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
            $transactie['BEDRAG'] = $tarief['BEDRAG'];

            $id = $trObj->AddObject($transactie, false);
            $teBetalen = $tarief['BEDRAG'];

            if ($lid['TEGOED'] > 0)
            {
                $strippen = floor($teBetalen / 5);         // en strip heeft de waarde van 5 euro

                if ($strippen > $lid['TEGOED'])
                    $strippen = $lid['TEGOED'];

                $tarief = $tarieven[2010];          // 2010 = afboeken voorraad strippen

                $transactie = array();
                $transactie['DDWV'] = true;
                $transactie['LID_ID'] = $lid['ID'];
                $transactie['TYPE_ID'] = $tarief['ID'];
                $transactie['EENHEDEN'] = -1 * $strippen;
                $transactie['VLIEGDAG'] = $datum;
                $transactie['OMSCHRIJVING'] = $tarief['OMSCHRIJVING'];
                $transactie['BEDRAG'] = -1 * $strippen * 5;

                $id = $trObj->AddObject($transactie, true);
            }

        }
    }
}
