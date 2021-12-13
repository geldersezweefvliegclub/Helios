<?php

// Gebruik het slim framework
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/*
Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
*/
$app->post(url_base() . 'AanwezigLeden/CreateTable', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $params = $request->getQueryParams();
        $fill = (isset($params['FILLDATA'])) ? $params['FILLDATA'] : null;

        $obj->CreateTable($fill);   // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/CreateTable: " .$exception);

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
$app->post(url_base() . 'AanwezigLeden/CreateViews', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $obj->CreateViews();    // Hier staat de logica voor deze functie
        return $response->withStatus(intval(201));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/CreateViews: " .$exception);

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
$app->get(url_base() . 'AanwezigLeden/GetObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;
        $lid_id = (isset($params['LID_ID'])) ? $params['LID_ID'] : null;        

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
        Debug(__FILE__, __LINE__, "/AanwezigLeden/GetObject: " .$exception);

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
$app->get(url_base() . 'AanwezigLeden/GetObjects', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $parameters = $request->getQueryParams();
        $v = $obj->GetObjects($parameters);     // Hier staat de logica voor deze functie

        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/GetObjects: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);   // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        exit;
    }
});

/*
Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
Het veld VERWIJDERD wordt op "1" gezet.
*/
$app->delete(url_base() . 'AanwezigLeden/DeleteObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;
        $lidID = (isset($params['LID_ID'])) ? $params['LID_ID'] : null;
        $verificatie = (isset($params['VERIFICATIE'])) ? $params['VERIFICATIE'] : null;
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;

        $obj->VerwijderObject($id, $lidID, $datum, $verificatie);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(204));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/DeleteObject: " .$exception);

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
$app->patch(url_base() . 'AanwezigLeden/RestoreObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $params = $request->getQueryParams();
        $id = (isset($params['ID'])) ? $params['ID'] : null;

        $record = $obj->HerstelObject($id);     // Hier staat de logica voor deze functie
        return $response->withStatus(intval(202));
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/RestoreObject: " .$exception);

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
$app->post(url_base() . 'AanwezigLeden/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $al = $obj->AddObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($al));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/SaveObject: " .$exception);

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
$app->put(url_base() . 'AanwezigLeden/SaveObject', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $al = $obj->UpdateObject($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($al));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/SaveObject: " .$exception);

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
$app->post(url_base() . 'AanwezigLeden/Aanmelden', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->Aanmelden($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/Aanmelden: " .$exception);

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
$app->post(url_base() . 'AanwezigLeden/Afmelden', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $data = json_decode($request->getBody(), true);

        $v = $obj->Afmelden($data);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($v));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/Afmelden: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});


/* 
Welke potentiele vligers hebben we voor dit vliegtuig 
*/
$app->get(url_base() . 'AanwezigLeden/PotentieelVliegers', function (Request $request, Response $response, $args) {
    $obj = MaakObject("AanwezigLeden");
    try
    {
        $params = $request->getQueryParams();
        $vliegtuigID = (isset($params['VLIEGTUIG_ID'])) ? $params['VLIEGTUIG_ID'] : null;
        $datum = (isset($params['DATUM'])) ? $params['DATUM'] : null;        

        $vliegers = $obj->PotentieelVliegers($vliegtuigID, $datum);   // Hier staat de logica voor deze functie
        $response->getBody()->write(json_encode($vliegers));
        return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $exception)
    {
        Debug(__FILE__, __LINE__, "/AanwezigLeden/PotentieelVliegers: " .$exception);

        list($dummy, $exceptionMsg) = explode(": ", $exception);
        list($httpStatus, $message) = explode(";", $exceptionMsg);  // onze eigen formaat van een exceptie

        header("X-Error-Message: $message", true, intval($httpStatus));
        header("Content-Type: text/plain");
        die;
    }  
});

?>