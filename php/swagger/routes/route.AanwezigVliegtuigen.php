<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/*
Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
*/
$app->post(url_base() . 'AanwezigVliegtuigen/CreateTable', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $params = $request->getQueryParams();
        $fill = (isset($params['FILLDATA'])) ? $params['FILLDATA'] : null;

        $obj->CreateTable($fill);   // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/CreateTable: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Maak database views, als view al bestaat wordt deze overschreven
*/
$app->post(url_base() . 'AanwezigVliegtuigen/CreateViews', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $obj->CreateViews();    // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/CreateViews: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Haal een enkel record op uit de database
*/
$app->get(url_base() . 'AanwezigVliegtuigen/GetObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;
        $lid_id = (isset($params['LID_ID'])) ? $params['LID_ID'] : null;
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;

        $d = $obj->GetObject($id, $lid_id, $datum);  // Hier staat de logica voor deze functie
        if ($d === null)
        {
            header("X-Error-Message: Geen data", true, 404);
            header("Content-Type: text/plain"); 
            die;           
        }

        $response->getBody()->write(json_encode($d));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/GetObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Haal een dataset op met records als een array uit de database. 
*/
$app->get(url_base() . 'AanwezigVliegtuigen/GetObjects', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $parameters = $request->getQueryParams();
        $v = $obj->GetObjects($parameters);     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/GetObjects: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});

/*
Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
Het veld VERWIJDERD wordt op "1" gezet.
*/
$app->delete(url_base() . 'AanwezigVliegtuigen/DeleteObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;
        $vliegtuig_id = (isset($params['VLIEGTUIG_ID'])) ? $params['VLIEGTUIG_ID'] : null;
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;

        $d = $obj->VerwijderObject($id, $vliegtuig_id, $datum);  // Hier staat de logica voor deze functie
        return $response->withStatus(intval(204));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/DeleteObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});  

/*
Haal een record terug dat verwijderd is . Het record was gelukkig niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
Het veld VERWIJDERD wordt terug op "0" gezet.
*/
$app->patch(url_base() . 'AanwezigVliegtuigen/RestoreObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;

        $record = $obj->HerstelObject($id);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(202));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/RestoreObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
}); 

/*
Aanamken van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
*/
$app->post(url_base() . 'AanwezigVliegtuigen/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $data = json_decode($request->getBody(), true);

        $al = $obj->AddObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($al));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/SaveObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Aanpassen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
*/
$app->put(url_base() . 'AanwezigVliegtuigen/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $data = json_decode($request->getBody(), true);

        $al = $obj->UpdateObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($al));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/SaveObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Aanmelden van het lid als aanwezig. Maakt record aan als het niet bestaat of update bestaand record (ook al is het lid aanwezig)
*/
$app->post(url_base() . 'AanwezigVliegtuigen/Aanmelden', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->Aanmelden($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/Aanmelden: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Afmelden van het lid als aanwezig. Update bestaand record. Lid moet aanwezig zijn
*/
$app->post(url_base() . 'AanwezigVliegtuigen/Afmelden', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigVliegtuigen");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->Afmelden($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigVliegtuigen/Afmelden: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

?>