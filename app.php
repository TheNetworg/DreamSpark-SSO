<?php
if(getenv("ENVIRONMENT") == "DEV") ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

require_once "includes/api.php";
require_once "includes/app.storage.php";
require_once "includes/dreamspark.php";

session_cache_limiter(false);
session_start();

$app = new \Slim\Slim([
    'view' => new \Slim\Views\Twig(),
    'templates.path' => './views'
]);

$view = $app->view();
$view->parserOptions = [
    'debug' => true,
    'cache' => dirname(__FILE__) . '/assets/cache/twig'
];
// Set the template directories for Twig
$viewPath = 'views';
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST,
    RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
);
$paths = [$viewPath];
foreach ($iter as $path => $dir) {
    if ($dir->isDir()) {
        $paths[] = $path;
    }
}
$view->twigTemplateDirs = $paths;
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

if(getenv("ENVIRONMENT") != "DEV") {
    // Disable public debug logs
    $app->config('debug', false);
    // Disable view debug logs
    $view->parserOptions['debug'] = false;
    // Load assets from cache
    $view->appendData([
        'cache' => json_decode(file_get_contents('assets/cache/cache.json'))
    ]);
}

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