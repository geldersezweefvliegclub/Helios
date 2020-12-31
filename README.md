# Helios

## Table of contents
- [Helios](#helios)
  - [Table of contents](#table-of-contents)
  - [General info](#general-info)
  - [Technologie](#technologie)
  - [Introductie](#introductie)
  - [Setup](#setup)
  - [Documentatie](#documentatie)
  - [Gebruik](#gebruik)
  - [Configuratie](#configuratie)

## General info
This project is implementing the backend processing for a Declared Training Organization for Soaring clubs. If you are a not a Dutch speaking, 
please contact us by email. The remainder of this document will be in the Dutch language.
	
## Technologie
Project is gemaakt met:
* php 7.2
* Slim framework 4.3 / slim/psr7 (See ext directory)
* Swagger (see swagger directory)
* MariaDB 10.5.8

## Introductie
Helios (de zonnegod) is backend server waar data opgeslagen wordt in een database. Via een Rest API kan informatie uitgewisseld worden. Dit kan een website zijn, een app voor een mobiele telefoon, een ander computer programma of iets anders. Helios is dus geen applicatie voor eind gebruikers. De bedoeling is dat iedereen zijn eigen applicatie kan maken bovenop Helios. Alle applicatie delen dezelfde data en logica. Hiermee onstaat consistentie in het beheer van de data.

Het grote voordeel hiervan is dat iedere club zijn eigen frontend kan bouwen en zijn eigen integratie met andere software. De informatie die wordt opgeslagen wordt:
- Leden bestand
- Vliegtuigen
- Dag rapportage
- Start administratie
- Progressie 
- Leerling volg systeem (Tracks)
- Rooster

Belangrijk aspect bij de ontwikkeling van Helios zijn de privacy aspecten. Dit zijn:
- 2 Factor authenticatie via Google Authenticator (verplicht voor kritische rollen)
- Afscherming van wachtwoorden
- Iedere gebruiker kan privancy mode inschakelen waarbij privancy gevoelige informatie (denk aan adres gegevens) worden afgeschermd
- Normale leden hebben alleen toegang tot hun eigen data
  
We kennen de volgende rollen  
- Leden
- DDWV'er (Door De Week Vliegen)
- Instructeurs
- CIMT
- Lieristen
- Startleiders
- Roostermaker
- DDWV beheerder
- Beheerder

## Setup
Installeren van deze software in niet noodzakelijk. In principe is alle software beschikbaar in de GitHub download.

Belangrijk is dat er een werkende omgeving met 
- Web server
- PHP kan uitgevoerd worden. Installeer de yaml extentie 
- MariaDB of MySQL database engine beschikbaar

Controlleer de primaire werking van de website en php door de url te openen: http://mijn.helios.org/dummy.php  (mijn.helios.org is uw domein). Indien dit werkt kunt u verder met de volgende stappen. 

Zorg dat alle requests worden omgeleid naar index.php. Dit kan via de site.config of via .htaccess

Voorbeeld voor nginx (let op, de configuratie is erg afhankelijk van andere omstandigheden en dient alleen als voorbeeld)
```
server {
    listen 80;
    listen [::]:80;

    index index.php;
    server_name helios.local;
    root /app/helios;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Voorbeeld voor .htdocs in de home directory van de website 
```
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
</IfModule>
```

Voor de volgende stap heeft u informatie van de database nodig om toegang te krijgen
- database server
- inlognaam op de server
- wachtwoord van deze gebruiker
- naam van de database, of bedenk een naam als deze nog niet bekend is

Er zijn nu twee opties:
- U heeft al een database aangemaakt 
- U heeft beheer rechten op de database server, maar er is nog geen database

Het wordt nu tijd om de database te creeren als dat nog niet gedaan is, en de database tabellen aan te maken. Dit is een voudig te realiseren door
http://mijn.helios.org/install te open in de browser. Volg de stappen in de wizard. Voor een eerste gebruikt is het aan te raden om voorbeeld data te installeren.
De voorbeeld data geeft inzicht in het gebruik en wordt gebruikt voor testen. 

LET OP: Deze installer werkt maar 1 keer, zorg dat de configuratie correct wordt ingevoerd. 

Als de configuratie wizard afgerond is dan is het volgende gebeurd
- de $db_info configuratie file is gevuld in /include/config.php
- installer_account.php, om onderhoud te kunnen doen op de database

De database heeft de volgende tabellen
- oper_aanwezig_leden	  	
- oper_aanwezig_vliegtuigen-
- oper_daginfo
- oper_progressie
- oper_rooster
- oper_startlijst
- oper_tracks	
- ref_competenties
- ref_leden
- ref_types
- ref_vliegtuigen

De tabellen met referentie data beginnen met ref_ , de data met operatationele data begint met oper_

## Documentatie
Een belangrijke voorwaarde om Helios te gebruiken is dat alle communicatie via de Rest API geschied. Voor Helios maken we gebruiken we OpenAPI Specification v3 specificatie. De intreface beschrijving is opgeslagen in yml file in de direcory /docs. Ieder object in Helios is op deze wijze beschreven. Er zijn vele programmeertalen die deze yml bestanden kunnen gebruiken om source code te generen. Helaas is er geen code generator beschikbaar voor PHP.

Om deze interface beter te kunnen gebruiken, maken we gebruik van swagger (https://swagger.io) De SwaggerUI is ook opgenomen in dit project. Ga naar http://mijn.helios.org/docs om de interface te openen. Deze SwaggerUI heeft wel de php yaml extensie nodig. Vanuit SwaggerUI kunnen ook de API getest worden. Wanneer er zich problemen voordoen met de SwaggerUI in Helios, kunnen de yml files ook geopend worden met open source tools (https://github.com/swagger-api/swagger-editor) of via een online tool (https://petstore.swagger.io)


## Gebruik
Bijhet initialiseren van de database kan gebruik gemaakt worden om database te vullen met data. Voor operationele system moet de test data verwijderd worden. De tabel "ref_types" is essientieel voor het gebruik. De velden met READ_ONLY == 1, mogen NIET verwijderd worden.

Voordat er gebruik gemaakt kan worden van de web services moet de gebruiker geautoriseerd worden. Dit kan via basic authenticatie bij iedere aanroep, of om eerst in te loggen. Dat gaat via http://mijn.helios.org/Login/GetUserInfo met basic authenticatie. De PHP sessie cookie kan daarna gebruikt worden om andere aanroepen te authenticeren. Wanneer gebruikt gemaakt wordt van 2 factor authenticatie, moet het token (bepaald door Google Authenticator op smartphone) als token parameter worden meegegeven. De aanroep wordt dan http://mijn.helios.org/Login/GetUserInfo?token=12345678. De werkwijze met de eerste aanroep GetUserInfo heeft de voorkeur omdat de inloggegevens dan niet in ieder aanroep opgenomen zijn.

Data die eenmaal opgeslagen is in de database wordt NOOIT fysiek verwijderd. Hiermee bestaat dus ook de mogelijkheid om weggegooide informatie weer te herstellen. Via het veld VERWIJDERD kan bepaald worden of de data verwijderd is.

Ieder record heeft een veld LAATSTE_AANPASSING. Dit is een tijdstempel wanneer de data voor de laaste keer gewijzigd is. Ook het het initeel aanmaken van een record wordt het tijdstempel gezet. Dit is een database functie en kan niet van buitenaf beinvloed worden.

Bij het opvragen van data via GetObjects worden 3 elementen aangeleverd door de API. De velden "totaal", "laatste_aanpassing" en "dataset".  
- totaal, aantal records dat voldoet aan de query. Dat kan dus meer zijn dan aantal records in dataset. Bijvoorbeeld waneer we aantal records maximeren met de MAX parameter. Stel http://mijn.helios.org/Leden/GetObjects?MAX=20  Het aantal records in dataset in dan maximaal 20, terwijl totaal 80 kan bevattem omdat er 80 leden zijn.
- laatste_aanpassing, het tijdstempel van de laatste aanpassing in de uitgevoerde query (kan dus afwijken van laaste_aanpassing in dataset, zie bovenstaande logica)
- dataset, de werkelijke data

Om een beeld te krijgen hoe Helios werkt, kan er gebruik gemaakt worden van postman (https://www.postman.com) Postman is gebruikt voor automatische testen en geven een prima inzicht hoe e.e.a. werkt. /postman/Helios.postman_collection.json kan geimporteerd worden om alle testen beschikbaar te krijgen. 


## Configuratie
De Helios configuratie is samngevat in /include/config. 

```
$db_info = array(
	'dbType' => 'mysql',
	'dbHost' => 'mariadb',
	'dbName' => 'helios', 
	'dbUser' => 'root',
	'dbPassword' => 'rootroot'
);
```

Dit zijn de parameter die ingevoerd zijn, tijdens initeele configuratie. Deze configuratie kan handmatig aangepast worden.

```
$app_settings = array(
	'DbLogging' => false,			// Log database queries naar logfile
	'DbError' => false,				// Log errors naar logfile
	'Debug' => false,				// Debug informatie naar logfile, uitzetten voor productie
	'LogDir' => '/tmp/log/helios/',	// Locatie waar log bestanden geschreven worden
	'Vereniging' => "GeZC"	
);
```
Om een analyse te kunnen doen van Helios logica, kunnen de logging parameters op true gezet worden. De logfiles worden dan naar de LogDir geschreven. Ander alternatief is LogDir => 'syslog' De loggings gaan dan naar de sys log deamon. Bedenk dat logfiles erg snel groeien. DbLogging en Debug moeten voor productie systemen ALTIJD op false staan.

