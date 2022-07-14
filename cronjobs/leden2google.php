<?php

// Voor uitleg zie https://developers.google.com/people/quickstart/php

use Google\Service\PeopleService\Address;

require __DIR__ . '/google/vendor/autoload.php';

include "config.php";
include "functions.php";

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Leden naar Google contacts');
    $client->setScopes(Google_Service_PeopleService::CONTACTS);
    $client->setAuthConfig('google/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'google/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_PeopleService($client);

// Print the names for up to 10 connections.
$optParams = array(
    'personFields' => 'addresses,ageRanges,biographies,birthdays,calendarUrls,clientData,coverPhotos,emailAddresses,events,externalIds,genders,imClients,interests,locales,locations,memberships,metadata,miscKeywords,names,nicknames,occupations,organizations,phoneNumbers,photos,relations,sipAddresses,skills,urls,userDefined'
);
$contacts = $service->people_connections->listPeopleConnections('people/me', $optParams);

if (count($contacts->getConnections()) == 0) {
    print "No connections found.\n";
} else {
    print "People:\n";
    foreach ($contacts->getConnections() as $person) {
        print_r($person);
        if (count($person->getNames()) == 0) {
            print "No names found for this connection\n";
        } else {
            $names = $person->getNames();
            $name = $names[0];
            printf("%s\n", $name->getDisplayName());
        }
    }
}


// GeZC code

// ophalen wie er allemaal een email ontvangen. Dat zijn alleen instructeurs en beheerders
heliosInit("Leden/GetObjects?TYPES=601,602,603,606,625");
$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body    = "Leden/GetObjects?TYPES='601, 602,603, 606, 625'" . "\n";
    $mail->Body   .= "HEADER :\n";
    $mail->Body   .= print_r(curl_getinfo($curl_session), true);
    $mail->Body   .= "\n";
    $mail->Body   .= "BODY :\n" . $body;

    $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    if(!$mail->Send()) {
        print_r($mail);
    }
    die;  
}
$leden = json_decode($body, true);
foreach ($leden['dataset']as $lid) {

    $contactToCreate = new Google\Service\PeopleService\Person();

    $adres = new Google\Service\PeopleService\Address();
    $adres->setStreetAddress($lid['ADRES']);
    $adres->setPostalCode($lid['POSTCODE']);
    $adres->setCity($lid['WOONPLAATS']);

    
    break;
}

