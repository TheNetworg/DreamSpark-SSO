<?php
if(isset($_GET["admin_consent"])) {
    $tenantDomain = API::getTenantDomain();
    $settings = appStorage::getSettings($tenantDomain);
    
    $accessGroups = [
        'students' => [],
        'faculty' => [],
        'staff' => []
    ];
    $webstore = [
        'account' => null,
        'key' => null
    ];
    $organization = [
        'logoutUri' => ""
    ];
    $access = 'groups';
    if($settings) {
        $accessGroups = json_decode($settings->getPropertyValue("accessGroups"));
        $webstore = json_decode($settings->getPropertyValue("webstore"));
        $organization = json_decode($settings->getPropertyValue("organization"));
        $access = $settings->getPropertyValue("access");
    }
    $setup = [
        'installed' => "1",
        'access' => $access,
        'accessGroups' => json_encode($accessGroups),
        'webstore' => json_encode($webstore),
        'organization' => json_encode($organization),
        'adminConsent' => "1",
        'version' => "1"
    ];
    appStorage::setSettings($tenantDomain, $setup);
    
    $app->flash("success", "The application was successfuly set up in your tenant.");
    $app->redirect("/settings");
}
else {
    $tenantDomain = API::getTenantDomain();
    $settings = appStorage::getSettings($tenantDomain);
    $installed = false;
    $adminConsent = false;
    if($settings) {
        $installed = $settings->getPropertyValue("installed");
        $adminConsent = $settings->getPropertyValue("adminConsent");
    }
    if($settings && $adminConsent) {
        $app->flash("warning", "The application is already installed in your tenant.");
        $app->redirect("/settings");
    }
    else {
        $redirectUrl = $app->OAuth2->provider->getAuthorizationUrl(['resource' => "https://graph.windows.net/", 'prompt' => 'admin_consent']);
        $_SESSION['OAuth2.state'] = $app->OAuth2->provider->getState();
        $app->redirect($redirectUrl);
    }
}