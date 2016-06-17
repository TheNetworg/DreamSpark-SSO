<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$access = $settings->getPropertyValue("access");
$accessGroups = json_decode($settings->getPropertyValue("accessGroups"));

$studentGroups = [];
$facultyGroups = [];
$staffGroups = [];

foreach($accessGroups->students as $objectId) {
	$group = API::getGroup($objectId);
    if($group) $studentGroups[] = $group;
}
foreach($accessGroups->faculty as $objectId) {
	$group = API::getGroup($objectId);
    if($group) $facultyGroups[] = $group;
}
foreach($accessGroups->staff as $objectId) {
	$group = API::getGroup($objectId);
    if($group) $staffGroups[] = $group;
}

$app->render('settings/permissions.twig', [
    'access' => $access,
    'studentGroups' => $studentGroups,
    'facultyGroups' => $facultyGroups,
    'staffGroups' => $staffGroups
]);
