<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$organization = null;
if($settings) {
    $organization = json_decode($settings->getPropertyValue('organization'));
}
$redirectUri = isset($organization->logoutUri) ? $organization->logoutUri : "https://www.msn.com";

session_destroy();

$logoutUrl = $app->OAuth2->provider->getLogoutUrl($redirectUri); // ."&id_token_hint=".$app->OAuth2->token->getValues()["id_token"];

$app->redirect($logoutUrl);