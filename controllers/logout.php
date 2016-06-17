<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$organization = null;
if($settings) {
    $organization = json_decode($settings->getPropertyValue('organization'));
}
$redirectUri = isset($organization->redirectUri) ? $organization->redirectUri : "https://www.msn.com";

session_destroy();

$logoutUrl = $app->OAuth2->provider->getLogoutUrl($redirectUri);

$app->redirect($logoutUrl);