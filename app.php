<?php
if(getenv("ENVIRONMENT") == "DEV") ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

require_once "/templates/base.php";
require_once "includes/API.php";
require_once "includes/app.storage.php";
require_once "includes/DreamSpark.php";

session_cache_limiter(false);
session_start();

$app = new \Slim\Slim([]);
if(getenv("ENVIRONMENT") != "DEV") $app->config('debug', false);

appStorage::connect();

$app->OAuth2 = new stdClass();
$app->OAuth2->provider = new TheNetworg\OAuth2\Client\Provider\Azure([
    'clientId'          => getenv("Auth_appId"),
    'clientSecret'      => getenv("Auth_appSecret"),
    'redirectUri'       => getenv("Auth_redirectUri")
]);


require_once "includes/app.errors.php";
require_once "includes/app.oauth2.php";
require_once "includes/app.router.php";
require_once "includes/app.applicationinsights.php";

\Slim\ApplicationInsights::init();

$app->run();
?>