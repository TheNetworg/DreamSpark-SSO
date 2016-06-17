<?php
$access = $_POST["access"];
$students = isset($_POST["students"]) ? $_POST["students"] : [];
$faculty = isset($_POST["faculty"]) ? $_POST["faculty"] : [];
$staff = isset($_POST["staff"]) ? $_POST["staff"] : [];
//validate that all are valid UUIDs
if($access == "everyone") $students = $faculty = $staff = [];

if(array_search($access, ["everyone", "groups"]) !== FALSE) {
    $tenantDomain = API::getTenantDomain();
    $settings = appStorage::getSettings($tenantDomain);
    
    appStorage::setSettings($tenantDomain, [
        'accessGroups' => json_encode([
            "students" => $students,
            "faculty" => $faculty,
            "staff" => $staff
        ]),
        'access' => $access
    ]);
    
    $app->flash("success", "Your permissions were successfuly updated! If you changed permissions, please note that it may take up to 12 hours for users to see the application in their app launcher.");
}
else {
    $app->flash("error", "Something went wrong! Please refresh the page and try again.");
}
$app->redirect($app->request->getResourceUri());
