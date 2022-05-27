<?php

include "../cronjobs/config.php";
include "../cronjobs/functions.php";

$htmlContent = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<h1>Status club vliegtuigen</h1>

<table>
%s
</table>

</body></html>";


$url_args = "CLUBKIST=true&VELDEN=REG_CALL,INZETBAAR,OPMERKINGEN";
heliosInit("Vliegtuigen/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body    = "Vliegtuigen/GetObjects?" . $url_args . "\n";
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
    $vliegtuigen = json_decode($body, true);
    if (count($vliegtuigen['dataset']) == 0)
    {
        // er zijn geen club vliegtuigen ingevoerd :-(
        echo "<h1>Er zijn geen vliegtuigen</h1>";
        die;
    }
    
    $tabel = "";

    foreach ($vliegtuigen['dataset'] as $vliegtuig)
    {
        $inzetbaar = ($vliegtuig['INZETBAAR'] == true) ? "Inzetbaar" : "<div style='color:red;'><b>NIET</b> inzetbaar</div>";

        $tabel .= "<tr>";
        $tabel .= "<td style='padding-right:25px;'>" . $vliegtuig['REG_CALL'] . "</td>";
        $tabel .= "<td>" . $inzetbaar . "</td>";
        $tabel .= "</tr>";
    }

    echo sprintf($htmlContent, $tabel);
}