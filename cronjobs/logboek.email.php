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
    In onze startadministratie staan op <b>%s</b> voor jou onderstaande vluchten genoteerd. Deze vluchten staan ook in je digitale logboek.

    We vragen aan je om te controleren of deze informatie correct en volledig is. Mocht dat niet het geval zijn, dan kan je via de website de vluchten aanpassen. Doe dit tijdig, na verloop van tijd is het niet meer mogelijk om zelf wijzigingen door te voeren.  
</p>

<table style='white-space: nowrap; margin-left: 10px; margin-right: 10px;font-family: Arial, Helvetica, sans-serif; font-size:12px;'>
    <thead>
        <tr>
            <th> Datum </th>
            <th> Vliegtuig </th>
            <th> Vliegveld </th>
            <th> Start methode </th>
            <th> Vlieger </th>
            <th> Inzittende </th>
            <th> Starttijd </th>
            <th> Landingstijd </th>
            <th> Duur </th>
            <th> Opmerkingen </th>
        </tr>
    </thead>
    %s
</table>
<a href='https://mijn.gezc.org/' 
    style='padding: 8px 12px;
        border: 1px solid #d6c84d;
        border-radius: 2px;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 14px;
        color: black; 
        text-decoration: none;
        font-weight: bold;
        display: inline-block;'>Open website</a>

<p>
    Het correct bijhouden van het logboek is een verantwoordelijkheid van de vlieger. Aanpassingen die je zelf invoert worden ter controle opgeslagen en kunnen gebruikt worden voor audits.
    </p>
    <p> 
    Indien je vragen hebt, mag je contact opnemen met startadmin@gezc.org. Doe dit door te reageren op deze email. We hebben dan meteen de juiste informatie beschikbaar. 
</p>
<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";

$htmlGeenMedical="
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
    Je hebt vandaag een vlucht gemaakt, echter ontbreekt de geldigheidsdatum van je medical in onze administratie. 
</p>
<p>     
    We verzoeken je vriendelijk om de geldigheidsdatum in je profiel in te vullen. Dat is handig omdat we tijdens het invoeren van een nieuwe start een controle uitvoeren of je een geldig medical hebt. Wanneer de geldigheidsdatum niet ingevoerd is, zal tot vragen leiden en daarmee het vliegbedrijf onnodig vertragen. 
</p>
<p> 
    Bedankt voor de medewerking en met vriendelijke groet,
</p>
<p>     
    De startadministratie  
</p>

</body></html>";

$htmlOnbevoegd="
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
    Je hebt vandaag een of meerdere vluchten gemaakt. In onze administratie ontbreekt de bevoegdheid om op %s te mogen vliegen.
</p>
<p>     
    De oorzaak kan zijn dat je vandaag je eerste start hebt gemaakt op het vliegtuig en dat de instructeur nog geen vinkje heeft gezet. Mocht het niet je eerste vlucht zijn dan 
    graag contact opnemen met een instructeur zodat het vinkje alsnog gezet kan worden.
</p>
<p>     
    We willen graag dat onze administratie op orde hebben en vandaar een vriendelijk verzoek om dit te regelen voordat je weer gaat vliegen. Bij het invoeren van een start wordt deze controle
    ook uitgevoerd, wat tot vragen / verwarring kan leiden op de strip. Met het mogelijke gevolg dat vliegbedrijf opgehouden kan worden.  
</p>
<p> 
    Bedankt voor de medewerking en met vriendelijke groet,
</p>
<p>     
    De startadministratie  
</p>
<p>     
    PS: Een kopie van deze mail is naar het CI-MT verstuurd  
</p>

</body></html>";


$datum = date('Y-m-d');
$url_args = "SORT=VLIEGER_ID,STARTTIJD&BEGIN_DATUM=$datum&EIND_DATUM=$datum&VELDEN=VLIEGER_ID,INZITTENDE_ID,STARTTIJD";
heliosInit("Startlijst/GetObjects?" . $url_args);

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
    $startlijst = json_decode($body, true);
    if (count($startlijst['dataset']) == 0)
    {
        // er zijn geen starts ingevoerd
        die;
    }

    // ophalen club vliegtuigen
    heliosInit("Vliegtuigen/GetObjects?CLUBKIST=true");

    $result      = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);
    $dbVliegtuigen = json_decode($body, true);
    $vliegtuigen = array();
    $alleBevoegdheden = array();                    // array met competenties ID
    foreach ($dbVliegtuigen['dataset'] as $v)
    {
        $vliegtuigen[$v['ID']] = $v;

        if (isset($v['BEVOEGDHEID_LOKAAL']))
            array_push($alleBevoegdheden, $v['BEVOEGDHEID_LOKAAL_ID']);

        if (isset($v['BEVOEGDHEID_OVERLAND']))
            array_push($alleBevoegdheden, $v['BEVOEGDHEID_OVERLAND_ID']);
    }

    // wie heeft er allemaal een start gemaakt
    $aanwezig = array();
    foreach ($startlijst['dataset'] as $start)
    {
        if ($start['STARTTIJD'] == null) continue;
        if ($start['VLIEGER_ID'] == null) continue;

        if (!in_array($start['VLIEGER_ID'], $aanwezig))
            array_push($aanwezig, $start['VLIEGER_ID']);
    
        if (($start['INZITTENDE_ID'] != null) && (!in_array($start['INZITTENDE_ID'], $aanwezig)))
            array_push($aanwezig, $start['INZITTENDE_ID']);
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

    foreach ($aanwezig as $lid)
    {
        // ophalen lid data
        $url_args = "ID=$lid";
        heliosInit("Leden/GetObject?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $lidData = json_decode($body, true);

        // ophalen progressie kaart van het lid
        $url_args = "LID_ID=" . $lidData['ID'] . "&IN=" . implode(",", $alleBevoegdheden);
        heliosInit("Progressie/GetObjects?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $dbProgressies = json_decode($body, true);
        $progressies= array();
        foreach ($dbProgressies['dataset'] as $p)
            array_push($progressies, $p['COMPETENTIE_ID']);

        // email
        $mail = emailInit();
        
        switch ($lidData['LIDTYPE_ID'])
        {
            case 600: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Diverse (Bijvoorbeeld bedrijven- of jongerendag)
            case 601: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Erelid
            case 602: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Lid
            case 603: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Jeugdlid
            case 604: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Private owner
            case 606: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Donateur
            case 607: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);                      // Zusterclub
                      $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); 
                      break; 
            case 608: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);                      // 5-rittenkaarthouder
                      $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); 
                      break; 
            case 609: $mail->addAddress($startadmin['EMAIL'], $startadmin['NAAM']);  break; // Nieuw lid, nog niet verwerkt in ledenadministratie
            case 610: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Oprotkabel
            case 611: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Cursist
            case 612: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Penningmeester
            case 613: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Systeem account
            case 625: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // DDWV vlieger
        }

        $url_args = "LID_ID=$lid&BEGIN_DATUM=$datum&EIND_DATUM=$datum&SORT=starttijd";
        heliosInit("Startlijst/GetLogboek?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $vluchten = json_decode($body, true);

        $logboekRegels = "";
        $zelfPIC = false;
        $onbevoegd = array();

        foreach ($vluchten['dataset'] as $vlucht)
        {
            $d = explode("-", $vlucht['DATUM']);

            if ($vlucht['INSTRUCTIEVLUCHT'] == false)
                $zelfPIC = true;

            if (isset($vliegtuigen[$vlucht['VLIEGTUIG_ID']])) {
                $lokaal = $vliegtuigen[$vlucht['VLIEGTUIG_ID']]['BEVOEGDHEID_LOKAAL_ID'];
                $overland = $vliegtuigen[$vlucht['VLIEGTUIG_ID']]['BEVOEGDHEID_OVERLAND_ID'];

                // zijn bevoegdheden van toepassing
                if (isset($lokaal) || isset($overland))
                {
                    $bevoegd = false;
                    if (isset($lokaal))
                        $bevoegd = in_array($lokaal, $progressies);

                    if ($bevoegd == false && isset($overland))
                        $bevoegd = in_array($overland, $progressies);

                    if ($bevoegd == false)
                        array_push($onbevoegd, $vlucht['VLIEGTUIG_ID']);
                }
            }

            $logboekRegels .= sprintf("
                <tr>
                    <td>%02d-%02d-%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>",
                $d[2]*1, $d[1]*1, $d[0],
                $vlucht['REG_CALL'],
                $vlucht['VELD'],
                $vlucht['STARTMETHODE'],
                $vlucht['VLIEGERNAAM'],
                $vlucht['INZITTENDENAAM'],
                $vlucht['STARTTIJD'],
                $vlucht['LANDINGSTIJD'],
                $vlucht['DUUR'],
                $vlucht['OPMERKINGEN']);
        }

        $body = sprintf($htmlContent, $lidData['VOORNAAM'], $datumString, $logboekRegels);
    
        $mail->Subject = 'Logboek ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $body;
    
        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        if(!$mail->Send()) {
            print_r($mail);
        }

        // check of medical verlopen is
        $protocol = (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) ? 'https://' : 'http://';
        $server = $_SERVER['SERVER_NAME'];
        $port = ($_SERVER['SERVER_PORT'] && ($protocol != 'https://')) ? ':'.$_SERVER['SERVER_PORT'] : '';

        file_get_contents($protocol.$server.$port . "/cronjobs/medical_verlopen.php?ID=" . $lidData['ID']);

        // stuur een mail als medical niet is ingevoerd
        if (($lidData['LIDTYPE_ID'] == 601 || $lidData['LIDTYPE_ID'] == 602 || $lidData['LIDTYPE_ID'] == 603) &&
            !isset($lidData['MEDICAL']) && $zelfPIC)
        {
            $mail->Subject = 'Medical';
            $mail->Body    = sprintf($htmlGeenMedical, $lidData['VOORNAAM']);

            if(!$mail->Send()) {
                print_r($mail);
            }

        }

        // check of er gevlogen is terwijl men niet bevoegd is
        if (count($onbevoegd) > 0)
        {
            $onbevoegdOp = array_unique($onbevoegd);

            $callsigns = "";
            foreach ($onbevoegdOp as $id)
            {
                $callsigns .= ($callsigns != "") ? ", "  : "";
                $callsigns .= $vliegtuigen[$id]['CALLSIGN'];
            }

            $mail->Subject = sprintf("Bevoegdheid %s %s", $callsigns, $lidData['NAAM']);
            $mail->Body    = sprintf($htmlOnbevoegd, $lidData['VOORNAAM'], $callsigns);
            $mail->addCC($cimt['EMAIL'], $cimt['NAAM']);

            if(!$mail->Send()) {
                print_r($mail);
            }
        }
    }
}
