<?php
class DreamSpark {
    public static function SignIn($webstore_account, $webstore_key, $user, $academicStatus = "students") {
        $webstoreUrl = "https://e5.onthehub.com/WebStore/Security/AuthenticateUser.aspx?".
            "account=".$webstore_account."&".
            "key=".$webstore_key."&".
            "shopper_ip=".$_SERVER["REMOTE_ADDR"]."&".
            "academic_statuses=".$academicStatus."&".
            "username=".rawurldecode($user['userPrincipalName'])."&".
            "email=".rawurldecode($user['mail'])."&". //Use different attribute?
            "first_name=".rawurldecode($user['givenName'])."&".
            "last_name=".rawurldecode($user['surname']);
        
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
            throw new \Exception("DreamSpark: Something went wrong while logging into DreamSpark. Please try again later or contact your administrator.");
        }
    }
}
?>