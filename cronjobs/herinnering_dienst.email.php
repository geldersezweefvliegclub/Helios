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
    We willen je graag informeren dat op <b>%s</b> voor jouw de dienst <b>%s</b> ingeroosterd is. 
</p>

<p>
    Het zou fijn zijn als je tijdig aanwezig kunt zijn, dus ruim voordat de dienst aanvangt. Zo zorgen we ervoor dat we een veilig en efficient vliegbedrijf kunnen organiseren.
</p>

<p>
    %s
</p>

<p>
    Mocht je verhinderd zijn dan vragen we je dringend om zelf een vervanger te regelen. Hierbij is de telefoon je beste vriend, een beller is immers sneller. Nadat je een vervanger gevonden hebt, stuur even een mail naar de roostercoordinator.  Hij zal dan het rooster aanpassen zodat er op de vliegdag duidelijk is wie er dienst heeft.
</p>

<p> 
    Alvast hartelijk dank voor de inzet en met vriendelijke groet,
</p>
<p> 
    De vliegers
</p>
</body></html>";

$schemaClub = "De ochtenddienst vangt aan om 8:30 en wordt, voor de startleider en lierist, om 14:00 overgedragen naar de middagploeg. <br><br>";
$schemaClub .= "Voor instructeurs is het volgende schema van toepassing:";
$schemaClub .= "<ul>";
$schemaClub .= "<li>Ochtend DDI: 08:30 – 16:00</li>";
$schemaClub .= "<li>Overlap (DBO): 10:30 – 18:00</li>";
$schemaClub .= "<li>Middag DDI: 14:00 – Einde</li>";
$schemaClub .= "</ul><br><br>";

$schemaDDWV = "DDWV dagen beginnen we om 9:00 en eindigt de dienst om 15:00";

$datum = Date('Y-m-d', strtotime('+3 days'));
$url_args = "DATUM=$datum";

heliosInit("Rooster/GetObject?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
$rooster = json_decode($body, true);
if (!$rooster['CLUB_BEDRIJF'] && !$rooster['DDWV']) 
    die;        // het is geen clubdag en geen DDWV dag

$schema = ($rooster['CLUB_BEDRIJF']) ? $schemaClub : $schemaDDWV;

heliosInit("Diensten/GetObjects?" . $url_args);

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
    $diensten = json_decode($body, true);
    if (count($diensten['dataset']) == 0)
    {
        // er zijn geen diensten ingevoerd
        die;
    }
    
    switch (date('w', strtotime('+3 days'))) {
        case 0; $datumString = "zondag "; break;
        case 1; $datumString = "maandag "; break;
        case 2; $datumString = "dinsdag "; break;
        case 3; $datumString = "woensdag "; break;
        case 4; $datumString = "donderdag "; break;
        case 5; $datumString = "vrijdag "; break;
        case 6; $datumString = "zaterdag "; break;
    }

    $datumString .= date('d-m-Y', strtotime('+3 days'));

    echo "Herinnering email gestuurd voor " . $datumString . "\n";
    foreach ($diensten['dataset'] as $dienst)
    {
        $url_args = "ID=" . $dienst['LID_ID'];
        heliosInit("Leden/GetObject?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $lid = json_decode($body, true);

        $mail = emailInit();
        $body = sprintf($htmlContent, $lid['VOORNAAM'], $datumString, $dienst['TYPE_DIENST'], $schema);
    
        $mail->Subject = 'Je dienst voor ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $body;
    
        $mail->addAddress($lid['EMAIL'], $lid['NAAM']); 

        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        echo $lid['EMAIL'] . "<br>";
        
        if($mail->Send()) {
            echo sprintf("%s: %s [%s]\n", $dienst['TYPE_DIENST'], $lid['NAAM'], $lid['EMAIL']);
        }
        else  {
            print_r($mail);
        }
    
    }
}
