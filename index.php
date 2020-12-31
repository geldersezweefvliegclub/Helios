<?php

include('include/config.php');
include('include/functions.php');
include('include/helios.php');
include('include/GoogleAuthenticator.php');

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$l = MaakObject('Login');
try
{
  $l->heeftToegang($_GET["token"]);			// het stopt hier als de gebruiker niet ingelogd is	
}
catch(Exception $exception)
{
    Debug(__FILE__, __LINE__, "heeftToegang: " .$exception);

    list($dummy, $exceptionMsg) = explode(": ", $exception);
    list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

    header("X-Error-Message: $message", true, intval($httpStatus));
    header("Content-Type: text/plain");
    die;
}  


require __DIR__ . '/ext/vendor/autoload.php';

$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
// Laat alle JSON/REST webservices
$files = glob("routes/route.*.php");
foreach($files as $file) {
//  Debug(__FILE__, __LINE__, sprintf("include %s", $file));
  include($file);
}
// Run app
$app->run();

?>