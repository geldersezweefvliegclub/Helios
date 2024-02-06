<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/*
Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
*/
$app->post(url_base() . 'Competenties/CreateTable', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $params = $request->getQueryParams();
        $fill = (isset($params['FILLDATA'])) ? $params['FILLDATA'] : null;

        $obj->CreateTable($fill);   // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/CreateTable: " .$exception);

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
$app->post(url_base() . 'Competenties/CreateViews', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $obj->CreateViews();    // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/CreateViews: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";",  $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

/*
Haal een enkel record op uit de database
*/
$app->get(url_base() . 'Competenties/GetObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;

        $t = $obj->GetObject($id);  // Hier staat de logica voor deze functie
        if ($t === null)
        {
            header("X-Error-Message: Geen data", true, 404);
            header("Content-Type: text/plain"); 
            die;           
        }

        $response->getBody()->write(json_encode($t));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/GetObject: " .$exception);

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
$app->get(url_base() . 'Competenties/GetObjects', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $parameters = $request->getQueryParams();
        $t = $obj->GetObjects($parameters); // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($t));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/GetObjects: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }
});

/*
Haal competenties op in een boomstructuur, dan heeft de client minder werk
*/
$app->get(url_base() . 'Competenties/CompetentiesBoom', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $parameters = $request->getQueryParams();
        $t = $obj->CompetentiesBoom($parameters); // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($t));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/CompetentiesBoom: " .$exception);

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
$app->delete(url_base() . 'Competenties/DeleteObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;
        $verificatie = (isset($params['VERIFICATIE'])) ? $params['VERIFICATIE'] : null;        

        $obj->VerwijderObject($id, $verificatie);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(204));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/DeleteObject: " .$exception);

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
$app->patch(url_base() . 'Competenties/RestoreObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;

        $record = $obj->HerstelObject($id);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(202));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/RestoreObject: " .$exception);

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
$app->post(url_base() . 'Competenties/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $data = json_decode($request->getBody(), true);

        $t = $obj->AddObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($t));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/SaveObject: " .$exception);

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
$app->put(url_base() . 'Competenties/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("Competenties");
    try
    {
        $data = json_decode($request->getBody(), true);

        $t = $obj->UpdateObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($t));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/Competenties/SaveObject: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});
?>