<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";
include "MatrixSynapse.php";
include "e-boekhouden.php";

$ledenSync = array();       // Leden die gesynchroniseerd moeten worden
$dataSync = array();        // Data die gesynchroniseerd moet worden

if (isset($_GET['Leden']))
{
    // iedereen die opgenomen is in het ledenbestand moet gesynchroniseerd worden
    // 600 = Student, 601 = Erelid, 602 = Lid, 603 = Jeugdlid, 604 = private owner, 605 = veteraan, 606 = Donateur, 608 = 5 rittenkaart houder
    heliosInit("Leden/GetObjects?VELDEN=ID&TYPES=600,601,602,603,604,605,606,608");
    $result = curl_exec($curl_session);

    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        Error(__FILE__, __LINE__, "Leden/GetObjects: " . $status_code . "; " . $body . "; " . print_r($header, true));
        emailError($result);
        die;
    }
    else {
        $leden = json_decode($body, true);

        foreach ($leden['dataset'] as $lid)
        {
            $ledenSync[] = $lid['ID'];
        }
    }
/*
    // iedereen die verwijderd is moet ook gecontroleerd worden
    heliosInit("Leden/GetObjects?VELDEN=ID&VERWIJDERD=1");
    $result = curl_exec($curl_session);

    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        Error(__FILE__, __LINE__, "Leden/GetObjects VERWIJDERD=1: " . $status_code . "; " . $body . "; " . print_r($header, true));
        emailError($result);
        die;
    }
    else {
        $leden = json_decode($body, true);

        foreach ($leden['dataset'] as $lid)
        {
            $ledenSync[] = $lid['ID'];
        }
    }
*/
}
else
{

    heliosInit("Sync/GetObjects");

    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        Error(__FILE__, __LINE__, "Sync/GetObjects: " . $status_code . "; " . $body . "; " . print_r($header, true));
        emailError($result);
        die;
    } else {
        $items = json_decode($body, true);

        if (count($items) == 0) {
            // er zijn geen items om te synchroniseren
            //die;
        }

        foreach ($items as $item) {
            if (isset($item['LID_ID']))
            {
                $ledenSync[] = $item['LID_ID'];
                $dataSync[$item['LID_ID']] = $item;
            }
        }
    }
}


$removeSyncID = array();
foreach ($ledenSync as $lid_id)
{
    heliosInit("Leden/GetObject?ID=" . $lid_id);
    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header,$body) = returnHeaderBody($result);

    if ($status_code != 200)
        continue;

    $lid = json_decode($body, true);

    $syncDataRecord = array_key_exists($lid_id, $dataSync) ? $dataSync[$lid_id] : array();
    $syncID = array_key_exists('ID', $syncDataRecord) ? $syncDataRecord['ID'] : "--";

    Debug(__FILE__, __LINE__, sprintf("Sync ID %s: %s %s %s", $syncID, $lid_id, $lid['NAAM'], json_encode($syncDataRecord)));

    // Syncen van de data naar e-boekhouden
    if (isset($lid['LIDNR']))
    {
        if ($lid['VERWIJDERD'])
        {
            // Lid is verwijderd, maar niet in e-boekhouden
            // Dit kan gebeuren als een lid verwijderd wordt, maar de synchronisatie nog niet heeft plaatsgevonden
            // In dat geval moet het lid alsnog verwijderd worden
            eboekhouden::verwijderLid($lid);
        }
        else
        {
            eboekhouden::updateLid($lid);
        }
    }


    // Synapse synchronisatie
    if (!isset($lid['INLOGNAAM'])) {
        Error(__FILE__, __LINE__, sprintf ("Lid %s heeft geen inlognaam: ", $lid['NAAM']));
    }
    else
    {
        try
        {
            if ($lid['VERWIJDERD']) {
                synapse::verwijderGebruiker($lid);
            } else {
                switch ($lid['LIDTYPE_ID']) {
                    case 600:   // 600 = Student
                    case 601:   // 601 = Erelid
                    case 602:   // 602 = Lid
                    case 603:   // 603 = Jeugdlid
                    case 604:   // 604 = private owner
                    case 605:   // 605 = veteraan
                    case 606:   // 606 = Donateur
                    case 608:   // 608 = 5 rittenkaart houder
                        $data = array_key_exists("DATA", $syncDataRecord) ? $syncDataRecord['DATA'] : array();
                        $ww = array_key_exists("INGEVOERD_WACHTWOORD", $data) ? $data['INGEVOERD_WACHTWOORD'] : null;

                        if (array_key_exists("ID", $syncDataRecord))
                            $removeSyncID[] = $syncDataRecord['ID'];

                        Debug(__FILE__, __LINE__, sprintf("UpdateGebruiker: %s %s", $lid['INLOGNAAM'], $ww));
                        synapse::updateGebruiker($lid, $ww);
                        synapse::toevoegenAanKamers($lid);
                        synapse::markeerAlsFavoriet($lid, $ww);
                        break;
                    case 607:   // 607 = Zusterclub
                    case 613:   // 613 = Systeem account
                    case 620:   // 620 = Wachtlijst
                    case 625:   // 625 = DDWV
                        synapse::verwijderGebruiker($lid);
                        break;
                    default:
                        Error(__FILE__, __LINE__, sprintf("Onbekend lidtype %s: %s", $lid['LIDTYPE_ID'], $lid['NAAM']));
                        break;
                }
            }

            if (array_key_exists("ID", $syncDataRecord))
            {
                Debug(__FILE__, __LINE__, "Sync/DeleteObject: " . $syncDataRecord['ID']);
                heliosInit("Sync/DeleteObject?ID=" . $syncDataRecord['ID'], "DELETE");

                $result      = curl_exec($curl_session);
                $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
                list($header, $body) = returnHeaderBody($result);

                if ($status_code != 204) // We verwachten een status code van 200
                {
                    Error(__FILE__, __LINE__, "Sync/DeleteObject: " . $status_code . "; " . $body . "; " . print_r($header, true));
                    emailError($result);
                }
            }
        }
        catch (Exception $e) {
            continue;
        }
    }
}



