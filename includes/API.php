<?php
class cacheItem {
	public $url;
	public $content;
	public $expires;
	
	function __construct($url, $content, $expires) {
		$this->content = $content;
		$this->expires = time() + $expires;
		$this->url = $url;
	}
	public function isValid() {
		if($this->expires > time()) return true;
		else return false;
	}
}
class API {
	public static function authorizeAdministrator() {
		global $app;
		
		if(!self::isAdministrator()) {
			$app->error("You don't have sufficient permissions to acess this part of the site.");
		}
	}
	public static function authorizeOrganization() {
		global $app;
		
		$tenantDomain = self::getTenantDomain();
		$settings = appStorage::getSettings($tenantDomain);
		$installed = false;
		$adminConsent = false;
		if($settings) {
			$installed = $settings->getPropertyValue("installed");
			$adminConsent = $settings->getPropertyValue("adminConsent");
		}
		if(!$installed||!$adminConsent) {
			if(self::isAdministrator()) {
				$app->redirect("/install");
			}
			else {
				$app->error("The application isn't set up in your tenant.");
			}
		}
		else {
			$webstore = json_decode($settings->getPropertyValue("webstore"));
			if(!isset($webstore->account) || !isset($webstore->key)) {
				if(self::isAdministrator()) {
					if(strpos($app->request->getResourceUri(), "/settings") === false) {
						$app->flash("webstoreConfigError", "1");
						$app->redirect("/settings/webstore");
					}
				}
				else {
					$app->error("The application isn't configured correctly. Please contact your administrator.");
				}
			}
		}
	}
	public static function isAdministrator() {
		$memberOf = self::memberOf();
		foreach($memberOf as $group) {
			if($group['displayName'] == "Company Administrator") return true;
		}
		return false;
	}
	public static function authorizeResource() {
		global $app;
		$academicStatus = self::getAcademicStatus();
		if(!$academicStatus) {
			$app->error("You are not authorized to access this application. If you would like to get access, please contact your administrator.");
		}
	}
	public static function me() {
		global $app;
		
		$me = self::getCache("GRAPH:me");
		if(!$me) {
			$me = $app->OAuth2->provider->get("me", $app->OAuth2->token);
			if($me) self::setCache("GRAPH:me", $me, 600);
		}
		return $me;
	}
	public static function memberOf() {
		global $app;
		
		$memberOf = self::getCache("GRAPH:memberOf");
		if(!$memberOf) {
			$memberOf = $app->OAuth2->provider->get("me/memberOf", $app->OAuth2->token);
			if($memberOf) self::setCache("GRAPH:memberOf", $memberOf, 600);
		}
		return $memberOf;
	}
	public static function tenantDetails() {
		global $app;
		
		$tenantDetails = self::getCache("GRAPH:tenantDetails");
		if(!$tenantDetails) {
			$tenantDetails = $app->OAuth2->provider->get("myOrganization/tenantDetails", $app->OAuth2->token)[0];
			if($tenantDetails) self::setCache("GRAPH:tenantDetails", $tenantDetails, 600);
		}
		return $tenantDetails;
	}
	public static function groups() {
		global $app;
		
		$groups = self::getCache("GRAPH:groups");
		if(!$groups) {
			$groups = $app->OAuth2->provider->get("myOrganization/groups", $app->OAuth2->token);
			if($groups) self::setCache("GRAPH:groups", $groups, 600);
		}
		return $groups;
	}
	public static function getTenantDomain() {
		global $app;
		
		$tenantDetails = self::tenantDetails();
		foreach($tenantDetails['verifiedDomains'] as $verifiedDomain) {
			if(strpos($verifiedDomain['name'], ".onmicrosoft.com") !== FALSE && count(explode(".", $verifiedDomain['name'])) == 3) return $verifiedDomain['name'];
		}
		return "";
	}
	public static function searchGroupByName($groupName) {
		global $app;
		
		$filter = strlen($groupName) ? '$filter=startswith(displayName,\''.rawurldecode($groupName).'\')' : '';
		$result = $app->OAuth2->provider->get("myOrganization/groups?".$filter.'&$top=5', $app->OAuth2->token);
		return $result;
	}
	public static function getAcademicStatus() {
		$tenantDomain = self::getTenantDomain();
		$settings = appStorage::getSettings($tenantDomain);
		$access = $settings->getPropertyValue("access");
		if($access == "everyone") return "students";
		else {
			$memberOf = self::memberOf();
			$accessGroups = json_decode($settings->getPropertyValue("accessGroups"));
			foreach($memberOf as $group) {
				if(in_array($group['objectId'], $accessGroups->students)) return "students";
				else if(in_array($group['objectId'], $accessGroups->faculty)) return "faculty";
				else if(in_array($group['objectId'], $accessGroups->staff)) return "staff";
			}
			return false;
		}
	}
	public static function getGroup($groupId) {
		global $app;
		
		$group = $app->OAuth2->provider->get("myOrganization/groups/".rawurldecode($groupId), $app->OAuth2->token);
		return $group;
	}
	public static function assignToApplication($tenant, $everyone = false, $groupsToAssign = []) {
		global $app;
		
		$NULL_KEY = "00000000-0000-0000-0000-000000000000";
		
		echo "--".$tenant."--\n";
		
		//TODO: validate groupsToAssign against graphAPI
		
		$servicePrincipal = $app->OAuth2->provider->get($tenant.'/servicePrincipals?$filter='.urlencode("appId eq '".$app->OAuth2->provider->getClientId()."'"), $app->OAuth2->token)[0];
		$assignments = $app->OAuth2->provider->getObjects($tenant, "/servicePrincipals/".$servicePrincipal['objectId']."/appRoleAssignedTo", $app->OAuth2->token);
		
		$directoryRoles = $app->OAuth2->provider->get($tenant."/directoryRoles", $app->OAuth2->token);
		$companyAdministratorId = null;
		foreach($directoryRoles as $role) {
			if($role['displayName'] == "Company Administrator") {
				$companyAdministratorId = $role['objectId'];
				break;
			}
		}
		$globalAdministrators = $app->OAuth2->provider->get($tenant."/directoryRoles/".$companyAdministratorId."/members", $app->OAuth2->token);
		$adminsToAssign = [];
		foreach($globalAdministrators as $user) {
			if($user['objectType'] == "User")	$adminsToAssign[] = $user['objectId'];
		}
		
		$usersToAssign = [];
		if($everyone) {
			$users = $app->OAuth2->provider->getObjects($tenant, "/users", $app->OAuth2->token);
			foreach($users as $key=>$user) {
				$isUserAdmin = array_search($user['objectId'], $adminsToAssign);
				if($isUserAdmin === FALSE) {
					$usersToAssign[] = $user['objectId'];
				}
			}
		}
		
		foreach($assignments as $key=>$assignment) {
			$existingGroupAssignment = array_search($assignment['principalId'], $groupsToAssign);
			$existingAdminAssignment = array_search($assignment['principalId'], $adminsToAssign);
			$existingUserAssignment = array_search($assignment['principalId'], $usersToAssign);
			if($assignment['principalType'] == "Group" && $existingGroupAssignment !== FALSE) {
				echo "group ".$assignment['principalId']." is already assigned\n";
				unset($assignments[$key]);
				unset($groupsToAssign[$existingGroupAssignment]);
			}
			else if($assignment['principalType'] == "User" && $existingAdminAssignment !== FALSE) {
				echo "admin ".$assignment['principalId']." is already assigned\n";
				unset($assignments[$key]);
				unset($adminsToAssign[$existingAdminAssignment]);
			}
			else if($assignment['principalType'] == "User" && $existingUserAssignment !== FALSE) {
				echo "user ".$assignment['principalId']." is already assigned\n";
				unset($assignments[$key]);
				unset($usersToAssign[$existingUserAssignment]);
			}
		}
		
		foreach($adminsToAssign as $user) {
			echo "assigning admin ".$user." to application\n";
			$assignment = [
				"resourceId" => $servicePrincipal['objectId'],
				"principalId" => $user,
				"id" => $NULL_KEY
			];
			$app->OAuth2->provider->post($tenant."/users/".$user."/appRoleAssignments", $assignment, $app->OAuth2->token);
		}
		foreach($usersToAssign as $user) {
			echo "assigning user ".$user." to application\n";
			$assignment = [
				"resourceId" => $servicePrincipal['objectId'],
				"principalId" => $user,
				"id" => $NULL_KEY
			];
			$app->OAuth2->provider->post($tenant."/users/".$user."/appRoleAssignments", $assignment, $app->OAuth2->token);
		}
		foreach($groupsToAssign as $group) {
			echo "assigning group ".$group." to application\n";
			$assignment = [
				"resourceId" => $servicePrincipal['objectId'],
				"principalId" => $group,
				"id" => $NULL_KEY
			];
			$app->OAuth2->provider->post($tenant."/groups/".$group."/appRoleAssignments", $assignment, $app->OAuth2->token);
		}
		foreach($assignments as $assignment) {
			if($assignment['principalType'] == 'Group') {
				echo "removing group ".$assignment['objectId']." from assigned to application\n";
				$app->OAuth2->provider->delete($tenant."/groups/".$assignment['principalId']."/appRoleAssignments/".$assignment['objectId'], $app->OAuth2->token);
			}
			else if($assignment['principalType'] == 'User') {
				echo "removing user ".$assignment['objectId']." from assigned to application\n";
				$app->OAuth2->provider->delete($tenant."/users/".$assignment['principalId']."/appRoleAssignments/".$assignment['objectId'], $app->OAuth2->token);
			}
		}
		echo "--DONE--\n\n";
	}
	private static function getCache($url) {
		$cache = null;
		if(isset($_SESSION["API"]["cache"][$url])) $cache = $_SESSION["API"]["cache"][$url];
		if($cache) {
			if($cache->isValid()) return $cache->content;
			else {
				unset($_SESSION["API"]["cache"][$url]);
				return null;
			}
		}
		return null;
	}
	private static function setCache($url, $content, $ttl = 0) {
		$_SESSION["API"]["cache"][$url] = new cacheItem($url, $content, $ttl);
	}
}
?>