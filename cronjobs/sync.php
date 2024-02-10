<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";
include "MatrixSynapse.php";

if (!isset($matrix_settings)) {
    echo "Matrix settings not set";
    exit;
}

if (isset($_GET['Leden'])) {
    // Alle leden synchroniseren

    // 600 = Student
    // 601 = Erelid
    // 602 = Lid
    // 603 = Jeugdlid
    // 604 = private owner
    // 605 = veteraan
    // 606 = Donateur
    // 625 = DDWV

    heliosInit("Leden/GetObjects?TYPES=600,601,602,603,605,606");
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
            if (!isset($lid['INLOGNAAM'])) {
                HeliosError(__FILE__, __LINE__, sprintf ("Lid %s heeft geen inlognaam: ", $lid['NAAM']));
                continue;
            }
            synapse::updateGebruiker($lid, null);
            synapse::toevoegenAanKamers($lid);
        }
        sleep(60);
    }
}

heliosInit("Sync/GetObjects");

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    Error(__FILE__, __LINE__, "Sync/GetObjects: " . $status_code . "; " . $body . "; " . print_r($header, true));
    emailError($result);
    die;
}
else {
    $items = json_decode($body, true);

    if (count($items) == 0) {
        // er zijn geen items om te synchroniseren
        die;
    }

    $idsToBeRemoved = array();
    foreach ($items as $item) {
        if (isset($item['LID_ID']))     // sync leden
        {
            $lid = $item['DATA'];
            if (!isset($item['DATA'])) {    // we hebben wel een ID, geen DATA
                heliosInit("Leden/GetObject?ID=" . $item['LID_ID']);
                $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
                list($header, $lid) = returnHeaderBody($result);

                if ($status_code != 200)
                    continue;
            }
            Debug(__FILE__, __LINE__, sprintf("Sync ID %s: %s %s", $item['ID'], $item['LID_ID'], $lid['NAAM']));

            if (!isset($lid['INLOGNAAM'])) {
                HeliosError(__FILE__, __LINE__, sprintf ("Lid %s heeft geen inlognaam: ", $lid['NAAM']));
                continue;
            }
            $ww = isset($lid['INGEVOERD_WACHTWOORD']) ? $lid['INGEVOERD_WACHTWOORD'] : null;
            synapse::updateGebruiker($lid, $ww);
            synapse::toevoegenAanKamers($lid);
            synapse::markeerAlsFavoriet($lid, $ww);
        }
        $idsToBeRemoved[] = $item['ID'];
    }

    Debug(__FILE__, __LINE__, "Sync/DeleteObject: " . implode(",", $idsToBeRemoved));
    $csv = implode(",", $idsToBeRemoved);
    heliosInit("Sync/DeleteObject?ID=" . $csv, "DELETE");

    $result      = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 204) // We verwachten een status code van 200
    {
        Error(__FILE__, __LINE__, "Sync/DeleteObject: " . $status_code . "; " . $body . "; " . print_r($header, true));
        emailError($result);
    }
}
