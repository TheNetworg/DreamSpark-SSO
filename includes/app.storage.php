<?php
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Table\Models\Entity;
use WindowsAzure\Table\Models\EdmType;

class appStorage {
	static $tableRestProxy;
	static $table = "organizationSettings";
	
	static $settings;
	
	public static function connect() {
		if(!self::$tableRestProxy) self::$tableRestProxy = ServicesBuilder::getInstance()->createTableService(getEnv('CUSTOMCONNSTR_Storage'));
	}
	private function generateUuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
	}
	public static function getSettings($tenant) {
		if(self::$settings) return self::$settings;
		else {
			try {
				$result = self::$tableRestProxy->getEntity(self::$table, $tenant, 'settings');
			}
			catch(ServiceException $e) {
				$code = $e->getCode();
				if($code == 404) return null;
				else {
					$error_message = $e->getMessage();
					$app->error("Something went wrong. Please try again later.");
				}
			}
			$entity = $result->getEntity();
			self::$settings = $entity;
			return $entity;
		}
	}
	public static function getAllSettings() {
		try {
			$result = self::$tableRestProxy->queryEntities(self::$table, "RowKey eq 'settings'");
		}
		catch(ServiceException $e) {
			$code = $e->getCode();
			if($code == 404) return null;
			else {
				$error_message = $e->getMessage();
				echo "ERROR: ".$error_message;
				return null;
			}
		}
		$entities = $result->getEntities();
		return $entities;
	}
	public static function setSettings($tenant, $settings) {
		$entity = new Entity();
		$entity->setPartitionKey($tenant);
		$entity->setRowKey("settings");
		foreach($settings as $name=>$value) {
			$entity->addProperty($name, null, $value);
		}
		
		try {
			self::$tableRestProxy->insertOrMergeEntity(self::$table, $entity);
		}
		catch(ServiceException $e) {
			$code = $e->getCode();
			$error_message = $e->getMessage();
			
			$app->error("Something went wrong when trying to save your configuration. Please try again later.");
		}
	}
}
?>