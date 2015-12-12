<?php
function JSON() {
	\SlimJson\Middleware::inject([
		'json.override_error' => true,
		'json.override_notfound' => true,
		'json.clear_data' => true
	]);
}

$app->get('/', ['Auth', 'Authenticate'], ['API', 'authorizeOrganization'], function() use($app) {
	if(API::isAdministrator()) {
		$app->redirect("/settings");
	}
	else {
		$app->redirect("/SSOPassThrough");
	}
});
$app->group("/settings", ['Auth', 'Authenticate'], ['API', "authorizeOrganization"], ['API', "authorizeAdministrator"], function() use ($app) {
	$app->get('/', function() use($app) {
		include_once "/templates/settings.php";
	});
	$app->get('/webstore', function() use($app) {
		include_once "/templates/settings.webstore.php";
	});
	$app->post('/webstore', function() use($app) {
		$account = $_POST["account"];
		$key = $_POST["key"];
		if($account && $key) {
			$tenantDomain = API::getTenantDomain();
			$settings = appStorage::getSettings($tenantDomain);
			$webstore = json_decode($settings->getPropertyValue('webstore'));
			$webstore->account = $account;
			$webstore->key = $key;
			
			appStorage::setSettings($tenantDomain, ['webstore' => json_encode($webstore)]);
			
			$app->flash("updateSuccess", "1");
		}
		else {
			$app->flash("validationError", "1");
		}
		$app->redirect($app->request->getResourceUri());
	});
	$app->get('/permissions', function() use($app) {
		include_once "/templates/settings.permissions.php";
	});
	$app->post('/permissions', function() use($app) {
		$access = $_POST["access"];
		$students = isset($_POST["students"]) ? $_POST["students"] : [];
		$faculty = isset($_POST["faculty"]) ? $_POST["faculty"] : [];
		$staff = isset($_POST["staff"]) ? $_POST["staff"] : [];
		//validate that all are valid UUIDs
		if($access == "everyone") $students = $faculty = $staff = [];
		
		if(array_search($access, ["everyone", "groups"]) !== FALSE) {
			$tenantDomain = API::getTenantDomain();
			$settings = appStorage::getSettings($tenantDomain);
			
			appStorage::setSettings($tenantDomain, [
				'accessGroups' => json_encode([
					"students" => $students,
					"faculty" => $faculty,
					"staff" => $staff
				]),
				'access' => $access
			]);
			
			$app->flash("updateSuccess", "1");
		}
		else {
			$app->flash("validationError", "1");
		}
		$app->redirect($app->request->getResourceUri());
	});
	$app->get('/organization', function() use($app) {
		include_once "/templates/settings.organization.php";
	});
	$app->post('/organization', function() use($app) {
		$logoutUri = $_POST["logoutUri"];
		if($logoutUri == "" || filter_var($logoutUri, FILTER_VALIDATE_URL)) {
			$tenantDomain = API::getTenantDomain();
			$settings = appStorage::getSettings($tenantDomain);
			$organization = json_decode($settings->getPropertyValue('organization'));
			$organization->logoutUri = $logoutUri;
			
			appStorage::setSettings($tenantDomain, ['organization' => json_encode($organization)]);
			
			$app->flash("updateSuccess", "1");
		}
		else {
			$app->flash("validationError", "1");
		}
		$app->redirect($app->request->getResourceUri());
	});
});
$app->group("/api", ['Auth', 'Authenticate'], ['API', "authorizeOrganization"], ['API', "authorizeAdministrator"], 'JSON', function() use ($app) {
	$app->get('/groups', function() use($app) {
		$query = isset($_GET["q"]) ? $_GET["q"] : "";
		$results = API::searchGroupByName($query);
		$app->render(200, $results);
	});
});
$app->get("/SSOPassThrough", ['Auth', 'Authenticate'], ['API', "authorizeOrganization"], ['API', "authorizeResource"], function() use ($app) {
	$tenantDomain = API::getTenantDomain();
	$settings = appStorage::getSettings($tenantDomain);
	$webstore = json_decode($settings->getPropertyValue('webstore'));
	
	$academicStatus = API::getAcademicStatus();
	$me = API::me();
	
	DreamSpark::SignIn($webstore->account, $webstore->key, $me, $academicStatus);
});
$app->get("/external", function() use ($app) {
	if($_GET["action"] == "signin") $app->redirect("/");
	else if($_GET["action"] == "signout") $app->redirect("/logout");
	else {
		throw new \Exception("Unsupported action from external service.");
	}
});
$app->get("/logout", ['Auth', 'Authenticate'], function() use ($app) {
	$tenantDomain = API::getTenantDomain();
	$settings = appStorage::getSettings($tenantDomain);
	$organization = null;
	if($settings) {
		$organization = json_decode($settings->getPropertyValue('organization'));
	}
	$redirectUri = isset($organization->redirectUri) ? $organization->redirectUri : "https://www.msn.com";
	
	session_destroy();
	
	$app->redirect("https://login.windows.net/common/oauth2/logout?post_logout_redirect_uri=".rawurlencode($redirectUri));
});
$app->get("/install", ['Auth', 'Authenticate'], ['API', 'authorizeAdministrator'], function() use ($app) {
	if(isset($_GET["admin_consent"])) {
		$tenantDomain = API::getTenantDomain();
		$settings = appStorage::getSettings($tenantDomain);
		
		$accessGroups = [
			'students' => [],
			'faculty' => [],
			'staff' => []
		];
		$webstore = [
			'account' => null,
			'key' => null
		];
		$organization = [
			'logoutUri' => ""
		];
		$access = 'groups';
		if($settings) {
			$accessGroups = json_decode($settings->getPropertyValue("accessGroups"));
			$webstore = json_decode($settings->getPropertyValue("webstore"));
			$organization = json_decode($settings->getPropertyValue("organization"));
			$access = $settings->getPropertyValue("access");
		}
		$setup = [
			'installed' => "1",
			'access' => $access,
			'accessGroups' => json_encode($accessGroups),
			'webstore' => json_encode($webstore),
			'organization' => json_encode($organization),
			'adminConsent' => "1",
			'version' => "1"
		];
		appStorage::setSettings($tenantDomain, $setup);
		
		$app->flash("installResult", "success");
		$app->redirect("/settings");
	}
	else {
		$tenantDomain = API::getTenantDomain();
		$settings = appStorage::getSettings($tenantDomain);
		$installed = false;
		$adminConsent = false;
		if($settings) {
			$installed = $settings->getPropertyValue("installed");
			$adminConsent = $settings->getPropertyValue("adminConsent");
			echo $adminConsent;
		}
		if($settings && $adminConsent) {
			$app->flash("installResult", "alreadyInstalled");
			$app->redirect("/settings");
		}
		else {
			$redirectUrl = $app->OAuth2->provider->getAuthorizationUrl(['resource' => "https://graph.windows.net/", 'admin_consent' => 'true']);
			$app->redirect($redirectUrl);
		}
	}
});
$app->get("/debug", function() use ($app) {
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>";
	/*echo "<pre>";
	$result = AzureGraphAPI::getObjects("myOrganization", "users", "User");
	if($result) print_r($result);
	else if($result == FALSE) echo "FALSE";
	else if($result == null) echo "null";
	else echo $result;*/
});
?>