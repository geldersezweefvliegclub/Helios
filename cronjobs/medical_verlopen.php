<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$htmlContentBulk = "
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

$htmlContentSingleID = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
    Wij hebben <b>%s</b> geregistreerd staan als geldigheidsdatum voor je medical. In de startadministratie staat voor jou een vlucht genoteerd waarbij je medical verlopen is. 
    We gaan er vanuit dat je profiel nog niet aangepast is met de nieuwe geldigheidsdatum van je medical. Of dat er misschien een verkeerde invoer in de startadministratie is gedaan.
</p>
<p>
    Mocht je echter gevlogen hebben met een verlopen medical, dan verzoeken we contact op the nemen met het CI-MT.
</p>
<p> 
    Bedankt voor de medewerking en met vriendelijke groet,
</p>
<p>     
    De startadministratie  
</p>

<p>
    PS: Een kopie van de mail is verzonden aan het CI-MT 
</p>   

</body></html>";



$datum = date('Y-m-d');
$url_args = "TYPES=601,602,603,604&VELDEN=VOORNAAM,NAAM,MEDICAL,EMAIL";

if (isset($_GET['ID'])) {
    $url_args .= "&ID=" . $_GET['ID'];
    $htmlContent = $htmlContentSingleID;

    $criteria = 0;      // alleen bij verlopen medical
}
else
{
    $htmlContent = $htmlContentBulk;
    $criteria = 61;     // mail als medical binnen 2 maanden verloopt
}

heliosInit("Leden/GetObjects?" . $url_args);

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
    $leden = json_decode($body, true);

    echo json_encode($leden);
    foreach ($leden['dataset'] as $lid)
    {
        if (!isset($lid['MEDICAL'])) continue;    // geen medical datum bekend

        $nu = new DateTime();
        $medical = new DateTime($lid['MEDICAL']);
        $diffDays = 1*$nu->diff($medical)->format('%r%a');	// total days between the two times

        if ($diffDays < $criteria)
        {
            $mail = emailInit();
            $mail->addAddress($lid['EMAIL'], $lid['NAAM']);

            $mail->Subject = 'Geldigheid medical';
            $mail->isHTML(true);                                  		
            $mail->Body = sprintf($htmlContent, $lid['VOORNAAM'], $medical->format('d-m-Y'));

            if (isset($_GET['ID'])) {
                $mail->addCC($cimt['EMAIL'], $cimt['NAAM']);
            }

            $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
            $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

            printf("%s %s: medical geldigheid %s <br>\n", date("d-m-Y"), $leden['NAAM'], $lid['MEDICAL']);

            if(!$mail->Send()) {
                print_r($mail);
            }
        }
    }
}
