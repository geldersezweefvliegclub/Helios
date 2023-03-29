<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$htmlContent = "
<html>
<body>

<p>
    Hallo,
</p>
<p>
    Je bent geabonneerd op de dag rapporten mailing lijst. Hierbij ontvangt u het dagrapport <b>[DATUM]</b>
</p>

[DAGINFO]

[DAGRAPPORTEN]
</body></html>";

$htmlDagInfo= "
<h3>Algemene informatie</h3>
<p>
    <table>
        <tr><td>Vliegveld :</td><td>[VLIEGVELD]</td></tr>
        <tr><td>Strip :</td><td>[STRIP]</td></tr>
        <tr><td>Standaard startmethode :</td><td>[STARTMETHODE]</td></tr>
        <tr><td>Bedrijf :</td><td>[BEDRIJF]</td></tr>
    </table>
</p>

<h3>Aanwezigheid diensten</h3>
<p>
[AANWEZIGHEID]
</p><hr>";

$rapportContent = "
<p>
Op [dd-mm-yyyy] om [hh:mm] schreef [DR_AUTHOR] dagrapport voor vliegveld [DR_STRIP]
</p>

<h3>Vliegend materieel</h3>
<p>
    [VLIEGEND]
</p>

<h3>Rollend materieel</h3>
<p>
    [ROLLEND]
</p>

<h3>Verslag</h3>
<p>
    [VERSLAG]
</p>

<h3>Incidenten</h3>
<p>
    [INCIDENTEN]
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>";


// ophalen wie er allemaal een email ontvangen. Dat zijn alleen instructeurs en beheerders
$ontvangers = array();
heliosInit("Leden/GetObjects?INSTRUCTEURS=true");
$result = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

$leden = json_decode($body, true);
foreach ($leden['dataset'] as $lid) {
    if ($lid['EMAIL_DAGINFO'] == true) {
        array_push($ontvangers, array('NAAM' => $lid['NAAM'], 'EMAIL' => $lid['EMAIL']));
    }
}
heliosInit("Leden/GetObjects?BEHEERDERS=true");
$result = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

$leden = json_decode($body, true);
foreach ($leden['dataset'] as $lid) {
    if ($lid['EMAIL_DAGINFO'] == true) {
        array_push($ontvangers, array('NAAM' => $lid['NAAM'], 'EMAIL' => $lid['EMAIL']));
    }
}
printf("<span>ontvangers: %s </span>", print_r($ontvangers, true));
// we weten nu wie er allemaal een email moet hebben, staat in array $ontvangers

// kijk of er een dagrapport aangemaakt / bewerkt is in de audit tabel
$datum = date('Y-m-d');
$url_args = "DATUM=$datum&TABEL=oper_dagrapporten";
heliosInit("Audit/GetObjects?" . $url_args);

$result = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

$auditRecords = json_decode($body, true);
if (count($auditRecords['dataset']) == 0) {
    die;    // er is geen dag info gewijzigd
}

$dagen = array();

foreach ($auditRecords['dataset'] as $record) {
    $resultaat = json_decode(preg_replace('/\r|\n/', '<br>', trim($record['RESULTAAT'])), true);

    if (in_array($resultaat['DATUM'], $dagen))
        continue;       // datum is al een keer verzonden

    array_push($dagen, $resultaat['DATUM']);
}
printf("<span>dagen: %s </span><hr>", print_r($dagen, true));

// Voor alle dagen een rapport versturen
foreach ($dagen as $dag) {
    $d = explode("-", $dag);
    $datumString = sprintf("%02d-%02d-%s", $d[2] * 1, $d[1] * 1, $d[0]);

    $htmlBody = str_replace("[DATUM]", $datumString, $htmlContent);

    // Ophalen DagInfo
    $url_args = "DATUM=" . $dag;
    heliosInit("Daginfo/GetObjects?" . $url_args);

    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        emailError($result);
        continue;
    }

    $dagInfo = json_decode($body, true);
    printf("<span>dagInfo: %s </span><hr>", print_r($dagInfo, true));

    // als er daginfo aanwezig is, beginnen we daarmee
    if ($dagInfo['totaal'] == 0)
    {
        $htmlDI= "";
    }
    else
    {

        $htmlDI = str_replace("[DATUM]", $datumString, $htmlDagInfo);
        $htmlDI = str_replace("[VLIEGVELD]", $dagInfo['dataset'][0]['VELD_OMS'], $htmlDI);
        $htmlDI = str_replace("[STRIP]", $dagInfo['dataset'][0]['BAAN_OMS'], $htmlDI);
        $htmlDI = str_replace("[STARTMETHODE]", $dagInfo['dataset'][0]['STARTMETHODE_OMS'], $htmlDI);
        $htmlDI = str_replace("[AANWEZIGHEID]", $dagInfo['dataset'][0]['DIENSTEN'], $htmlDI);

        $bedrijf = "";
        $bedrijf .= ($dagInfo['dataset'][0]['DDWV'] == true) ? "DDWV" : "";
        if ($dagInfo['dataset'][0]['CLUB_BEDRIJF'] == true) {
            $bedrijf .= ($bedrijf == "") ? "club bedrijf" : " en club bedrijf";
        }
        $htmlDI = str_replace("[BEDRIJF]", $bedrijf, $htmlDI);
    }
    $htmlBody = str_replace("[DAGINFO]", $htmlDI, $htmlBody);

    // Ophalen dag rapporten van de dag
    $url_args = "DATUM=" . $dag;
    heliosInit("DagRapporten/GetObjects?" . $url_args);

    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        emailError($result);
        continue;
    }
    $dagRapporten = json_decode($body, true);
    $dagRapportEmailContent="";

    printf("<span>dagRapporten: %s </span><hr>", print_r($dagRapporten, true));

    foreach ($dagRapporten['dataset'] as $dagRapport)
    {
        $laatsteAanpassing = explode(' ', $dagRapport['LAATSTE_AANPASSING']);
        $datumParts = explode('-', $laatsteAanpassing[0]);
        $hhmm = substr($laatsteAanpassing[1], 0, 5);

        $inhoud = $rapportContent;

        $inhoud = str_replace("[hh:mm]", $hhmm, $inhoud);
        $inhoud = str_replace("[dd-mm-yyyy]", sprintf("%s-%s-%s", $datumParts[2], $datumParts[1], $datumParts[0]), $inhoud);
        $inhoud = str_replace("[DR_AUTHOR]", $dagRapport['INGEVOERD'], $inhoud);
        $inhoud = str_replace("[DR_STRIP]", $dagRapport['VELD_OMS'], $inhoud);

        $inhoud = str_replace("[VLIEGEND]", $dagRapport['VLIEGENDMATERIEEL'], $inhoud);
        $inhoud = str_replace("[ROLLEND]", $dagRapport['ROLLENDMATERIEEL'], $inhoud);
        $inhoud = str_replace("[VERSLAG]", $dagRapport['VERSLAG'], $inhoud);
        $inhoud = str_replace("[INCIDENTEN]", $dagRapport['INCIDENTEN'], $inhoud);

        $dagRapportEmailContent .= $inhoud;
    }

    printf("<span>dagRapportEmailContent: %s </span><hr>", $dagRapportEmailContent);
    if ($dagRapportEmailContent == "")
        $htmlBody = str_replace("[DAGRAPPORTEN]", "", $htmlBody);
    else
        $htmlBody = str_replace("[DAGRAPPORTEN]", $dagRapportEmailContent, $htmlBody);

    printf("<span>htmlBody: %s </span><hr>", $htmlBody);

    // email
    $mail = emailInit();

    $mail->Subject = 'Dagrapport van ' . $datumString;
    $mail->isHTML(true);                                        //Set email format to HTML
    $mail->Body = $htmlBody;

    $mail->addAddress($cimt['EMAIL'], $cimt['NAAM']);           // CI-MT krijgt altijd email

    foreach ($ontvangers as $geadresseerde) {
        $mail->addAddress($geadresseerde['EMAIL'], $geadresseerde['NAAM']);
    }
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

    if (!$mail->Send()) {
        print_r($mail);
    }
}
