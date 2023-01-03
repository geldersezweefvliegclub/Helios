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
    Je bent geabbonneerd op de dag rapporten mailing lijst. Hierbij ontvangt u het dagrapport <b>[DATUM]</b>
</p>

<h1>Algemene informatie</h1>
<p>
    
    <table>
        <tr><td>Vliegveld :</td><td>[VLIEGVELD]</td></tr>
        <tr><td>Strip :</td><td>[STRIP]</td></tr>
        <tr><td>Standaard startmethode :</td><td>[STARTMETHODE]</td></tr>
        <tr><td>Bedrijf :</td><td>[BEDRIJF]</td></tr>
    </table>
</p>

<h1>Aanwezigheid diensten</h1>
<p>
    [AANWEZIGHEID]
</p>

<h1>Meteo</h1>
<p>
    [METEO]
</p>

<h1>Vliegbedrijf</h1>
<p>
    [VLIEGBEDRIJF]
</p>

<h1>Vliegend materieel</h1>
<p>
    [VLIEGEND]
</p>

<h1>Rollend materieel</h1>
<p>
    [ROLLEND]
</p>

<h1>Verslag</h1>
<p>
    [VERSLAG]
</p>

<h1>Incidenten</h1>
<p>
    [INCIDENTEN]
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";


// ophalen wie er allemaal een email ontvangen. Dat zijn alleen instructeurs en beheerders
$ontvangers = array();
heliosInit("Leden/GetObjects?INSTRUCTEURS=true");
$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

$leden = json_decode($body, true); 
foreach ($leden['dataset']as $lid) {
    if ($lid['EMAIL_DAGINFO'] == true) {
        array_push($ontvangers , array('NAAM' => $lid['NAAM'], 'EMAIL' => $lid['EMAIL']));
    }    
}
heliosInit("Leden/GetObjects?BEHEERDERS=true");
$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

$leden = json_decode($body, true); 
foreach ($leden['dataset']as $lid) {
    if ($lid['EMAIL_DAGINFO'] == true) {
        array_push($ontvangers , array('NAAM' => $lid['NAAM'], 'EMAIL' => $lid['EMAIL']));
    }    
}

$datum = date('Y-m-d');
$url_args = "DATUM=$datum&TABEL=oper_daginfo";
heliosInit("Audit/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
else
{
    $auditRecords = json_decode($body, true);   
    if (count($auditRecords['dataset']) == 0)
    {
        die;    // er is geen dag info gewijzigd
    }

    if (count($ontvangers) == 0) 
    {
        die;    // er is niemand die geabonneerd is
    }
    
    $verzonden = array();

    foreach ($auditRecords['dataset'] as $record)
    {
        $resultaat = json_decode(preg_replace('/\r|\n/', '<br>', trim($record['RESULTAAT'])), true);

        if (in_array($resultaat['DATUM'], $verzonden))
            continue;       // datum is al een keer verzonden

        $url_args = "DATUM=" . $resultaat['DATUM'];
        heliosInit("Daginfo/GetObjects?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        if ($status_code != 200) // We verwachten een status code van 200
        {
            emailError($result);
            continue;   
        }

        $dagInfo = json_decode($body, true); 
        $d = explode("-", $dagInfo['dataset'][0]['DATUM']);
        $datumString = sprintf("%02d-%02d-%s", $d[2]*1, $d[1]*1, $d[0]);

        $doorgaan = false;
        if (!empty($dagInfo['dataset'][0]['METEO']))                $doorgaan = true;
        if (!empty($dagInfo['dataset'][0]['VLIEGBEDRIJF']))         $doorgaan = true;
        if (!empty($dagInfo['dataset'][0]['VLIEGENDMATERIEEL']))    $doorgaan = true;
        if (!empty($dagInfo['dataset'][0]['ROLLENDMATERIEEL']))     $doorgaan = true;
        if (!empty($dagInfo['dataset'][0]['VERSLAG']))              $doorgaan = true;
        if (!empty($dagInfo['dataset'][0]['INCIDENTEN']))           $doorgaan = true;

        if (!$doorgaan)         // er is geen zinnige info 
            continue;

        $htmlBody = $htmlContent;
        $htmlBody =  str_replace("[DATUM]",$datumString, $htmlBody);
        $htmlBody =  str_replace("[VLIEGVELD]",$dagInfo['dataset'][0]['VELD_OMS'], $htmlBody);
        $htmlBody =  str_replace("[STRIP]",$dagInfo['dataset'][0]['BAAN_OMS'], $htmlBody);
        $htmlBody =  str_replace("[STARTMETHODE]",$dagInfo['dataset'][0]['STARTMETHODE_OMS'], $htmlBody);
        $htmlBody =  str_replace("[AANWEZIGHEID]",$dagInfo['dataset'][0]['DIENSTEN'], $htmlBody);
        $htmlBody =  str_replace("[METEO]",$dagInfo['dataset'][0]['METEO'], $htmlBody);
        $htmlBody =  str_replace("[VLIEGBEDRIJF]",$dagInfo['dataset'][0]['VLIEGBEDRIJF'], $htmlBody);
        $htmlBody =  str_replace("[VLIEGEND]",$dagInfo['dataset'][0]['VLIEGENDMATERIEEL'], $htmlBody);
        $htmlBody =  str_replace("[ROLLEND]",$dagInfo['dataset'][0]['ROLLENDMATERIEEL'], $htmlBody);
        $htmlBody =  str_replace("[VERSLAG]",$dagInfo['dataset'][0]['VERSLAG'], $htmlBody);
        $htmlBody =  str_replace("[INCIDENTEN]",$dagInfo['dataset'][0]['INCIDENTEN'], $htmlBody);

        $bedrijf = "";
        $bedrijf .= ($dagInfo['dataset'][0]['DDWV'] == true) ? "DDWV" : "";
        if ($dagInfo['dataset'][0]['CLUB_BEDRIJF'] == true)
        {
            $bedrijf .= ($bedrijf == "") ? "club bedrijf" : " en club bedrijf";
        }
        $htmlBody =  str_replace("[BEDRIJF]",$bedrijf, $htmlBody);
    
        // email 
        $mail = emailInit();

        $mail->Subject = 'Dagrapport van ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $htmlBody ;
        
        foreach ($ontvangers as $geadresseerde) {
            $mail->addAddress($geadresseerde['EMAIL'], $geadresseerde['NAAM']); 
        }
        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        if(!$mail->Send()) {
            print_r($mail);
        }

        array_push($verzonden, $dagInfo['dataset'][0]['DATUM']);

    }
}
