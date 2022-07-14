<html>
    <head>
        <style>
            .centreer {
                width:100%;
                display: flex;
                justify-content: center;
            }

            .titel {
                font-family: Arial, Helvetica, sans-serif; 
                font-size:20px;
            }

            .inhoud {
                font-family: Arial, Helvetica, sans-serif; 
                font-size:30px;
            }
        </style>
    </head>
    <body style="background-color: rgba(222,235,247,1)">
        <img src='logo.jpg' width="100%"></img>
        <div class='centreer'>
            <div>

<?php

include "../cronjobs/config.php";
include "../cronjobs/functions.php";

$htmlContent = "<div class='titel'><h1>Status club vliegtuigen</h1></div><div><table>%s</table></div>";

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

        $tabel .= "<tr  class='inhoud'>";
        $tabel .= "<td style='padding-right:25px;'>" . $vliegtuig['REG_CALL'] . "</td>";
        $tabel .= "<td style='padding-right:25px;'>" . $inzetbaar . "</td>";
        $tabel .= "<td>" . $vliegtuig['OPMERKINGEN'] . "</td>";
        $tabel .= "</tr>";
    }

    echo sprintf($htmlContent, $tabel);
}


?>

            </div>
        </div>
    </body>
</html>