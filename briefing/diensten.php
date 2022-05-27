<?php

include "../cronjobs/config.php";
include "../cronjobs/functions.php";

$htmlContent = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<h1>Ingeroosterde diensten voor %s</h1>

<table>
%s
</table>

</body></html>";


$datum = date('Y-m-d');
$url_args = "DATUM=$datum";

heliosInit("Diensten/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body    = "Diensten/GetObjects?" . $url_args . "\n";
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
    $diensten = json_decode($body, true);
    if (count($diensten['dataset']) == 0)
    {
        // er zijn geen diensten ingevoerd
        echo "<h1>Er zijn geen diensten ingeroosterd</h1>";
        die;
    }
    
    switch (date('w')) {
        case 0; $datumString = "zondag "; break;
        case 1; $datumString = "maandag "; break;
        case 2; $datumString = "dinsdag "; break;
        case 3; $datumString = "woensdag "; break;
        case 4; $datumString = "donderdag "; break;
        case 5; $datumString = "vrijdag "; break;
        case 6; $datumString = "zaterdag "; break;
    }

    $datumString .= date('d-m-Y');
    $tabel = "";

    foreach ($diensten['dataset'] as $dienst)
    {
        $tabel .= "<tr>";
        $tabel .= "<td style='padding-right:25px;'>" . $dienst['NAAM'] . "</td>";
        $tabel .= "<td>" . $dienst['TYPE_DIENST'] . "</td>";
        $tabel .= "</tr>";
    }

    echo sprintf($htmlContent, $datumString, $tabel);
}