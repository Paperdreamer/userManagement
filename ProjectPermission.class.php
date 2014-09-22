<?php
	
	abstract class ProjectPermission {
		private static $permissions = Array("Director", "Supervisor", "Artist", "Observer");
		
		public static function atLeast ($atLeastRole, $currentRole) {
			$atLeastRole = array_search($atLeastRole, $this->permissions);
			$currentRole = array_search($currentRole, $this->permissions);
			
			return $currentRole <= $atLeastRole;
		}
		
		public static function getProjectRole($userID, $projectID) {
			$DB = Flight::DB();
			$parameters = Array();
			$parameters[":userID"] = $userID;
			$parameters[":projectID"] = $projectID;
			
			$result = $DB->getRow("SELECT Role FROM " . USERSINPROJECTS_TABLE . " WHERE UserID = :userID AND ProjectID = :projectID", $parameters);
			if(is_array($result)) {
				return array_values($result)[0];
			} else {
				return false;
			}
		}
	}
?>