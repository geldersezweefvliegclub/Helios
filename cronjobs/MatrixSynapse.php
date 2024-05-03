<?php


class synapse
{
    private static array $mappingTabel = array();

    static private function initCurl($url, $http_method = "GET", $contentType = null, $token = null)
    {
        $curl_session = curl_init($url);

        if ($curl_session === false) {
            Debug(__FILE__, __LINE__, sprintf("ERROR: curl_init failed for %s", $url));
            return null;
        }

        curl_setopt($curl_session, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_session, CURLOPT_CUSTOMREQUEST, $http_method);

        if (!isset($contentType))
            $contentType = "application/json";

        if (!isset($token) && isset($_SESSION["MATRIX"]))
            $token = $_SESSION["MATRIX"];

        if (isset($token)) {
            $authorization = "Authorization: Bearer " . $token;
            curl_setopt($curl_session, CURLOPT_HTTPHEADER, array('Content-Type: ' . $contentType, $authorization));
            Debug(__FILE__, __LINE__, sprintf("authorization = %s", $authorization));
        }
        return $curl_session;
    }

    // zie https://matrix-org.github.io/synapse/latest/admin_api/user_admin_api.html#login-as-a-user
    static private function login($username = null, $password = null): string
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("login(%s, %s)", $username, isset($password) ? "true" : "false"));

        if (((!isset($username)) || (!isset($password))) && file_exists("matrix-access_token.json")) {
            $tokens = json_decode(file_get_contents("matrix-access_token.json"), true);

            if (!isset($tokens))
            {
                self::deleteToken();
            }
            else
            {
                if (time() < $tokens["expires_in"])
                    return $tokens["access_token"];       // token is nog geldig

                // refresh van het token
                $reloginUrl = $matrix_settings['url'] . "_matrix/client/r0/refresh";
                $curl_session = self::initCurl($reloginUrl, "POST");
                curl_setopt($curl_session, CURLOPT_POSTFIELDS, sprintf("{\"refresh_token\":\"%s\"}", $tokens["refresh_token"]));
                $body = curl_exec($curl_session);

                $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

                if ($status_code != 200) {
                    Error(__FILE__, __LINE__, sprintf("Refresh mislukt, status=%s body=%s", $status_code, $body));
                    self::deleteToken();
                } else {
                    $response = json_decode($body, true);
                    $response["expires_in"] = time() + ($response["expires_in_ms"] / 1000) - 10;
                    Debug(__FILE__, __LINE__, sprintf("refresh token=%s", $username, $response["access_token"]));

                    // opslaan voor later gebruik
                    $fd = fopen("matrix-access_token.json", "w");
                    fwrite($fd, json_encode($response));
                    fclose($fd);

                    return $response["access_token"];
                }
            }
        }

        if ((!isset($username)) || (!isset($password))) {
            $data = array(
                "type" => "m.login.password",
                "user" => $matrix_settings["user"],
                "refresh_token" => true,
                "password" => $matrix_settings["password"]
            );
        }
        else
        {
            $data = array(
                "type" => "m.login.password",
                "refresh_token" => true,
                "user" => $username,
                "password" => $password
            );
        }

        $loginUrl = $matrix_settings['url'] . "_matrix/client/r0/login";
        $curl_session = self::initCurl($loginUrl, "POST");
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($data));
        $body = curl_exec($curl_session);

        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            Error(__FILE__, __LINE__, sprintf("login(%s, %s)", $username, isset($password) ? "true" : "false"));
            Error(__FILE__, __LINE__, sprintf("Inloggen mislukt, status=%s body=%s", $status_code, $body));
            throw new Exception("500;Inloggen mislukt;" . $body);
        }

        $response = json_decode($body, true);
        $response["expires_in"] = time() + 5 * 60;
        Debug(__FILE__, __LINE__, sprintf("login gebruiker=%s  token=%s", $username, $body));

        // opslaan voor later gebruik, maar alleen voor helios account
        if (!isset($username)) {
            $fd = fopen("matrix-access_token.json", "w");
            fwrite($fd, json_encode($response));
            fclose($fd);
        }

        return $response["access_token"];
    }

    static private function deleteToken()
    {
        if (isset($_SESSION["MATRIX"]))
            unset($_SESSION["MATRIX"]);
        unlink("matrix-access_token.json");
    }

    static  private function HandleError($status_code, $body)
    {
        if ($status_code == 401)
        {
            $error = json_decode($body, true);
            if ($error['errcode'] == 'M_UNKNOWN_TOKEN')
            {
                self::deleteToken();
            }
        }
    }


    // zie https://matrix-org.github.io/synapse/latest/admin_api/user_admin_api.html#query-user-account
    static public function bestaatGebruiker($id): ?array
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("bestaatGebruiker(%s)", $id));

        if (!isset($matrix_settings))
            return null;

        if (!isset($_SESSION["MATRIX"]))
            $_SESSION["MATRIX"] = self::login();

        $url = sprintf("%s_synapse/admin/v2/users/@%s:%s", $matrix_settings['url'], strtolower($id), $matrix_settings["domein"]);
        $curl_session = self::initCurl($url);

        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            return null;
        }

        Debug(__FILE__, __LINE__, sprintf("gebruiker = %s", $body));
        return json_decode($body, true);
    }

    // zie https://matrix-org.github.io/synapse/latest/admin_api/user_admin_api.html#create-or-modify-account
    static public function updateGebruiker($lid, $password = null)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("updateGebruiker (%s,%s)", print_r($lid, true), isset($password) ? "true" : "false"));
        if (!isset($matrix_settings))
            return;

        if ($lid["VERWIJDERD"]) {
            self::verwijderGebruiker($lid);
            return;             // na verwijderen kunnen we stoppen
        }

        $updateNodig = false;

        $matrixGebruiker = self::bestaatGebruiker(strtolower($lid["INLOGNAAM"]));
        $gebruikerBestaat = isset($matrixGebruiker);

        if (!isset($gebruikerBestaat))
        {
            Debug(__FILE__, __LINE__, sprintf("Gebruiker bestaat niet, upload avatar uit helios profiel"));
            $avatarUrl = !isset($lid["AVATAR"]) ? null : self::uploadAvatar($lid, $lid["AVATAR"]);
            $updateNodig = isset($avatarUrl);
        }
        else
        {
            // Als de gebruiker bestaat, gaan we kijken of er een update nodig is
            $avatarUrl = null;  // synapse::updateAvatar($lid);
            if (isset($avatarUrl)) {
                $updateNodig = true;
            }
            else {
                $email = null;
                if (isset($matrixGebruiker["threepids"])) {
                    foreach ($matrixGebruiker["threepids"] as $t) {
                        switch ($t["medium"]) {
                            case "email" :
                                $email = $t["address"];
                                break;
                        }
                    }
                }

                if ($email !== $lid['EMAIL'])
                    $updateNodig = true;
                else if ($matrixGebruiker['admin'] !== $lid['BEHEERDER'])
                    $updateNodig = true;
                else if ($matrixGebruiker['displayname'] !== $lid["NAAM"])
                    $updateNodig = true;
                else if ($matrixGebruiker['deactivated'] !== $lid['VERWIJDERD'])
                    $updateNodig = true;
            }
        }

        // moeten we matrix data aanpassen?
        Debug(__FILE__, __LINE__, sprintf("gebruikerBestaat=%s updateNodig=%s", $gebruikerBestaat ? "true" : "false", $updateNodig ? "true" : "false"));
        if (!$gebruikerBestaat || $updateNodig || isset($password))
        {
            $data = array();

            if (!isset($_SESSION["MATRIX"]))
                $_SESSION["MATRIX"] = self::login();

            $url = sprintf("%s_synapse/admin/v2/users/@%s:%s", $matrix_settings['url'], strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);

            if (!$gebruikerBestaat || $updateNodig) {  // alle user data vervangen
                $data = array(
                    "displayname" => $lid["NAAM"],
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
            }

            if (isset($password))
            {
                $data["password"] = $password;
                $data["logout_devices"] = false;
            }

            if (isset($avatarUrl))
                $data["avatar_url"] = $avatarUrl;

            // voor re-activeren moet wachtwoord ingevoerd zijn. Als dat niet zo is de maken we een fake wachtwoord
            if (!$lid['VERWIJDERD'] && $matrixGebruiker['deactivated'])
            {
                if (!array_key_exists("password", $data))
                    $data["password"] = base64_encode(random_bytes(10));
            }

            Debug(__FILE__, __LINE__, sprintf("url=%s", $url));
            Debug(__FILE__, __LINE__, sprintf("data=%s", print_r($data, true)));

            if (count($data) == 0) {
                Debug(__FILE__, __LINE__, sprintf("Geen data om te updaten %s %s", print_r($lid, true), print_r($matrixGebruiker, true)));
                Error(__FILE__, __LINE__, sprintf("Geen data om te updaten"));
            }
            else {
                $curl_session = self::initCurl($url, "PUT");
                curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($data));
                $body = curl_exec($curl_session);

                $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

                if ($status_code != 200) {
                    self::HandleError($status_code, $body);
                    if ($status_code == 0)
                        Error(__FILE__, __LINE__, sprintf("status=0 url=%s", $url));
                    else
                        Error(__FILE__, __LINE__, sprintf("status=%s body=%s", $status_code, $body));
                }
                Debug(__FILE__, __LINE__, sprintf("updateGebruiker result = %s %s", $status_code, $body));
            }
        }
    }

    // zie https://matrix-org.github.io/synapse/latest/admin_api/user_admin_api.html#deactivate-account
    static public function verwijderGebruiker($lid): void
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("verwijderGebruiker %s", $lid["INLOGNAAM"]));
        if (!isset($matrix_settings))
            return;

        if (!isset($_SESSION["MATRIX"]))
            $_SESSION["MATRIX"] = self::login();

        $matrixGebruiker = self::bestaatGebruiker(strtolower($lid["INLOGNAAM"]));
        if (!isset($matrixGebruiker)) {
            Debug(__FILE__, __LINE__, sprintf("Gebruiker %s bestaat niet in Matrix", $lid["INLOGNAAM"]));
            return;
        }

        $data = array("erase" => true);

        $url = sprintf("%s_synapse/admin/v1/deactivate/@%s:%s", $matrix_settings['url'], strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);
        $curl_session = self::initCurl($url, "POST");
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($data));

        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            Error(__FILE__, __LINE__, sprintf("Verwijderen gebruiker mislukt, status=%s body=%s", $status_code, $body));
            throw new Exception("500;Verwijderen gebruiker mislukt;" . $body);
        }
        Debug(__FILE__, __LINE__, sprintf("verwijderGebruiker result = %s %s", $status_code, $body));
    }

    // zie https://spec.matrix.org/latest/client-server-api/#post_matrixmediav3upload
    static public function updateAvatar($lid): ?string
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("uploadAvatar %s", print_r($lid, true)));
        if (!isset($matrix_settings))
            return null;

        if (!isset($lid['AVATAR']))
            return null;

        // ophalen avatar uit oorspronkelijke bron
        $ch = self::initCurl($lid['AVATAR']);
        $avatar = curl_exec($ch);

        $body = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            Error(__FILE__, __LINE__, sprintf("Oorspondelijke avatar niet beschikbaar status_code = %s", $status_code));
            return null;
        }
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $orgSize = curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD);
        curl_close($ch);
        // done

        $updateNodig = false;

        // inloggen op Matrix indien nodig
        if (!isset($_SESSION["MATRIX"]))
            $_SESSION["MATRIX"] = self::login();

        // download picture van matrix
        $matrixGebruiker = self::bestaatGebruiker(strtolower($lid["INLOGNAAM"]));
        if (!isset($matrixGebruiker)) {
            $updateNodig = true;
        }
        else if (!isset($matrixGebruiker["avatar_url"]))
        {
            Debug(__FILE__, __LINE__, sprintf("Gebruiker %s heeft geen avatar in Matrix", $lid["INLOGNAAM"]));
            $updateNodig = true;
        }
        else {
            $avatar_split = explode("/", $matrixGebruiker["avatar_url"]);
            $avatarID = $avatar_split[count($avatar_split) - 1];
            $url = sprintf("%s_matrix/media/v3/download/%s/%s", $matrix_settings['url'], $matrix_settings['domein'], $avatarID);

            $curl_session = self::initCurl($url);
            $matrixAvatar = curl_exec($curl_session);

            $body = curl_exec($curl_session);
            $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

            if ($status_code != 200) {
                self::HandleError($status_code, $body);
                Debug(__FILE__, __LINE__, sprintf("Matrix avatar niet beschikbaar status_code = %s", $status_code));
                return null;
            }
            $matrixSize = curl_getinfo($curl_session,CURLINFO_SIZE_DOWNLOAD);

            if ($orgSize != $matrixSize)
            {
                Debug(__FILE__, __LINE__, sprintf("Avatar %s is gewijzigd, update nodig", $lid["INLOGNAAM"]));
                $updateNodig = true;
            }
        }

        if (!$updateNodig)
        {
            Debug(__FILE__, __LINE__, sprintf("Avatar %s is niet gewijzigd, geen update nodig", $lid["INLOGNAAM"]));
            return null;
        }

        // uploaden naar matrix
        $url = sprintf("%s_matrix/media/v3/upload?filename=%s", $matrix_settings['url'], $lid["ID"]);

        $curl_session = self::initCurl($url, "POST", $contentType);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, $avatar);

        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        Debug(__FILE__, __LINE__, sprintf("uploadAvatar result = %s %s", $status_code, $body));

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            Error(__FILE__, __LINE__, sprintf("Uploaden avatar mislukt, status=%s body=%s", $status_code, $body));
            return null;
        }
        $retVal = json_decode($body, true);
        return $retVal['content_uri'];
    }

    // Toevoegen aan de algemene kamer en de kamers die van toepassing zijn voor de functie
    static public function toevoegenAanKamers($lid): void
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("toevoegenAanKamers %s", print_r($lid, true)));
        if (!isset($matrix_settings))
            return;

        if (!isset($matrix_settings["kamers"]))
            return;

        // maak een koppeling van kamer naam naar kamer ID
        // we hebben de ID nodig om de gebruiker straks toe te voegen aan de kamer
        self::kamerMapping();

        // Iedereen moet toegang hebben tot de algemene kamers
        $matrixUserID = sprintf("@%s:%s", strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);
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

        // Als lid een specifieke rol heeft, dan toevoegen aan de bijbehorende kamers
        foreach ($functiesMappingArray as $rol => $config)
        {
            if ($lid[$rol])
                self::toevoegen($matrix_settings["kamers"][$config], $matrixUserID);
        }
    }

    // Zorg voor een mapping lijst tussen de naam van de kamer en het ID van de kamer
    // zie https://matrix-org.github.io/synapse/v1.40/admin_api/rooms.html#list-rooms
    static private function kamerMapping()
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("kamerMapping()"));

        // ophalen kamers, we hebben straks de ID van de kamers nodig, in de configuratie staat de naam van de kamer en niet het ID
        $url = sprintf("%s_synapse/admin/v1/rooms", $matrix_settings['url'] );
        $curl_session = self::initCurl($url);

        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            Error(__FILE__, __LINE__, sprintf("Kamers opvragen mislukt, status=%s body=%s", $status_code, $body));
            throw new Exception("500;Kamers opvragen mislukt;" . $body);
        }

        // body bevat een lijst van kamers
        $kamers = json_decode($body, true)["rooms"];

        // lookup tabel maken
        foreach ($kamers as $kamer)
            self::$mappingTabel[$kamer["name"]] = $kamer["room_id"];

        Debug(__FILE__, __LINE__, sprintf("mapping %s", print_r(self::$mappingTabel, true)));
    }


    // Gebruiker toevoegen aan de kamers (uit de configuratie) als hij/zij nog niet in de kamer aanwezig is
    static private function toevoegen($kamers, $userID): void
    {
        Debug(__FILE__, __LINE__, sprintf("toevoegen(%s, %s)", print_r($kamers, true), $userID));

        $mh = curl_multi_init();
        $curl_session = array();

        // toevoegen aan de kamers die voor iedereen bestemd zijn
        for ($i = 0; $i < count($kamers); $i++)
        {
            if (!array_key_exists($kamers[$i], self::$mappingTabel))
            {
                Error(__FILE__, __LINE__, sprintf("ERROR: kamer %s niet gevonden", $kamers[$i]));
                continue;
            }
            $roomID = self::$mappingTabel[$kamers[$i]];

            if (!self::isInKamer($roomID, $userID))
            {
                $cs = self::toevoegenUrl($roomID, $userID);

                if ($cs === null) {
                    Error(__FILE__, __LINE__, sprintf("ERROR: toevoegenUrl mislukt for %s", $kamers[$i]));
                }
                else
                {
                    $curl_session[$i] = $cs;
                    curl_multi_add_handle($mh, $curl_session[$i]);
                }
            }
        }

        Debug(__FILE__, __LINE__, sprintf("Toevoegen aan %d kamers", count($curl_session)));
        // uitvoeren van alle curl sessies
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);

        Debug(__FILE__, __LINE__, "Toevoegen aan kamers klaar");

        // get content and remove handles
        foreach($curl_session as $id => $c) {
            $result = curl_multi_getcontent($c);

            if ($result === false) {
                Error(__FILE__, __LINE__, sprintf("ERROR: curl_multi_getcontent failed for %s", $id));
            }
            else {
                $body = curl_exec($c);
                $status_code = curl_getinfo($c, CURLINFO_HTTP_CODE); //get status code
                $url = curl_getinfo($c, CURLINFO_EFFECTIVE_URL);

                if ($status_code != 200) {
                    self::HandleError($status_code, $body);
                    Error(__FILE__, __LINE__, sprintf("Toevoegen aan kamer mislukt, status=%s body=%s", $status_code, $body));
                    Error(__FILE__, __LINE__, sprintf("Toevoegen aan kamer mislukt, body=%s", $result));
                } else {
                    Debug(__FILE__, __LINE__, sprintf("Toevoegen aan kamer gelukt, status=%s url=%s", $status_code, $url));
                    Debug(__FILE__, __LINE__, sprintf("Toevoegen aan kamer gelukt, body=%s", $result));
                }
            }
            curl_multi_remove_handle($mh, $c);
        }
    }


    // Zit de gebruiker al in een kamer, roomID is interne ID van matrix
    // zie https://matrix-org.github.io/synapse/v1.40/admin_api/rooms.html#room-members-api
    static private function isInKamer($roomID, $userID) : bool
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("isInKamer(%s, %s)", $roomID, $userID));

        $url = sprintf("%s_synapse/admin/v1/rooms/%s/members", $matrix_settings['url'], $roomID);
        $curl_session = self::initCurl($url);
        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) {
            self::HandleError($status_code, $body);
            Error(__FILE__, __LINE__, sprintf("Kamer opvragen mislukt, status=%s body=%s", $status_code, $body));
            throw new Exception("500;Kamer opvragen mislukt;" . $body);
        }
        $members = json_decode($body, true)["members"];

        foreach ($members as $member)
        {
            if ($member == $userID)
                return true;
        }
        Debug(__FILE__, __LINE__, "Niet in kamer");
        return false;
    }

    // Het echte toevoegen aan een kamer in Matrix
    // zie https://spec.matrix.org/legacy/client_server/r0.3.0.html#post-matrix-client-r0-rooms-roomid-join
    static private function toevoegenUrl($roomID, $userID)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("toevoegenUrl(%s, %s)", $roomID, $userID));

        // Matrix kan alleen invites uitsturen als je zelf in de kamer zit. We joinen dus eerst.
        $url = sprintf("%s_matrix/client/v3/join/%s", $matrix_settings['url'], $roomID);
        $curl_session = self::initCurl($url, "POST");
        $body = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        Debug(__FILE__, __LINE__, sprintf("join = %s %s", $status_code, $body));

        $url = sprintf("%s_synapse/admin/v1/join/%s", $matrix_settings['url'], $roomID);
        $curl_session = self::initCurl($url, "POST");

        if ($curl_session === null)
            return null;

        curl_setopt($curl_session, CURLOPT_POSTFIELDS, sprintf("{\"user_id\": \"%s\"}", $userID));

        return $curl_session;
    }


    // Sommige kamers zijn belangrijk en worden daarom als favoriet gemarkeerd
    public static function markeerAlsFavoriet($lid, $password)
    {
        global $matrix_settings;

        Debug(__FILE__, __LINE__, sprintf("markeerAlsFavoriet(%s)", print_r($lid, true)));
        if (!isset($matrix_settings))
            return;

        if (!isset($matrix_settings["favorieten"]))
            return;

        if (!isset($password))
            return;

        if (!isset($_SESSION["MATRIX"]))
            $_SESSION["MATRIX"] = self::login();

        self::kamerMapping();   // moeten we opvragen met admin account

        $token= self::login(strtolower($lid["INLOGNAAM"]), $password);  // we gaan nu verder met user account
        $matrixUserID = sprintf("@%s:%s", strtolower($lid["INLOGNAAM"]), $matrix_settings["domein"]);

        $mh = curl_multi_init();
        $curl_session = array();

        // verzamel alle curl sessies
        for ($i = 0; $i < count($matrix_settings["favorieten"]); $i++) {
            $kamerNaam = $matrix_settings["favorieten"][$i];
            $roomID = self::$mappingTabel[$kamerNaam];

            if (!isset($roomID))
            {
                Error(__FILE__, __LINE__, sprintf("ERROR: roomID null voor favoriet %s", $kamerNaam));
                continue;
            }
            $url = sprintf("%s_matrix/client/v3/user/%s/rooms/%s/tags/m.favourite", $matrix_settings['url'], $matrixUserID, $roomID);

            $curl_session[$i] = self::initCurl($url, "PUT", null, $token);
            curl_setopt($curl_session[$i], CURLOPT_POSTFIELDS, "{}");
            curl_multi_add_handle($mh, $curl_session[$i]);
        }

        Debug(__FILE__, __LINE__, sprintf("Toevoegen %d favorieten", count($curl_session)));
        // uitvoeren van alle curl sessies
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);

        Debug(__FILE__, __LINE__, "Favorieten zijn toegevoegd");

        // get content and remove handles
        foreach($curl_session as $id => $c) {
            $result = curl_multi_getcontent($c);

            if ($result === false) {
                Error(__FILE__, __LINE__, sprintf("ERROR: curl_multi_getcontent failed for %s", $id));
            }
            else
            {
                $body = curl_exec($c);
                $status_code = curl_getinfo($c, CURLINFO_HTTP_CODE); //get status code
                $url = curl_getinfo($c, CURLINFO_EFFECTIVE_URL);

                if ($status_code != 200)
                {
                    self::HandleError($status_code, $body);
                    Error(__FILE__, __LINE__, sprintf("Toevoegen aan favorieten mislukt, status=%s url=%s", $status_code, $url));
                    Error(__FILE__, __LINE__, sprintf("Toevoegen aan favorieten mislukt, body=%s", $result));
                }
                else
                {
                    Debug(__FILE__, __LINE__, sprintf("Toevoegen aan favorieten gelukt, status=%s url=%s", $status_code, $url));
                    Debug(__FILE__, __LINE__, sprintf("Toevoegen aan favorieten gelukt, body=%s", $result));
                }
            }
            curl_multi_remove_handle($mh, $c);
        }
    }

    function directeKamer($gebruiker)
    {
        global $matrix_settings;

        $gebruiker_id = sprintf("@%s:%s", strtolower($gebruiker), $matrix_settings["domein"]);
        $createRoom = array(
            "preset" => "trusted_private_chat",
            "visibility" => "private",
            "invite" => array($gebruiker_id),
            "is_direct" => true,
            "initial_state" => array(
                array(
                    "type" => "m.room.guest_access",
                    "state_key" => "",
                    "content" => array(
                        "guest_access" => "can_join"
                    )
                )
            )
        );
    }
}
