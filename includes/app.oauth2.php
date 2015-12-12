<?php
class Auth {
	public static function Authenticate() {
		global $app;
		
		if(isset($_SESSION['OAuth2.token'])) {
			$app->OAuth2->token = $_SESSION['OAuth2.token'];
		}
		
		if(isset($_GET['code']) && isset($_SESSION['OAuth2.state']) && isset($_GET['state'])) {
			if($_GET['state'] == $_SESSION['OAuth2.state']) {
				unset($_SESSION['OAuth2.state']);
				$app->OAuth2->token = $app->OAuth2->provider->getAccessToken('authorization_code', ['code' => $_GET['code'], 'resource' => "https://graph.windows.net/"]);
				$_SESSION['OAuth2.token'] = $app->OAuth2->token;
				
				$redirectToOriginalUrl = "/";
				if($app->view()->getData('flash')["originalPath"]) $redirectToOriginalUrl = $app->view()->getData('flash')["originalPath"];
				
				if(isset($_GET["admin_consent"])) {
					$redirectToOriginalUrl .= "?admin_consent=true";
				}
				
				$app->redirect($redirectToOriginalUrl);
			}
			else {
				$app->error("Returned state didn't match the expected value. Please go back and try again.");
			}
		}
		else {
			$app->flash("originalPath", $app->request->getResourceUri());
			
			if(!isset($app->OAuth2->token)) {
				$authUrl = $app->OAuth2->provider->getAuthorizationUrl(['resource' => "https://graph.windows.net/"]);
				$_SESSION['OAuth2.state'] = $app->OAuth2->provider->getState();
				$app->redirect($authUrl);
			}
			else if($app->OAuth2->token->hasExpired()) {
				$app->OAuth2->token = $app->OAuth2->provider->getAccessToken('refresh_token', [
					'refresh_token' => $app->OAuth2->token->getRefreshToken()
    			]);
				$_SESSION['OAuth2.token'] = $app->OAuth2->token;
			}
		}
	}
}
?>