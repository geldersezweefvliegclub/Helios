<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$htmlContent = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste commissaris rollend,
</p>
<p>
    In onze startadministratie staan de volgende opmerkingen genoteerd in het dagrapport van <b>%s</b>
</p>
<p style='background-color: #dddddd; margin: 10px; padding: 10px;'> 
<b>%s</b>
</p>
<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";


$datum = date('Y-m-d');
$url_args = "DATUM=$datum&TABEL=oper_daginfo";
heliosInit("Audit/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = 'Helios API call mislukt';
    $mail->Body    = "Audit/GetObjects?" . $url_args . "\n";
    $mail->Body   .= "HEADER :\n";
    $mail->Body   .= print_r($header, true);
    $mail->Body   .= "\n";
    $mail->Body   .= "BODY :\n" . $body;

    $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    if(!$mail->Send()) {
        print_r($mail);
    }
}
else
{
    $auditRecords = json_decode($body, true);   
    if (count($auditRecords['dataset']) == 0)
    {
        // er is geen dag info gewijzigd
        die;
    }
    
    $datumArray = array();

    foreach ($auditRecords['dataset'] as $record)
    {
        $voor = json_decode(preg_replace('/\r|\n/', '<br>', trim($record['VOOR'])), true);
        $resultaat = json_decode(preg_replace('/\r|\n/', '<br>', trim($record['RESULTAAT'])), true);

        if ($voor['ROLLENDMATERIEEL'] == $resultaat['ROLLENDMATERIEEL']) {
            // de daginfo is wel gewijzigd, maar er is geen aanpassing gemaakt in rollend materieel            
            continue;
        }

        // als we meerdere aanpassingen hebben gedaan, dan alleen laatste resultaat op de mail zetten
        if (!in_array($resultaat['DATUM'], $datumArray)) {
            array_push($datumArray, $resultaat['DATUM']);

            $d = explode("-", $resultaat['DATUM']);
            $datumString = sprintf("%02d-%02d-%s", $d[2]*1, $d[1]*1, $d[0]);

            $body = sprintf($htmlContent, $datumString, $resultaat['ROLLENDMATERIEEL']);
        
            // email 
            $mail = emailInit();
    
            $mail->Subject = 'Rapportage rollend ' . $datumString;
            $mail->isHTML(true);                                  		//Set email format to HTML
            $mail->Body    = $body;
           
            $mail->addAddress($rollend['EMAIL'], $rollend['NAAM']); 
            $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
            $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);
    
            if(!$mail->Send()) {
                print_r($mail);
            }
            
        }
    }
}