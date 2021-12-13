<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;


/*
Haal gebruikers informatie op. Als er niet ingelogs is wordt basic authenticatie gebruikt
*/
$app->get(url_base() . 'Login/GetUserInfo', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Login");
    try
    {
        $lidID = $obj->getUserFromSession();
        $l = $obj->GetUserInfo($lidID);  // Hier staat de logica voor deze functie
        if ($l === null)
        {
            header("X-Error-Message: Geen data", true, 404);
            header("Content-Type: text/plain"); 
            die;           
        }

        $response->getBody()->write(json_encode($l));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Login/GetUserInfo: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Verstuur SMS
*/
$app->get(url_base() . 'Login/SendSMS', function (Request $request, Response $response, $args) {

    $obj = MaakObject("Login");
    try
    {
        $l = $obj->sendSMS();  // Hier staat de logica voor deze functie
        return $response;
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Login/sendSMS: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Heeft deze gebruiker toegang tot het systeem. Doordat eerder sessie is opgebouwd, of dat hij op een toegstane computer werky
*/
$app->get(url_base() . 'Login/Login', function (Request $request, Response $response, $args) {

    $params = $request->getQueryParams();
    $token = (isset($params['token'])) ? $params['token'] : null;
    
    try
    {        
        $obj = MaakObject("Login");
        $BearerToken = $obj->verkrijgToegang(null, null, $token);

        $response->getBody()->write('{"TOKEN":"' . $BearerToken . '"}');
        
        return $response;
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Login/HeeftToegang: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Verlengen van de bearer token
*/
$app->get(url_base() . 'Login/Relogin', function (Request $request, Response $response, $args) {

    try
    {        
        $obj = MaakObject("Login");
        $BearerToken = $obj->verlengBearerToken();

        $response->getBody()->write('{"TOKEN":"' . $BearerToken . '"}');
        
        return $response;
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Login/Relogin: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Het uitloggen van de gebruiker
*/
$app->get(url_base() . 'Login/Logout', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Login");
    try
    {
        $obj->Logout();  // Hier staat de logica voor deze functie
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Login/Logout: " .$exception);
    }
    return $response;
});

?>