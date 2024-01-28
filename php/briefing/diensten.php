
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

$htmlContent = "<div class='titel'><h1>Ingeroosterde diensten voor %s</h1></div><div><table>%s</table></div>";

$datum = date('Y-m-d');
$url_args = "DATUM=$datum";

heliosInit("Diensten/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}

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
    $tabel .= "<tr  class='inhoud'>";
    $tabel .= "<td style='padding-right:25px;'>" . $dienst['NAAM'] . "</td>";
    $tabel .= "<td>" . $dienst['TYPE_DIENST'] . "</td>";
    $tabel .= "</tr>";
}

echo sprintf($htmlContent, $datumString, $tabel);


?>

            </div>
        </div>
    </body>
</html>
