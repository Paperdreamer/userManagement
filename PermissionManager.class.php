<?php

/**
 *	This class handles permissions of users in groups
 */
abstract class PermissionManager
{
	private $DB;
	private $userID;

	/**
	* Check if the provided role is allowed to perform the action associated with the permission key
	*/
	//TODO: Delete or reimplement this method
	static function check ($context, $permissionKey, $role) {
		if ($context == "team")
			return teamPermission::check($permissionKey, $role);
		else if ($context == "system")
			return systemPermission::check($permissionKey, $role);
	}
	
	//Checks if the given user is an admin
	public static function isAdmin($userID) {
		$DB = Flight::DB();
		$parameters = Array();
		$parameters[":userID"] = $userID;
		$result = $DB->getRow("SELECT * FROM " . ADMIN_TABLE . " WHERE UserID = :userID", $parameters);
		return is_array($result);
	}

	//Checks if a the left user has a higher level than right user
	public static function isSuperior($userID1, $userID2) {
		if (self::isAdmin($userID1)) {
			if (!self::isDeleteable($userID1)) {
				return true;
			} else {
				return !self::isAdmin($userID2);
			}
		} else {
			return false;
		}
	}
	
	//Checks if an administrator is deletable
	public static function isDeleteable($userID) {
		$DB = Flight::DB();
		$parameters = Array();
		$parameters[":userID"] = $userID;
		return array_values($DB->getRow("SELECT Deleteable FROM " . ADMIN_TABLE . " WHERE UserID = :userID",$parameters))[0];
	}

}

?>