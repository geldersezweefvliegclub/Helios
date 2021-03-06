<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;


/*
Haal gebruikers informatie op. Als er niet ingelogs is wordt basic authenticatie gebruikt
*/
$app->get('/Login/GetUserInfo', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Login");
    try
    {
        $datum = $request->getQueryParams()['DATUM'];

        $l = $obj->GetUserInfo($datum);  // Hier staat de logica voor deze functie
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
Heeft deze gebruiker toegang tot het systeem. Doordat eerder sessie is opgebouwd, of dat hij op een toegstane computer werky
*/
$app->get('/Login/Login', function (Request $request, Response $response, $args) {

    $token = $request->getQueryParams()['token'];

    try
    {
        /*
            Je denkt dat je onderstaande code moet uitvoeren om in te loggen, maar dat is niet zo
            In de index.php wordt gecontroleerd of je toegang hebt via de heeftToegang functie. Wanneer je daar ook login info 
            meegeeft, dan wordt er al ingelogd. Wanneer je onderstaande code uitvoert, log je dus 2x in. En dat is niet nodig
        

        $obj = MaakObject("Login");
        $obj->heeftToegang($token);  
        */
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
Het uitloggen van de gebruiker
*/
$app->get('/Login/Logout', function (Request $request, Response $response, $args) {
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