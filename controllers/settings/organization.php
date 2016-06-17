<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$organization = json_decode($settings->getPropertyValue("organization"));

$app->render('settings/organization.twig', [
    'organization' => $organization,
    'tenantDomain' => $tenantDomain
]);