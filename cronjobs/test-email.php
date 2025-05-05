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
    Beste,
</p>
<p>
Dit is een test email van helios</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    Helios 
</p>
</body></html>";


heliosInit("Leden/GetObject?ID=1");

$result = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
else
{
    verstuurEmail();
}

function verstuurEmail()
{
    global $smtp_settings;
    global $htmlContent;

    $mail = emailInit();

    $mail->SMTPDebug  = 1;

    $mail->Subject = 'Test email ';
    $mail->isHTML(true);                                  		//Set email format to HTML
    $mail->Body    = $htmlContent;

    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

    $mail->addAddress("ict@gezc.org", "helios");

    if(!$mail->Send()) {
        print_r($mail);
    }

}
