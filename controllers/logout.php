<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$organization = null;
if($settings) {
    $organization = json_decode($settings->getPropertyValue('organization'));
}
$redirectUri = isset($organization->logoutUri) ? $organization->logoutUri : "https://www.msn.com";

session_destroy();

$redirectUri = $app->request->getUrl()."/loggedout?post_logout_redirect_uri=".rawurlencode($redirectUri);
$logoutUrl = $app->OAuth2->provider->getLogoutUrl($redirectUri);

$app->redirect($logoutUrl);