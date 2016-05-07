<?php
if(PHP_SAPI !== 'cli') die('Server side only.');

require_once '../vendor/autoload.php';
require_once "includes/API.php";
require_once "includes/app.storage.php";

appStorage::connect();
$entities = appStorage::getAllSettings();

$app = new stdClass();
$app->OAuth2 = new stdClass();
$app->OAuth2->provider = new TheNetworg\OAuth2\Client\Provider\Azure([
    'clientId'          => getenv("Auth_appId"),
    'clientSecret'      => getenv("Auth_appSecret"),
    'redirectUri'       => getenv("Auth_redirectUri")
]);

foreach($entities as $entity) {
	$tenantDomain = $entity->getPartitionKey();
	$app->OAuth2->provider->tenant = $tenantDomain;
	$app->OAuth2->token = $app->OAuth2->provider->getAccessToken('client_credentials', ['resource' => "https://graph.windows.net/"]);
	
	$accessGroups = json_decode($entity->getPropertyValue("accessGroups"));
    $everyone = $entity->getPropertyValue("access") == "everyone" ? true : false;
	$groups = $accessGroups->students + $accessGroups->faculty + $accessGroups->staff;
	
	try {
		API::assignToApplication($tenantDomain, $everyone, $groups);
	} catch(\Exception $e) {
		echo "--FAIL-- ".$e->getMessage()."\n\n";
	}
}
?>