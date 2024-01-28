<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";
include "MatrixSynapse.php";

if (!isset($matrix_settings)) {
    echo "Matrix settings not set";
    exit;
}

$alleLeden = false;
if (isset($_GET['Leden'])) {
    $alleLeden = true;
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
