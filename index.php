<?php

    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
  }

  // Access-Control headers are received during OPTIONS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");         

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
  }

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