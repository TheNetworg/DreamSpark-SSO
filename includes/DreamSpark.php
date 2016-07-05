<?php
class DreamSpark {
    public static function SignIn($webstore_account, $webstore_key, $user, $academicStatus = "students") {
        $givenName = static::PrepareGivenName($user['givenName']);
        $surname = static::PrepareSurname($user['surname']);

        $webstoreUrl = "https://e5.onthehub.com/WebStore/Security/AuthenticateUser.aspx?".
            "account=".$webstore_account."&".
            "key=".$webstore_key."&".
            "shopper_ip=".$_SERVER["REMOTE_ADDR"]."&".
            "academic_statuses=".$academicStatus."&".
            "username=".rawurldecode($user['userPrincipalName'])."&".
            "email=".rawurldecode($user['mail'])."&". //Use different attribute?
            "first_name=".rawurldecode($givenName)."&".
            "last_name=".rawurldecode($surname);
        
        $session = curl_init();
        curl_setopt_array($session, array(
            CURLOPT_URL					=> $webstoreUrl,
            CURLOPT_RETURNTRANSFER		=> TRUE,
        ));
        
        $redirectUrl = curl_exec($session);
        $status = curl_getinfo($session, CURLINFO_HTTP_CODE);
        
        curl_close($session);
        
        if($status == 200) {
            if(filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                global $app;
                $app->redirect($redirectUrl);
            }
            else {
                throw new \Exception("DreamSpark: Result of operation wasn't URL.");
            }
        }
        else {
            // error_log("[dreamspark_error] ".$status." --- ".$redirectUrl." --- ".json_encode($user)." --- ".$givenName." --- ".$surname);
            throw new \Exception("DreamSpark: Something went wrong while logging into DreamSpark. Please try again later or contact your administrator.");
        }
    }

    // DreamSpark also doesn't support firstName having more than one word, we handle the entire sanitization here
    private static function PrepareGivenName($string) {
        $string = static::RemoveAccents($string);
        $string = explode(" ", $string);
        return $string[0];
    }
    private static function PrepareSurname($string) {
        $string = self::RemoveAccents($string);
        $string = explode(" ", $string);
        return end($string);
    }

    // Since DreamSpark's login API doesn't support characters like é, we replace them with regular characters like e
    private static function RemoveAccents($string) {
        // From http://theserverpages.com/php/manual/en/function.str-replace.php
        $string = htmlentities($string);
        return preg_replace("/&([a-z])[a-z]+;/i", "$1", $string);
    }
}
?>