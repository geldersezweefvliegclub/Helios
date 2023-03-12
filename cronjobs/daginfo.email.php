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
</p>

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
$url_args = "DATUM=$datum&TABEL=oper_dagrapporten";
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

        if (in_array($resultaat['ID'], $verzonden))
            continue;       // datum is al een keer verzonden

        // Ophalen DagInfo
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

        // Ophalen dag rapport
        $url_args = "ID=" . $resultaat['ID'];
        heliosInit("DagRapporten/GetObjects?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        if ($status_code != 200) // We verwachten een status code van 200
        {
            emailError($result);
            continue;   
        }
        $dagRapport = json_decode($body, true);

        $htmlBody = $htmlContent;
        $htmlBody =  str_replace("[DATUM]",$datumString, $htmlBody);
        $htmlBody =  str_replace("[VLIEGVELD]",$dagInfo['dataset'][0]['VELD_OMS'], $htmlBody);
        $htmlBody =  str_replace("[STRIP]",$dagInfo['dataset'][0]['BAAN_OMS'], $htmlBody);
        $htmlBody =  str_replace("[STARTMETHODE]",$dagInfo['dataset'][0]['STARTMETHODE_OMS'], $htmlBody);
        $htmlBody =  str_replace("[AANWEZIGHEID]",$dagInfo['dataset'][0]['DIENSTEN'], $htmlBody);

        $laatsteAanpassing = explode(' ', $dagRapport['dataset'][0]['LAATSTE_AANPASSING']);
        $datumParts = explode('-', $laatsteAanpassing[0]);
        $hhmm = substr($laatsteAanpassing[1], 0, 5);

        $htmlBody =  str_replace("[hh:mm]",$hhmm, $htmlBody);
        $htmlBody =  str_replace("[dd-mm-yyyy]", sprintf("%s-%s-%s", $datumParts[2], $datumParts[1], $datumParts[0]), $htmlBody);
        $htmlBody =  str_replace("[DR_AUTHOR]",$dagRapport['dataset'][0]['INGEVOERD'], $htmlBody);
        $htmlBody =  str_replace("[DR_STRIP]",$dagRapport['dataset'][0]['VELD_OMS'], $htmlBody);

        $htmlBody =  str_replace("[VLIEGEND]",$dagRapport['dataset'][0]['VLIEGENDMATERIEEL'], $htmlBody);
        $htmlBody =  str_replace("[ROLLEND]",$dagRapport['dataset'][0]['ROLLENDMATERIEEL'], $htmlBody);
        $htmlBody =  str_replace("[VERSLAG]",$dagRapport['dataset'][0]['VERSLAG'], $htmlBody);
        $htmlBody =  str_replace("[INCIDENTEN]",$dagRapport['dataset'][0]['INCIDENTEN'], $htmlBody);

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

        array_push($verzonden, $dagInfo['dataset'][0]['ID']);

    }
}
