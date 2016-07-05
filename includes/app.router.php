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
		include 'controllers/settings/index.php';
	});
	$app->get('/webstore', function() use($app) {
		include 'controllers/settings/webstore.php';
	});
	$app->post('/webstore', function() use($app) {
		include 'controllers/settings/webstore.post.php';
	});
	$app->get('/permissions', function() use($app) {
		include 'controllers/settings/permissions.php';
	});
	$app->post('/permissions', function() use($app) {
		include 'controllers/settings/permissions.post.php';
	});
	$app->get('/organization', function() use($app) {
		include 'controllers/settings/organization.php';
	});
	$app->post('/organization', function() use($app) {
		include 'controllers/settings/organization.post.php';
	});
});
$app->group("/ajax", ['Auth', 'Authenticate'], ['API', "authorizeOrganization"], ['API', "authorizeAdministrator"], 'JSON', function() use ($app) {
	$app->get('/groups', function() use($app) {
		include 'controllers/ajax/groups.php';
	});
});
$app->get("/SSOPassThrough", ['Auth', 'Authenticate'], ['API', "authorizeOrganization"], ['API', "authorizeResource"], function() use ($app) {
	include 'controllers/ssopassthrough.php';
});
$app->get("/external", function() use ($app) {
	if($_GET["action"] == "signin") $app->redirect("/");
	else if($_GET["action"] == "signout") $app->redirect("/logout");
	else {
		throw new \Exception("Unsupported action from external service.");
	}
});
$app->get("/logout", ['Auth', 'Authenticate'], function() use ($app) {
	include 'controllers/logout.php';
});
$app->get("/install", ['Auth', 'Authenticate'], ['API', 'authorizeAdministrator'], function() use ($app) {
	include 'controllers/install.php';
});
$app->get("/debug", function() use ($app) {
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>";
	// echo "<pre>";
	// $result = AzureGraphAPI::getObjects("myOrganization", "users", "User");
	// if($result) print_r($result);
	// else if($result == FALSE) echo "FALSE";
	// else if($result == null) echo "null";
	// else echo $result;
});
?>