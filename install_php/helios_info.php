<?php

chdir(".."); // terug naar de home directory

include('include/functions.php');
include('include/helios.php');

$retValue = array();

$retValue['db_info'] = InitGedaan();
$retValue['installer_account'] = file_exists("installer_account.php");

echo json_encode($retValue);
die();

$retValue['Objecten'] = array(); 

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
                array_push($retValue['Objecten'], $obj_info);
            }
        }
    }
}

