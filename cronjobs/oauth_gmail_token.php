<?php
require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";

$redirect_uri =  (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];


$client = new Google_Client();
$client->setClientId($OAuthGoogle['clientId']);
$client->setClientSecret($OAuthGoogle['clientSecret']);
//$client->setRedirectUri((empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$client->setRedirectUri($redirect_uri);

$client->setAccessType('offline'); // Request refresh token
$client->setPrompt('consent');     // Always show consent
$client->addScope('https://mail.google.com/');

if (!isset($_GET['code'])) {
    // Step 1: No code, redirect to Google OAuth
    $authUrl = $client->createAuthUrl();
    echo "<a href='$authUrl'>Click here to authorize access to your Gmail</a>";
    exit;
} else {
    // Step 2: Callback from Google with ?code=
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        print_r($token);
        echo "Error retrieving token: " . htmlspecialchars($token['error_description']);
        exit;
    }

    echo "<h2>OAuth Token Results</h2>";
    echo "<p><strong>Access Token:</strong><br><code>{$token['access_token']}</code></p>";
    echo "<p><strong>Refresh Token:</strong><br><code>" .
        (isset($token['refresh_token']) ? $token['refresh_token'] : '<i>Not returned. Try again with consent prompt.</i>') .
        "</code></p>";
}
