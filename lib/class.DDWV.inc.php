<?php
class DDWV {

    function GetConfiguratie() 
    {
        global $ddwv;
        return $ddwv;
    }


    function dagIsDDWV($datum) 
    {
        global $ddwv;

        $functie = "DDWV.dagIsDDWV";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, $datum));

        if ($ddwv->DDWV == false) {
            Debug(__FILE__, __LINE__, sprintf("%s: GEEN DDWV", $functie));
            return false;
        } 
        
        $d= isDATE($datum, "DATUM");

        // is de dag binnen het DDWV seizoen
        $dateparts = explode('-', $d);
        $dateValue = $dateparts[1] * 100 + $dateparts[2] *1;

        $datepartsS = explode('-', $ddwv->START);
        $dateValueS = $datepartsS[0] * 100 + $datepartsS[1] *1;

        $datepartsE = explode('-', $ddwv->EIND);
        $dateValueE = $datepartsE[0] * 100 + $datepartsE[1] *1;

        Debug(__FILE__, __LINE__, sprintf("%s: DDWV periode %d %d %d", $functie, $dateValue, $dateValueS, $dateValueE));
        if (($dateValue < $dateValueS) || ($dateValue > $dateValueE)) 
        {
            return false;
        }

        // is het een doordeweekse dag
        $weekday = DateTime::createFromFormat('Y-m-d', $d)->format('N');

        Debug(__FILE__, __LINE__, sprintf("%s: weekdag %d %s", $functie, $weekday, ($weekday <= 5) ? "true" : "false"));

        return ($weekday <= 5) ? true : false;
    }
}