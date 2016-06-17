<?php
$logoutUri = $_POST["logoutUri"];
if($logoutUri == "" || filter_var($logoutUri, FILTER_VALIDATE_URL)) {
    $tenantDomain = API::getTenantDomain();
    $settings = appStorage::getSettings($tenantDomain);
    $organization = json_decode($settings->getPropertyValue('organization'));
    $organization->logoutUri = $logoutUri;
    
    appStorage::setSettings($tenantDomain, ['organization' => json_encode($organization)]);
    
    $app->flash("success", "Your organization settings were successfuly updated!");
}
else {
    $app->flash("error", "The logoutUri you entered isn't a valid URL.");
}
$app->redirect($app->request->getResourceUri());