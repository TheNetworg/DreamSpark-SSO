<?php
$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$webstore = json_decode($settings->getPropertyValue('webstore'));

$academicStatus = API::getAcademicStatus();
$me = API::me();

DreamSpark::SignIn($webstore->account, $webstore->key, $me, $academicStatus);