<?php
require ("include/PasswordHash.php");

	class Login extends StartAdmin
	{
		private $_userID = null;			// Wie is er ingelogd
		
		public function __construct()
		{
			parent::__construct();
			
			if (session_status() == PHP_SESSION_NONE)
			{
				session_start(); 
				if(isset($_SESSION['login']))
				{
					$this->_userID = $_SESSION['login'];
				}			
				session_write_close();
			}			
		}
		
		function getUserFromSession()
		{
			if ($this->_userID != null)
				return $this->_userID;

			if (isset($_SESSION['login']))
				return $_SESSION['login'];

			return null;	
		}

		function Logout()
		{
			Debug(__FILE__, __LINE__, "Logout()");
			
			session_start(); 
			if (isset($_SESSION['login']))
				unset($_SESSION['login']);

			if (isset($_SESSION['userInfo']))
				unset($_SESSION['userInfo']);				

			if (isset($_SESSION['isInstaller']))
				unset($_SESSION['isInstaller']);

			$this->_userID = null;
			session_destroy();				
		}		
		
		function setSessionUser($id)
		{
			Debug(__FILE__, __LINE__, sprintf("setSessionUser(%s)", $id));
			$this->_userID = $id;

			if (session_status() == PHP_SESSION_NONE)
				session_start();

			$_SESSION['login']= $id;
			$_SESSION['userInfo'] = json_encode($this->getUserInfo());
			//session_write_close();
		}

		function getUserInfo($datum = null)
		{			
			$Userinfo = array();
			$Userinfo['magSchrijven'] = $this->magSchrijven();

			// initieele waarde, weten we zeker dat array gevuld is
			$Userinfo['isBeheerderDDWV'] = false;
			$Userinfo['isBeheerder'] = false;
			$Userinfo['isStartleider'] = false;
			$Userinfo['isInstructeur'] = false;

			$Userinfo['isCIMT'] = false;
			$Userinfo['isStarttoren'] = false;
			$Userinfo['isRooster'] = false;		

			$Userinfo['isClubVlieger'] = false;
			$Userinfo['isDDWV'] = false;
			$Userinfo['isAangemeld'] = false;

			$UserID = $this->getUserFromSession();
			$LidData = null;

			$l = MaakObject('Leden');
			//TODO			$a = MaakObject('Aanwezig');

			if ((is_numeric($UserID)) && (!$this->isInstaller()))
			{	
				try
				{
					$LidData = $l->getObject($UserID);
					$LidData['WACHTWOORD'] 	= "****";
				}
				catch(Exception $exception) {}

				$Userinfo['isBeheerderDDWV'] 	= $l->isPermissie("DDWV_BEHEERDER", $LidData['ID'], $LidData);
				$Userinfo['isBeheerder'] 		= $l->isPermissie("BEHEERDER", $LidData['ID'], $LidData);
				$Userinfo['isStartleider'] 		= $l->isStartleider($LidData['ID'], $LidData, $datum);
				$Userinfo['isInstructeur'] 		= $l->isPermissie("INSTRUCTEUR", $LidData['ID'], $LidData);

				$Userinfo['isCIMT'] 			= $l->isPermissie("CIMT", $LidData['ID'], $LidData);
				$Userinfo['isRooster'] 			= $l->isPermissie("ROOSTER", $LidData['ID'], $LidData);
				$Userinfo['isStarttoren'] 		= $l->isPermissie("STARTTOREN", $LidData['ID'], $LidData);

				$Userinfo['isClubVlieger'] 		= $l->isClubVlieger($LidData['ID'], $LidData);
				$Userinfo['isDDWV'] 			= $l->isDDWV($LidData['ID'], $LidData);
				$Userinfo['isAangemeld'] 		= false;
//TODO				$Userinfo['isAangemeld'] = $a->IsAangemeldVandaag($UserID);				
			}
			return array ("LidData" => $LidData, "Userinfo" => $Userinfo);
		}

		function heeftToegang($token = null)
		{
			global $NoPasswordIP;
		
			// Indien username en wachtwword gezet zijn, via basic authenticatie. Gaan we opnieuw authoriseren
			if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
			{ 					
				$this->verkrijgToegang ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $token);
				return;
			}

			$UserID = $this->getUserFromSession();

			if(isset($UserID))
			{
				Debug(__FILE__, __LINE__, sprintf("heeftToegang: UserID=%s ", $UserID));
				return;
			}
			
			if (!isset($NoPasswordIP))
			{
				Debug(__FILE__, __LINE__, sprintf("NoPasswordIP not set "));
			}
			else
			{
				if (is_array($NoPasswordIP))
					$ClientsWithoutPassword = $NoPasswordIP;
				else
					$ClientsWithoutPassword = array ($NoPasswordIP);
					
				foreach ($ClientsWithoutPassword as $client)
				{
					Debug(__FILE__, __LINE__, sprintf("heeftToegang: Allow=%s  RemoteIP=%s", $client, $_SERVER['REMOTE_ADDR']));
					
					$network = explode('/', $client);
					
					if (count($network) == 1)
					{
						if ($_SERVER['REMOTE_ADDR'] == $client)
						{
							$this->setSessionUser('strip');
							Debug(__FILE__, __LINE__, "strip");						
							return;
						}		
					}
					elseif (count($network) == 2)
					{
						if (CheckCIDR($_SERVER['REMOTE_ADDR'], $client))
						{
							$this->setSessionUser('strip');	
							Debug(__FILE__, __LINE__, "2/strip");	
							return;						
						}						
					}
					else
					{
						Debug(__FILE__, __LINE__, "Config error, NoPasswordIP");
						error_log("Config error, NoPasswordIP");
					}
				}
			}
			$this->toegangGeweigerd();		
		}
		
		function magSchrijven()
		{			
			Debug(__FILE__, __LINE__, sprintf("magSchrijven() UserID = %s", $this->getUserFromSession()));
								
			// Beheeders hebben altijd schrijf rechten
			if ($this->isBeheerder())
			{
				Debug(__FILE__, __LINE__, sprintf("%d is beheerder, return true", $this->getUserFromSession()));
				return true;
			}
		
			if ($this->isInstructeur())
				return true;
			
			Debug(__FILE__, __LINE__, sprintf("%d is gewone gebruiker, return false", $this->getUserFromSession()));			
			return false;
		}

		// Geef data over gebruiker
		function lidData()
		{
			// als er session niet gezet is, kunnen we niets
			if (isset($_SESSION['userInfo']) === false)
				return null;

			// ophalen van de gebruikers info als en decodeer JSON data
			$ui = json_decode($_SESSION['userInfo']);

			// als er userInfo niet gezet is, gaan we opnieuw voor de veilige oplossing
			if (property_exists($ui, 'LidData') === false)
				return null;
				
			return $ui->LidData;
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function sessiePermissie($key)
		{	
			// als er session niet gezet is, gaan we voor de veilige oplossing
			if (isset($_SESSION['userInfo']) === false)
				return false;

			// ophalen van de gebruikers info als en decodeer JSON data
			$ui = json_decode($_SESSION['userInfo']);

			// als er userInfo niet gezet is, gaan we opnieuw voor de veilige oplossing
			if (property_exists($ui, 'Userinfo') === false)
				return false;
			
			if (property_exists($ui->Userinfo, $key) === false)
				return false;	

			if (($ui->Userinfo->$key) || ($ui->Userinfo->$key == 1))
				return true;

			return false;
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isInstructeur()
		{	
			$key = 'isInstructeur';
			return $this->sessiePermissie($key);
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isCIMT()
		{	
			$key = 'isCIMT';
			return $this->sessiePermissie($key);
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isRooster()
		{	
			$key = 'isRooster';
			return $this->sessiePermissie($key);
		}	
		
		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isStarttoren()
		{	
			$key = 'isStarttoren';
			return $this->sessiePermissie($key);
		}			

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isBeheerder()
		{	
			$key = 'isBeheerder';
			return $this->sessiePermissie($key);

		}	
		
		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isBeheerderDDWV()
		{			
			$key = 'isBeheerderDDWV';
			return $this->sessiePermissie($key);
		}			

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isDDWV()
		{			
			$key = 'isDDWV';
			return $this->sessiePermissie($key);
		}

		function isInstaller()
		{				
			if (array_key_exists('isInstaller', $_SESSION) === false)
				return false;

			return $_SESSION['isInstaller'];
		}		
			
		function toegangGeweigerd()
		{
			throw new Exception("401;Toegang geweigerd;");
		}
		
		function verkrijgToegang($username=null, $password=null, $token=null)
		{		
			global $app_settings;
			global $installer_account;
			
			Debug(__FILE__, __LINE__, sprintf("verkrijgToegang(%s, %s, %s)", $username, "??", $token)); 
			
			if (($username == null) || ($password == null))
			{				
				if ((array_key_exists('USERNAME', $this->Data)) && (array_key_exists('PASSWORD', $this->Data)))
				{
					$username = $this->Data['USERNAME'];
					$password = $this->Data['PASSWORD'];
					
					Debug(__FILE__, __LINE__, sprintf("username = %s", $username));
				}
				else
				{
					Debug(__FILE__, __LINE__, sprintf("Toegang geweigerd, geen username bekend", $username));
					$this->toegangGeweigerd();
				}
			}

			if (isset($installer_account))
			{
				if (($username == $installer_account['username']) && (sha1($password) == $installer_account['password']))
				{	
					Debug(__FILE__, __LINE__, sprintf("helios installer account = true", $username));

					$this->setSessionUser($installer_account['id']);
					$_SESSION['isInstaller']= true;
					return;
				}
				Debug(__FILE__, __LINE__, "helios account = false");
			}
						
			$l = MaakObject('Leden');

			try 
			{ 
				$lObj = $l->GetObjectByLoginNaam($username); 
				Debug(__FILE__, __LINE__, sprintf("Login(%s) = %s, LIDTYPE_ID=%s", $username, $lObj["NAAM"], $lObj['LIDTYPE_ID']));
			}
			catch(Exception $exception) 
			{
				Debug(__FILE__, __LINE__, "Login: " .$exception);
				$this->toegangGeweigerd();	
			}

			if (($lObj['AUTH'] == "1") && (empty($token)))
				throw new Exception("401;Sleutel moet ingevoerd worden;");
				
			$key = sha1(strtolower ($username) . $password);
			Debug(__FILE__, __LINE__, sprintf("Login(%s)[%s] = %s, %s %s %s", $username, $lObj["AUTH"], $lObj["NAAM"], $lObj['WACHTWOORD'], $key, $lObj['SECRET']));
			if ($lObj['WACHTWOORD'] == $key)	
			{		
				Debug(__FILE__, __LINE__, sprintf("Toegang toegestaan (%s)", $username));	

				if ($lObj['AUTH'] == "1")		// 2 factor authenticatie
				{
					$ga = new PHPGangsta_GoogleAuthenticator();
					$checkResult = $ga->verifyCode($lObj['SECRET'], $token, 2);    // 2 = 2*30sec clock tolerance

					if ($checkResult) 
					{
						Debug(__FILE__, __LINE__, sprintf("2 Factor succesvol"));	
						$this->setSessionUser($lObj['ID']);	
						return;
					}
					else
					{
						Debug(__FILE__, __LINE__, sprintf("2 Factor gefaalt"));	
					}

				}
				else
				{
					$this->setSessionUser($lObj['ID']);	
					return;
				}
			}
			else if ($lObj['LIDTYPE_ID'] == "625")			// 625 = DDWV
			{
				$phpass = new PasswordHash(10, true);
				$ok= $phpass->CheckPassword($password, $lObj['WACHTWOORD']);
				
				if ($ok == true) 
				{
					Debug(__FILE__, __LINE__, sprintf("Toegang toegestaan DDWV (%s)", $username));	
					$this->setSessionUser($lObj['ID']);	
					return;							
				}
			}
			
			Debug(__FILE__, __LINE__, sprintf("Toegang geweigerd (%s)", $username));
			$this->toegangGeweigerd();				
		}	
	}
?>
