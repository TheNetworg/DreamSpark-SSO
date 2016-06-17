<?php
$account = $_POST["account"];
$key = $_POST["key"];
if($account && $key) {
    $tenantDomain = API::getTenantDomain();
    $settings = appStorage::getSettings($tenantDomain);
    $webstore = json_decode($settings->getPropertyValue('webstore'));
    $webstore->account = $account;
    $webstore->key = $key;
    
    appStorage::setSettings($tenantDomain, ['webstore' => json_encode($webstore)]);
    
    $app->flash("success", "Your Webstore settings were sucessfully updated!");
}
else {
    $app->flash("error", "You have to fill out both your Webstore account number and key!");
}
$app->redirect($app->request->getResourceUri());
