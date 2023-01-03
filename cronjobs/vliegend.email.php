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
    Beste commissaris vliegend,
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
    emailError($result);
    die;
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

        if ($voor['VLIEGENDMATERIEEL'] == $resultaat['VLIEGENDMATERIEEL']) {
            // de daginfo is wel gewijzigd, maar er is geen aanpassing gemaakt in vliegend materieel            
            continue;
        }

        // als we meerdere aanpassingen hebben gedaan, dan alleen laatste resultaat op de mail zetten
        if (!in_array($resultaat['DATUM'], $datumArray)) {
            array_push($datumArray, $resultaat['DATUM']);

            $d = explode("-", $resultaat['DATUM']);
            $datumString = sprintf("%02d-%02d-%s", $d[2]*1, $d[1]*1, $d[0]);

            $body = sprintf($htmlContent, $datumString, $resultaat['VLIEGENDMATERIEEL']);
        
            // email 
            $mail = emailInit();
    
            $mail->Subject = 'Rapportage vliegend ' . $datumString;
            $mail->isHTML(true);                                  		//Set email format to HTML
            $mail->Body    = $body;
           
            $mail->addAddress($vliegend['EMAIL'], $vliegend['NAAM']); 
            $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
            $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);
    
            if(!$mail->Send()) {
                print_r($mail);
            }
        }
    }
}
