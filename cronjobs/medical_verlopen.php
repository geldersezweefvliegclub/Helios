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
    Beste %s,
</p>
<p>
    In onze administratie staat dat je medical binnekort verloopt, of al verlopen is. Wij hebben <b>%s</b> geregistreerd staan als geldigheidsdatum. Deze mail is een herinnering om je medical tijdig te verlengen. 
</p>
<p>     
    Mocht je inmiddels opnieuw gekeurd zijn, pas dan de nieuwe geldigheidsdatum aan in je profiel. Dat is handig omdat we tijdens het invoeren van een nieuwe start een controle uitvoeren of je medical nog geldig is. Een verlopen medical zal tot vragen leiden en daarmee het vliegbedrijf onnodig vertragen. 
</p>
<p> 
    Bedankt voor de medewerking en met vriendelijke groet,
</p>
<p>     
    De startadministratie  
</p>

<p>
    PS: Indien je medical niet meer verlengt kan worden, verwijder dan de datum uit je profiel. Je krijgt dan geen herinneringen meer. 
</p>   

</body></html>";


$datum = date('Y-m-d');
$url_args = "TYPES=601,602,603&VELDEN=VOORNAAM,NAAM,MEDICAL,EMAIL";

if (isset($_GET['ID'])) {
    $url_args .= "&ID=" . $_GET['ID'];
    $criteria = 0;      // alleen bij verlopen medical
}
else
{
    $criteria = 61;     // mail als medical binnen 2 maanden verloopt
}

heliosInit("Leden/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body    = "Leden/GetObjects?" . $url_args . "\n";
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
    $leden = json_decode($body, true);

    foreach ($leden['dataset'] as $lid)
    {
        if (!isset($lid['MEDICAL'])) continue;    // geen medical datum bekend
        $nu = time();
        $medical = strtotime($lid['MEDICAL']);

        $diffDays = ($medical - $nu) / (60*60*24);

        if ($diffDays < $criteria)
        {
            $mail = emailInit();
            $mail->addAddress($lid['EMAIL'], $lid['NAAM']);

            $mail->Subject = 'Geldigheid medical';
            $mail->isHTML(true);                                  		
            $mail->Body = sprintf($htmlContent, $lid['VOORNAAM'], gmdate("d-m-Y", $medical));

            $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
            $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

            if(!$mail->Send()) {
                print_r($mail);
            }
            die;
        }
    }
}
