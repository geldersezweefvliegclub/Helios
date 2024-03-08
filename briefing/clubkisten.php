
<?php

include "../cronjobs/config.php";
include "../cronjobs/functions.php";

$vliegtuigenHTML = "";

$url_args = "CLUBKIST=true&VELDEN=CALLSIGN,INZETBAAR,OPMERKINGEN";
heliosInit("Vliegtuigen/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
}
else {

    $vliegtuigen = json_decode($body, true);
    if (count($vliegtuigen['dataset']) == 0) {
        // er zijn geen club vliegtuigen ingevoerd :-(
        $vliegtuigenHTML = "<h1>Er zijn geen vliegtuigen</h1>";
    } else {
        $vliegtuigenHTML = "<table>";
        $vliegtuigenHTML .= "<tr><td>&nbsp;</td></tr>";

        foreach ($vliegtuigen['dataset'] as $vliegtuig) {
            $inzetbaar = ($vliegtuig['INZETBAAR'] == true) ? "Inzetbaar" : "<div style='color:red;'><b>NIET</b> inzetbaar</div>";

            $vliegtuigenHTML .= "<tr>";
            $vliegtuigenHTML .= "<td style='padding-right:10px;'>" . $vliegtuig['CALLSIGN'] . "</td>";
            $vliegtuigenHTML .= "<td style='padding-right:10px;'>" . $inzetbaar . "</td>";
            $vliegtuigenHTML .= "<td>" . $vliegtuig['OPMERKINGEN'] . "</td>";
            $vliegtuigenHTML .= "</tr>";
        }

        $vliegtuigenHTML .= "</table>";
    }
}

$samenvattingHTML = "";

$url_args = "DATUM=" . date("Y-m-d");
heliosInit("AanwezigLeden/Samenvatting?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
}
else {

    $samenvatting = json_decode($body, true);

    if ($samenvatting['aanmeldingen'] == 0) {
        // er zijn geen club vliegtuigen ingevoerd :-(
        $samenvattingHTML = "<h1>Er zijn geen aanmeldingen</h1>";
    }
    else
    {
        $ledenHTML = sprintf("
        <table style='padding-right: 10px'>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Aanmeldingen<td></td>   <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>DBO<td></td>            <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>Solisten<td></td>       <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>Brevethouders<td></td>  <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Instructeurs<td></td>   <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>Startleiders<td></td>   <td style='padding-left: 10px;'>%s</td></tr>
            <tr><td>Lieristen<td></td>      <td style='padding-left: 10px;'>%s</td></tr>
            
        </table>",
            $samenvatting['aanmeldingen'],
            $samenvatting['dbo'],
            $samenvatting['solisten'],
            $samenvatting['brevethouders'],
            $samenvatting['instructeurs'],
            $samenvatting['startleiders'],
            $samenvatting['lieristen']);

        $samenvattingHTML = "<table><tr><td><b>Type</b></td><td style='padding-left: 15px'><b>Aantal</b></td></tr>";
        foreach ($samenvatting['types'] as $aanmelding)
        {
            $samenvattingHTML .= sprintf("<tr><td>%s</td><td style='padding-left: 15px;text-align: center'>%s</td></tr>", $aanmelding["type"], $aanmelding["aantal"]);
        }
        $samenvattingHTML .= "</table>";

        $samenvattingHTML .= "<h2>Overland</h2>";
        $samenvattingHTML .= "<table>";
        foreach ($samenvatting['overland'] as $aanmelding)
        {
            $samenvattingHTML .= sprintf("<tr><td>%s</td><td style='padding-left: 15px;text-align: center'>%s</td></tr>", $aanmelding["reg_call"], $aanmelding["naam"]);
        }
        $samenvattingHTML .= "</table>";
    }
}
?>

<html>
    <head>
        <style>
            body {
               font-family: Arial, Helvetica, sans-serif;
            }

            table {
                font-size:25px;
            }
        </style>
    </head>
    <body style="background-color: rgba(222,235,247,1)">
        <img src='logo.jpg' width="100%"></img>
        <table style="width: 100%">
            <tr>
                <td><div class='titel'><h1>Status club vliegtuigen</h1></div></td>
                <td  style="padding-left: 30px;"><div class='titel'><h1>Aanmeldingen</h1></div></td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 40%;">
                    <?php echo $vliegtuigenHTML; ?>
                </td>
                <td style="padding-left: 30px;vertical-align: top;width=60%;">
                    <table style="width:100%">
                        <tr style='vertical-align:top;'">
                            <td style="width: 50%;"> <?php echo $ledenHTML; ?></td>
                            <td style="width: 50%;">  <?php echo $samenvattingHTML; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
