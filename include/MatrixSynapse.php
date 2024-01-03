<?php

class synapse
{
    private static ?string $access_token = null;
    private static CurlHandle|bool $curl_session;
    private static array $mappingTabel = array();

    static private function initCurl($url, $http_method = "GET", $contentType = null): void
    {
        self::$curl_session = curl_init();
        curl_setopt(self::$curl_session, CURLOPT_TIMEOUT, 10);
        curl_setopt(self::$curl_session, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt(self::$curl_session, CURLOPT_URL, $url);
        curl_setopt(self::$curl_session, CURLOPT_CUSTOMREQUEST, $http_method);

        if ($_SESSION["MATRIX"])
            self::$access_token = $_SESSION["MATRIX"];

        if (!isset($contentType))
            $contentType = "application/json";

        if (isset(self::$access_token)) {
            $authorization = "Authorization: Bearer " . self::$access_token;
            curl_setopt(self::$curl_session, CURLOPT_HTTPHEADER, array('Content-Type: ' . $contentType, $authorization));
            Debug(__FILE__, __LINE__, sprintf("authorization = %s", $authorization));
        }
    }


    static private function login($username = null, $password = null)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, "login");

        if ((!isset($username)) || (!isset($password))) {
            $data = array(
                "type" => "m.login.password",
                "user" => $matrix_settings["user"],
                "password" => $matrix_settings["password"]
            );
        }
        else
        {
            $data = array(
                "type" => "m.login.password",
                "user" => $username,
                "password" => $password
            );
        }

        $loginUrl = $matrix_settings['url'] . "_matrix/client/r0/login";
        self::initCurl($loginUrl, "POST");
        curl_setopt(self::$curl_session, CURLOPT_POSTFIELDS, json_encode($data));
        $body = curl_exec(self::$curl_session);

        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            throw new Exception("500;Inloggen mislukt;" . $body);
        }

        $response = json_decode($body, true);
        self::$access_token = $response["access_token"];
        $_SESSION["MATRIX"] = $response["access_token"];
    }

    static public function bestaatGebruiker($id)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("bestaatGebruiker(%s)", $id));

        if (!isset($matrix_settings))
            return null;

        $url = sprintf("%s_synapse/admin/v2/users/@%s:%s", $matrix_settings['url'], strtolower($id), $matrix_settings["domein"]);
        self::initCurl($url);

        $body = curl_exec(self::$curl_session);

        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code
        if ($status_code != 200)
            return null;

        Debug(__FILE__, __LINE__, sprintf("gebruiker = %s", $body));
        return json_decode($body, true);
    }

    static public function updateGebruiker($lid, $password = null, $avatarUrl = null)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("updateGebruiker (%s,password,%s)", print_r($lid, true), $avatarUrl));
        if (!isset($matrix_settings))
            return;

        if ($lid["VERWIJDERD"]) {
            self::verwijderGebruiker($lid);
            return;             // na verwijderen kunnen we stoppen
        }

        $updateNodig = false;

        $matrixGebruiker = self::bestaatGebruiker(strtolower($lid["INLOGNAAM"]));
        $gebruikerBestaat = isset($matrixGebruiker);
        if (!$gebruikerBestaat)
            $avatarUrl = !isset($lid["AVATAR"]) ? null : self::uploadAvatar($lid["ID"], $lid["AVATAR"]);
        else {
            // Als de gebruiker bestaat, gaan we kijken of er een update nodig is
            $email = null;
            $mobiel = null;

            foreach ($matrixGebruiker["external_ids"] as $t)
            {
                if ($t["auth_provider"] == "mijn.gezc.org")
                {
                    $mID = $t["external_id"];
                    break;
                }
            }
            foreach ($matrixGebruiker["threepids"] as $t)
            {
                switch ($t["medium"])
                {
                    case "email" :$email = $t["address"]; break;
                    case "msisdn" : $mobiel = $t["address"]; break;
                }
            }

            if ($email !== $lid['EMAIL'])
                $updateNodig = true;
            else if ($mobiel !== $lid['MOBIEL'])
                $updateNodig = true;
            else if ($mID !== $lid['ID'])
                $updateNodig = true;
            else if ($matrixGebruiker['admin'] !== $lid['BEHEERDER'])
                $updateNodig = true;
            else if ($matrixGebruiker['displayname'] !== $lid["NAAM"])
                $updateNodig = true;
            else if ($matrixGebruiker['deactivated'] !== $lid['VERWIJDERD'])
                $updateNodig = true;
        }

        // moeten we matrix data aanpassen?
        Debug(__FILE__, __LINE__, sprintf("gebruikerBestaat=%s updateNodig=%s", $gebruikerBestaat ? "true" : "false", $updateNodig ? "true" : "false"));
        if (!$gebruikerBestaat || $updateNodig || isset($avatarUrl)) {
            if ((!isset(self::$access_token)) && (!isset($_COOKIE['MATRIX'])))
                self::login();

            $url = sprintf("%s_synapse/admin/v2/users/@%s:%s", $matrix_settings['url'], strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);

            $data = array(
                "displayname" => $lid["NAAM"],
                "external_ids" => array(
                    array(
                        "auth_provider" => "mijn.gezc.org",
                        "external_id" => $lid["ID"]
                    )
                ),
                "admin" => $lid['BEHEERDER'],
                "deactivated" => false,
                "user_type" => null,
                "locked" => false
            );

            $data["threepids"] = array();
            if (isset($lid["EMAIL"]))
                $data["threepids"][] = array(
                    "medium" => "email",
                    "address" => $lid["EMAIL"]
                );

            if (isset($password))
            {
                $data["password"] = $password;
                $data["logout_devices"] = false;
            }

            if (isset($avatarUrl))
                $data["avatar_url"] = $avatarUrl;

            Debug(__FILE__, __LINE__, sprintf("url=%s", $url));
            Debug(__FILE__, __LINE__, sprintf("data=%s", json_encode($data)));

            self::initCurl($url, "PUT");
            curl_setopt(self::$curl_session, CURLOPT_POSTFIELDS, json_encode($data));
            $body = curl_exec(self::$curl_session);

            $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

            if ($status_code != 200) {
                if ($status_code == 0)
                    Debug(__FILE__, __LINE__, sprintf("status=0 url=%s", $url));
                else
                    Debug(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
                throw new Exception("500;Update gebruiker mislukt;" . $body);
            }
            Debug(__FILE__, __LINE__, sprintf("updateGebruiker result = %s %s", $status_code, $body));
        }
    }

    static public function verwijderGebruiker($lid): void
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("verwijderGebruiker %s", (isset($matrix_settings)) ? "true" : "false"));
        if (!isset($matrix_settings))
            return;

        if (!isset(self::$access_token))
            self::login();

        $data = array("erase" => true);

        $url = sprintf("%s_synapse/admin/v1/deactivate/@%s:%s", $matrix_settings['url'], strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);
        self::initCurl($url, "POST");
        curl_setopt(self::$curl_session, CURLOPT_POSTFIELDS, json_encode($data));

        $body = curl_exec(self::$curl_session);
        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            Debug(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
            throw new Exception("500;Verwijder gebruiker mislukt;" . $body);
        }
        Debug(__FILE__, __LINE__, sprintf("verwijderGebruiker result = %s %s", $status_code, $body));
    }

    static public function uploadAvatar($id, $imgUrl): ?string
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("uploadAvatar %s %s", $id, $imgUrl));
        if (!isset($matrix_settings))
            return null;

        // ophalen avatar
        $ch = curl_init($imgUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, $imgUrl);

        $avatar = curl_exec($ch);

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); //get status code
        if ($status_code != 200) {
            Debug(__FILE__, __LINE__, sprintf("avatar niet beschikbaar status_code = %s", $status_code));
            return null;
        }
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);
        // done

        if (!isset(self::$access_token))
            self::login();

        $url = sprintf("%s_matrix/media/v3/upload?filename=%s", $matrix_settings['url'], $id);

        self::initCurl($url, "POST", $contentType);
        curl_setopt(self::$curl_session, CURLOPT_POSTFIELDS, $avatar);

        $body = curl_exec(self::$curl_session);

        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code
        Debug(__FILE__, __LINE__, sprintf("uploadAvatar result = %s %s", $status_code, $body));

        $retVal = json_decode($body, true);
        return $retVal['content_uri'];
    }

    /*
     Toevoegen aan de algemene kamer en de kamers die van toepassing zijn voor de functie
     */
    static public function toevoegenAanKamers($lid): void
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("toevoegenAanKamers %s", print_r($lid, true)));
        if (!isset($matrix_settings))
            return;

        if (!isset($matrix_settings["kamers"]))
            return;

        $matrixUserID = sprintf("@%s:%s", strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);

        self::kamerMapping();

        // Iedereen moet toegang hebben tot de algemene kamers
        self::toevoegen($matrix_settings["kamers"]["algemeen"], $matrixUserID);

        // nu per functie
        $functiesMappingArray = array(
            "LIERIST" => "Lierist",
            "LIERIST_IO" => "LIO",
            "STARTLEIDER" => "Startleider",
            "INSTRUCTEUR" => "Instructeur",
            "CIMT" => "CIMT",
            "DDWV_CREW" => "DDWV",
            "DDWV_BEHEERDER" => "DDWV",
            "BEHEERDER" => "Beheerder",
            "STARTTOREN" => "Starttoren",
            "ROOSTER" => "Rooster",
            "SLEEPVLIEGER" => "Sleepvlieger",
            "RAPPORTEUR" => "Rapporteur",
            "GASTENVLIEGER" => "Gastenvlieger",
            "TECHNICUS" => "Technicus"
        );

        // Als lid een specifieke rol heeft, dan toevoegen aan de bijbehoorende kamers
        foreach ($functiesMappingArray as $rol => $config)
        {
            if ($lid[$rol])
                self::toevoegen($matrix_settings["kamers"][$config], $matrixUserID);
        }
    }

    /*
     Zorg voor een mapping lijst tussen de naam van de kamer en het ID van de kamer
     */
    static private function kamerMapping()
    {
        global $matrix_settings;

        // ophalen kamers, we hebben straks de ID van de kamers nodig, in de configuratie staat de naam van de kamer en niet het ID
        $url = sprintf("%s_synapse/admin/v1/rooms", $matrix_settings['url'] );
        self::initCurl($url);

        $body = curl_exec(self::$curl_session);
        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            Debug(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
            throw new Exception("500;Kamers opvragen mislukt;" . $body);
        }

        $kamers = json_decode($body, true)["rooms"];

        // lookup tabel maken
        foreach ($kamers as $kamer)
            self::$mappingTabel[$kamer["name"]] = $kamer["room_id"];

        Debug(__FILE__, __LINE__, sprintf("mapping %s", print_r(self::$mappingTabel, true)));
    }

    /*
     Gebruiker toevoegen aan de kamers (uit de configuratie) als hij/zij nog niet in de kamer aanwezig is
     */
    static private function toevoegen($kamers, $userID): void
    {
        Debug(__FILE__, __LINE__, sprintf("toevoegen(%s, %s)", print_r($kamers, true), $userID));

        // toevoegen aan de kamers die voor iedereen bestemd zijn
        foreach ($kamers as $kamerNaam)
        {
            $roomID = self::$mappingTabel[$kamerNaam];

            if (!isset($roomID))
            {
                Debug(__FILE__, __LINE__, sprintf("ERROR: roomID null voor kamer %s", $kamerNaam));
                continue;
            }

            if (!self::isInKamer($roomID, $userID))
                self::toevoegenAanEnkeleKamer($roomID, $userID);
        }
    }

    /*
     Zit de gebruiker al in een kamer, roomID is interne ID van matrix
     */
    static private function isInKamer($roomID, $userID) : bool
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("isInKamer(%s, %s)", $roomID, $userID));

        $url = sprintf("%s_synapse/admin/v1/rooms/%s/members", $matrix_settings['url'], $roomID);
        self::initCurl($url);

        $body = curl_exec(self::$curl_session);

        $members = json_decode($body, true)["members"];
        $gevonden = false;

        foreach ($members as $member)
        {
            if ($member == $userID)
                return true;
        }
        Debug(__FILE__, __LINE__, "niet in kamer");
        return false;
    }

    /*
    Het echte toevoegen aan een kamer in Matrix
     */
    static private function toevoegenAanEnkeleKamer($roomID, $userID)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("toevoegenAanEnkeleKamer(%s, %s)", $roomID, $userID));

        // Matrix kan alleen invites uitsturen als je zelf in de kamer zit. We joinen dus eerst.
        $url = sprintf("%s_matrix/client/v3/join/%s", $matrix_settings['url'], $roomID);
        self::initCurl($url, "POST");
        $body = curl_exec(self::$curl_session);
        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code
        Debug(__FILE__, __LINE__, sprintf("join = %s %s", $status_code, $body));

        $url = sprintf("%s_synapse/admin/v1/join/%s", $matrix_settings['url'], $roomID);
        self::initCurl($url, "POST");
        curl_setopt(self::$curl_session, CURLOPT_POSTFIELDS, sprintf("{\"user_id\": \"%s\"}", $userID));
        $body = curl_exec(self::$curl_session);
        $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            Debug(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
            throw new Exception("500;Toevoegen aan kamer mislukt;" . $body);
        }

        Debug(__FILE__, __LINE__, sprintf("resultaat = %s %s", $status_code, $body));
    }

    /*
     sommige kamer zijn belangrijk en worden daarom als favoriet gemarkeerd
     */
    public static function markeerAlsFavoriet($lid, $password)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("markeerAlsFavoriet(%s)", print_r($lid)));
        if (!isset($matrix_settings))
            return;

        if (!isset($matrix_settings["favorieten"]))
            return;

        self::login(strtolower($lid["INLOGNAAM"]), $password);

        self::kamerMapping();
        $matrixUserID = sprintf("@%s:%s", strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);

        foreach ((array)$matrix_settings["favorieten"] as $kamerNaam) {
            $roomID = self::$mappingTabel[$kamerNaam];

            $url = sprintf("%s_matrix/client/v3/user/%s/rooms/%s/tags/m.favourite", $matrix_settings['url'], $matrixUserID, $roomID);
            self::initCurl($url, "PUT");
            $body = curl_exec(self::$curl_session);
            $status_code = curl_getinfo(self::$curl_session, CURLINFO_HTTP_CODE); //get status code

            if ($status_code != 200) {
                Debug(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
                throw new Exception("500;makeren als favoritiet mislukt;" . $body);
            }

            Debug(__FILE__, __LINE__, sprintf("resultaat = %s %s", $status_code, $body));
        }
    }
}