<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/*
Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
*/
$app->post('/Leden/CreateTable', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $fill = $request->getQueryParams()['FILLDATA'];

        $obj->CreateTable($fill);   // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/CreateTable: " .$exception);

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
$app->post('/Leden/CreateViews', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $obj->CreateViews();    // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/CreateViews: " .$exception);

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
$app->get('/Leden/GetObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $id = $request->getQueryParams()['ID'];

        $l = $obj->GetObject($id);  // Hier staat de logica voor deze functie
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
        Debug(__FILE__, __LINE__, "/Leden/GetObject: " .$exception);

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
$app->get('/Leden/GetObjects', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $parameters = $request->getQueryParams();
        $v = $obj->GetObjects($parameters);     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/GetObjects: " .$exception);

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
$app->delete('/Leden/DeleteObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $id = $request->getQueryParams()['ID'];
        $verificatie = $request->getQueryParams()['VERIFICATIE'];

        $obj->VerwijderObject($id, $verificatie);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(204));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/DeleteObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});  

/*
Aanmaken van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
*/
$app->post('/Leden/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->AddObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/SaveObject: " .$exception);

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
$app->put('/Leden/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->UpdateObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/SaveObject: " .$exception);

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
$app->post('/Leden/UploadAvatar', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Leden");
    try
    {
        $params = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $avatar_url = $obj->UploadAvatar($params['ID'], $uploadedFiles['FILE']);    // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($avatar_url));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Leden/UploadAvatar: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

?>