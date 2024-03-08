<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";
include "MatrixSynapse.php";

if (!isset($matrix_settings)) {
    echo "Matrix settings not set";
    exit;
}

$ledenSync = array();       // Leden die gesynchroniseerd moeten worden
$dataSync = array();        // Data die gesynchroniseerd moet worden



if (isset($_GET['Leden']))
{
    // iedereen die opgenomen is in het ledenbestand moet gesynchroniseerd worden
    heliosInit("Leden/GetObjects?VELDEN=ID");
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
            die;
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

    if (!isset($lid['INLOGNAAM'])) {
        Error(__FILE__, __LINE__, sprintf ("Lid %s heeft geen inlognaam: ", $lid['NAAM']));

        // gooien wel het sync record weg
        if (array_key_exists("ID", $syncDataRecord))
            $removeSyncID[] = $syncDataRecord['ID'];
        continue;
    }

    try
    {
        if ($lid['VERWIJDERD']) {
            synapse::verwijderGebruiker($lid);
        }
        else {
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
    }
    catch (Exception $e)
    {
        continue;
    }
}

if (count($removeSyncID) == 0) {
    // er zijn geen items om te verwijderen
    die;
}

Debug(__FILE__, __LINE__, "Sync/DeleteObject: " . implode(",", $removeSyncID));
$csv = implode(",", $removeSyncID);
heliosInit("Sync/DeleteObject?ID=" . $csv, "DELETE");

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 204) // We verwachten een status code van 200
{
    Error(__FILE__, __LINE__, "Sync/DeleteObject: " . $status_code . "; " . $body . "; " . print_r($header, true));
    emailError($result);
}

