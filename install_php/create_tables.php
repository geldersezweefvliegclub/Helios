<?php

chdir(".."); // terug naar de home directory

// Wachtwoord om te installeren
if(!file_exists('installer_account.php'))
{
    header('HTTP/1.0 401 Unauthorized');
    die();    
}


include('installer_account.php');
include('include/functions.php');
include('include/helios.php');
include('include/config.php');

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.0 400 Wrong request');
    die();
} 

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$key = sha1(strtolower ($username) . $password);

if (($username != $installer_account['username']) || ($key != $installer_account['password']))
{	
    header('HTTP/1.0 401 Unauthorized');
    die();    
}

$postdata = file_get_contents("php://input");
$db_tables = json_decode($postdata, true);

$l = MaakObject('Login');
$l->verkrijgToegang($username, $password); // nodig om sessie data te zetten


// aanmaken tabellen
$retValue['tabel'] = array();
$retValue['view'] = array();

$filldata = isBool($_GET['filldata'], "filldata");

$r=0;
while ($r < 150) // binnen 150 cycles moet het echt wel klaar zijn
{
    $r++; 
    for ($i = 0 ; $i < count($db_tables) ; $i++)
    {      
        $overslaan = false; // kunnen we deze tanel aanmaken, of moet we overslaan

        if ($db_tables[$i]['bestaat'] == false)
        { 
            //moeten eerst forgein keys tabellen aanmaken
            $table = $db_tables[$i]['class'];          
            $source_code = file_get_contents("lib/class." . $table . ".inc.php", "r");
            preg_match_all("/FOREIGN.*/", $source_code, $class_matches);

            Debug(__FILE__, __LINE__, sprintf("tabel:%s ", $table));
            if (count($class_matches) > 0)
            {
                foreach ($class_matches[0] as $fk_regel)
                {                   
                    $fk_words = explode(" ", preg_replace('/\s+/', ' ', $fk_regel));                   
                    $fk = trim(preg_replace('/\(.*/', ' ', $fk_words[4]));
 
                    if ($fk !== "%s")
                    {
                        if ($l->bestaatTabel($fk) == false)
                        {   
                            Debug(__FILE__, __LINE__, sprintf("tabel:%s FK=%s bestaat niet", $table ,$fk));
                            $overslaan = true;
                            break;
                        }
                    }
                }
            }

            if ($overslaan == false)
            {
                $obj = MaakObject($table);

                switch(strtoupper($table))
                {
                    case "TYPES" :
                    case "COMPETENTIES" :
                    {
                        $obj->CreateTable(true); 
                        break;
                    }
                    default: $obj->CreateTable($filldata); 
                }
                Debug(__FILE__, __LINE__, sprintf("tabel=%s AANGEMAAKT", $table));
                
                $db_tables[$i]['bestaat'] = true;
            }
        }
    }
    Debug(__FILE__, __LINE__, "--------------------");

    // kijk of we klaar zijn, zo ja die(), want we zitten in een erg lange while loop
    $klaar = true;
    foreach ($db_tables as $db_table)
    {
        Debug(__FILE__, __LINE__, sprintf("tabel=%s bestaat %s", $db_table['class'], ($db_table['bestaat'] == true) ? "WEL" : "niet"));
        if ($db_table['bestaat'] == false)
        {
            $klaar = false;
            break;
        }
    }
    Debug(__FILE__, __LINE__, "--------------------");

    if ($klaar)
    {
        $retValue = array(); 

        $heliosObjecten = scandir("lib");
        foreach ($heliosObjecten as $heliosObj)
        {
            if (strpos($heliosObj, '.php') !== false)
            {
                $source_code = file_get_contents("lib/" . $heliosObj, "r");
                
                preg_match("/class .*extends.*Helios/", $source_code, $class_matches);
                if (count($class_matches) > 0)
                {
                    preg_match("/this->dbTable.*=.*/", $source_code, $tabel_matches);
                    if (count($tabel_matches) > 0)
                    {
                        $heliosClass = explode(" ", $class_matches[0]);
        
                        $obj_info = array();
                        $obj_info['class'] = $heliosClass[1];
                       
                        $obj = MaakObject($heliosClass[1]);
                        $obj_info['bestaat'] = $obj->bestaatTabel();
                        array_push($retValue, $obj_info);
                    }
                }
            }
        }
        echo json_encode($retValue);

        die();
    }
}

?>