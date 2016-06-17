<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$webstore = json_decode($settings->getPropertyValue("webstore"));

$app->render('settings/webstore.twig', [
    'webstore' => $webstore
]);
