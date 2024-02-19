<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";


$flarmLijst = file_get_contents('http://ddb.glidernet.org/download');

// Split the string into lines
$lines = explode("\r\n", $flarmLijst);

// Get the header line
$header = str_getcsv(array_shift($lines));
for ($i = 0; $i < count($header); $i++) {
    $header[$i] = str_replace("'", '', $header[$i]);
}



$flarmArray = [];
foreach ($lines as $line) {
    $csv = str_getcsv($line);
    for ($i = 0; $i < count($csv); $i++) {
        $csv[$i] = str_replace("'", '', $csv[$i]);
    }

    if (count($csv) != count($header)) {
        continue;
    }
    $record = array_combine($header, $csv);
    $key = $record['REGISTRATION'];
    $flarmArray[$key] = $record;
}

heliosInit("Vliegtuigen/GetObjects");
$result = curl_exec($curl_session);

$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    Error(__FILE__, __LINE__, "Vliegtuigen/GetObjects: " . $status_code . "; " . $body . "; " . print_r($header, true));
    emailError($result);
    die;
}
else {
    $vliegtuigen = json_decode($body, true);

    foreach ($vliegtuigen['dataset'] as $vliegtuig) {
        if (!isset($vliegtuig['REGISTRATIE'])) {
            HeliosError(__FILE__, __LINE__, sprintf("Vliegtuig %s heeft geen registratie: ", $vliegtuig['NAAM']));
            continue;
        }

        if (!array_key_exists($vliegtuig['REGISTRATIE'], $flarmArray)) {
            continue;
        }

        if ($vliegtuig['FLARMCODE'] === $flarmArray[$vliegtuig['REGISTRATIE']]['DEVICE_ID']) {
            continue;
        }

        // echo "Vliegtuig " . $vliegtuig['REGISTRATIE'] . " heeft een nieuwe FLARM: " . $flarmArray[$vliegtuig['REGISTRATIE']]['DEVICE_ID'] . "\n";

        heliosInit("Vliegtuigen/SaveObject", "PUT");
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode(array("ID" => $vliegtuig['ID'], "FLARMCODE" => $flarmArray[$vliegtuig['REGISTRATIE']]['DEVICE_ID'])));

        $result = curl_exec($curl_session);

        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        if ($status_code != 200) // We verwachten een status code van 200
        {
            Error(__FILE__, __LINE__, "Vliegtuigen/SaveObject: " . $status_code . "; " . $body . "; " . print_r($header, true));
            emailError($result);
        }
    }
}