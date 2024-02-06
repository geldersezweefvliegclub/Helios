<?php

class Boom 
{
    // Allerhoogste niveau om de progressie kaart te tonen
	// De hoofgroepen komen uit de types tabel
	public static function bouwBoom($dbdata)
	{
		$functie = "Boom.bouwBoom";
		Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($dbdata, true)));	

		$t = MaakObject('Types');
		$hoofdGroepen = $t->GetObjects(array("GROEP" => 10));

		$competentieBoom = array();

		foreach ($hoofdGroepen['dataset'] as $leerfase)
		{
			$children = self::bouwStam($leerfase["ID"], $dbdata['dataset']);
			$onderwerp = ($leerfase["CODE"] != null) ? sprintf("%s: %s", $leerfase['CODE'], $leerfase['OMSCHRIJVING']) : $leerfase['OMSCHRIJVING'];

			$c = new EnkeleCompetentie(
				$leerfase["ID"],             // leerfase ID uit types
				null,           // competentie ID
				null,               // progressie ID
				null,       			    // blok ID
				null,                      // blok
				$onderwerp,                     // onderwerp
				null,               // documentatie
				$children,                      // child data
				null,              // Datum behaald
				self::isBehaald(null, $children),    // is onderliggende competentie behaald
				null,				// afgetekend door
				null,                // opmerkingen
                null,                  // geldig tot
                null);                     // score
			
			array_push($competentieBoom, $c);
		}

		return $competentieBoom;
	}

	static function bouwStam($topID, $dataset)
	{
		$pKaart = array(); 

		foreach ($dataset as $competentie)
		{
			Debug(__FILE__, __LINE__, sprintf("%s - %s", $topID, print_r($competentie, true)));
			if (($competentie["LEERFASE_ID"] == $topID) && ($competentie["BLOK_ID"] == null))  
			{					
				Debug(__FILE__, __LINE__, sprintf("%s",$competentie['ONDERWERP']));
				$children = self::bouwTakken($competentie["ID"], $dataset);

				$c = new EnkeleCompetentie(
					$topID,							// Leerfase ID
					
					$competentie["ID"],             // competentie ID
					$competentie["PROGRESSIE_ID"],  // progressie ID
					$competentie["BLOK_ID"],        // blok ID
					$competentie["BLOK"],           // blok
					$competentie["ONDERWERP"],      // onderwerp
					$competentie["DOCUMENTATIE"],   // documentatie
					$children,                      // child data
					
					$competentie["INGEVOERD"],      // Datum behaald
					self::isBehaald($competentie["PROGRESSIE_ID"], $children),    // is onderliggende competentie behaald
					$competentie["INSTRUCTEUR_NAAM"],  // afgetekend door
					$competentie["OPMERKINGEN"],     // Opmerkingen bij het behalen
                    $competentie["GELDIG_TOT"],     // geldigheidsdatum
                    $competentie["SCORE"]);          // score 1 t/m 5
				
				array_push($pKaart, $c);
			}    
		}

		if (count($pKaart) == 0)
			return null;

		return $pKaart;
	}

	
	// Nu alle onderliggende niveaus. Wordt via een recursieve aanroep opgebouwd.
	private static function bouwTakken($ouderID, $dataset)
	{  
		$pKaart = array();  

		foreach ($dataset as $competentie)
		{   
			if ($competentie["BLOK_ID"] == $ouderID)
			{
				$children = self::bouwTakken($competentie["ID"], $dataset);

				$c = new EnkeleCompetentie(
					$competentie["LEERFASE_ID"],    // leerfase ID
					$competentie["ID"],             // competentie ID
					$competentie["PROGRESSIE_ID"],  // progressie ID
					$competentie["BLOK_ID"],        // blok ID
					$competentie["BLOK"],           // blok
					$competentie["ONDERWERP"],      // onderwerp
					$competentie["DOCUMENTATIE"],   // documentatie
					$children,                      // child data
					
					$competentie["INGEVOERD"],      // Datum behaald
					self::isBehaald($competentie["PROGRESSIE_ID"], $children),    // is onderliggende competentie behaald
					$competentie["INSTRUCTEUR_NAAM"],  // afgetekend door
                    $competentie["OPMERKINGEN"],     // Opmerkingen bij het behalen
                    $competentie["GELDIG_TOT"],      // geldigheidsdatum
                    $competentie["SCORE"]);          // score 1 t/m 5
				
				array_push($pKaart, $c);
			}    
		}

		if (count($pKaart) == 0)
			return null;

		return $pKaart;
	}  

	// Geeft 0/1/2 terug als alle compententies behaald zijn, of misschien maar een gedeelte
	static function isBehaald($progressieID, $kaarten)
	{
		if ($kaarten == null)
		{
			if ($progressieID != null)
				return 2;           // behaald want datum is ingevoerd
			else
				return 0;           // 0 = nee
		}

		$retValue = -1;             // -1 nog niet bepaald (is nooit een return waarde)

		for ($i=0 ; $i < count($kaarten) ; $i++)
		{
			switch ($kaarten[$i]->IS_BEHAALD)
			{
				case 0: // 0 = niet behaald
					{
						if ($retValue == 2)
							return 1;           // Vorige wel gehaald, deze niet = gedeeltelijk gehaald
						
						$retValue = 0;
						break;
					}
				case 1: // 1 = gedeeltelijk, onderliggende comptentie is gedeeltelijk gehaald
					{
						return 1; 
					}
				case 2: // 2= gehaald
					{
						if ($retValue == 0)
							return 1;           // Vorige niet gehaald, deze wel = gedeeltelijk gehaald
						
						$retValue = 2;
						break;
					}
			}
		}
		return $retValue;
	}	

}

class EnkeleCompetentie 
{
	public $LEERFASE_ID;
	public $COMPETENTIE_ID;
	public $BLOK_ID;
	public $BLOK;
	public $ONDERWERP;
	public $DOCUMENTATIE;
	public $OPMERKINGEN;

	public $PROGRESSIE_ID;
	public $IS_BEHAALD;              // 0 = nee, 1 = gedeeltelijk, 2 = ja
	public $INGEVOERD;
	public $INSTRUCTEUR_NAAM;

    public $GELDIG_TOT;
    public $SCORE;

	public $children;

	public function __construct(
			$leerfaseID, 
	
			$competentieID,
			$progressieID,
			$blokID,
			$blok,
			$onderwerp,
			$documentatie,
			$childData,

			$datumBehaald = null,
			$isBehaald = 0,
			$afgetekendDoor  = null,
			$opmerkingen = null,
            $geldig_tot = null,
            $score  = null
		)
	{
		$this->LEERFASE_ID = isset($leerfaseID) ? $leerfaseID *1 : null;
		$this->COMPETENTIE_ID = isset($competentieID) ? $competentieID *1 : null;
		$this->BLOK_ID = isset($blokID) ? $blokID *1 : null;
		$this->BLOK = $blok;
		$this->ONDERWERP = $onderwerp;
		$this->DOCUMENTATIE = $documentatie;
		$this->children = $childData;

		$this->PROGRESSIE_ID  = isset($progressieID) ? $progressieID *1 : null;
		$this->INGEVOERD = $datumBehaald;
		$this->IS_BEHAALD = $isBehaald;
		$this->INSTRUCTEUR_NAAM = $afgetekendDoor;
		$this->OPMERKINGEN = $opmerkingen;
        $this->GELDIG_TOT = $geldig_tot;
        $this->SCORE = isset($score) ? $score *1 : null;
	}
}
