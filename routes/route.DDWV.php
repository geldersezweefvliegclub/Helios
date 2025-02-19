<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;



/*
Haal een dataset op met records als een array uit de database. 
*/
$app->get(url_base() . 'DDWV/GetConfiguratie', function (Request $request, Response $response, $args) {
    $obj = MaakObject("DDWV");
    try
    {
        $c = $obj->GetConfiguratie();     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($c));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "DDWV/GetConfiguratie: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});



/*
De DDWV dag gaat niet door, strippen terug boeken
*/
$app->get(url_base() . 'DDWV/ToetsingDDWV', function (Request $request, Response $response, $args) {
    $obj = MaakObject("DDWV");
    try
    {
        $params = $request->getQueryParams();
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;
        $hash = (isset($params['HASH'])) ? $params['HASH'] : null;

        $r = $obj->ToetsingDDWV($datum, $hash);     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($r));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "DDWV/AnnulerenDDWV: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});

/*
De DDWV dag gaat niet door, strippen terug boeken
*/
$app->post(url_base() . 'DDWV/UitbetalenCrew', function (Request $request, Response $response, $args) {
    $obj = MaakObject("DDWV");
    try
    {
        $data = json_decode($request->getBody(), true);
        $r = $obj->UitbetalenCrew($data);     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($r));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/DDWV/UitbetalenCrew: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});

/*
Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd
*/
$app->post(url_base() . 'DDWV/MaakTransacties', function (Request $request, Response $response, $args) {
    $obj = MaakObject("DDWV");
    try
    {
        $data = json_decode($request->getBody(), true);

        $obj->MaakTransacties($data["DATUM"]);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode(null));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/DDWV/MaakTransacties: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});


?>
