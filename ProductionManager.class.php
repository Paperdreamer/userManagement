<?php
	require_once("../userManagement/config.php");

	class ProductionManager {
		private $DB;

		public function __construct ($DB) {
			$this->DB = $DB;
		}

		public function projectExists($projectID) {
			if ($this->getProject($projectID)) {
				return true;
			} else {
				return false;
			}
		}

		public function updateProject ($projectID, $data) {
			$canvasManager = new CanvasManager($this->DB);
			// TODO check for permission
			$this->updateProjectMetaData($projectID, $data);
			foreach ($data["Panels"] as $panel) {
				$canvasManager->updatePanel($projectID, $panel);
				// TODO: Prevent manipulating assets that are assigned to another project...
				$canvasManager->updateAssets($panel["ID"], $panel["Assets"]);
			}
		}

		public function updateProjectMetaData ($projectID, $data) {
			$parameters = Array();
			$parameters[":ID"] = $projectID;
			$parameters[":Name"] = $data["Name"];
			$parameters[":Description"] = $data["Description"];

			return $this->DB->query("UPDATE " . PROJECT_TABLE . " SET Name = :Name, Description = :Description WHERE ID = :ID", $parameters);
		}
		
		public function deleteProject ($projectID) {
			// TODO: Warning! Remove related data, like uploaded images and so on?
			$parameters = Array();
			$parameters[":id"] = $projectID;

			return $this->DB->query("DELETE FROM " . PROJECT_TABLE . " WHERE ID = :id", $parameters);
		}
		
		public function createProject($name, $description) {
			$parameters = Array();
			$parameters[":name"] = $name;
			
			if (!array_values($this->DB->getList("SELECT EXISTS(SELECT * FROM " . PROJECT_TABLE . " WHERE Name = :name)", $parameters))[0])
				return false;
			$parameters[":description"] = $description;
			
			if (!$this->DB->query("INSERT INTO " . PROJECT_TABLE . " (Name, Description, Approved) VALUES (:name, :description, 0)", $parameters))
				return false;
			
			return $this->DB->getLastInsertId();
		}
		
		public function updateProject($id, $name, $description) {
			$parameters = Array();
			$parameters[":projectID"] = $id;
			$parameters[":name"] = $name;
			$parameters[":description"] = $description;
			
			$this->DB->query("UPDATE " . PROJECT_TABLE . " SET Name = :name, Description = :description WHERE ID = :projectID", $parameters);
		}

		public function openProject($projectID) {
			$parameters = Array();
			$parameters[":projectID"] = $projectID;
			return $this->DB->query("UPDATE " . PROJECT_TABLE . " SET Approved = 0 WHERE ID = :projectID", $parameters);
		}
		
		public function closeProject($projectID) {
			$parameters = Array();
			$parameters[":projectID"] = $projectID;
			return $this->DB->query("UPDATE " . PROJECT_TABLE . " SET Approved = 1 WHERE ID = :projectID", $parameters);
		}

		public function getProjectUsers ($projectID) {
			$parameters = Array();
			$parameters[":projectID"] = $projectID;

			return $this->DB->getList("SELECT UserID, Role, Name, Fullname FROM " . USERSINPROJECTS_TABLE . " JOIN " . USER_TABLE . " ON " . USERSINPROJECTS_TABLE . ".UserID = " . USER_TABLE . ".ID " . 
			                          "WHERE ProjectID = :projectID", $parameters);
		}

		public function addUser2Project ($userID, $projectID, $role) {
			$parameters = Array();
			$parameters[":userID"] = $userID;
			$parameters[":projectID"] = $projectID;

			if (is_array($this->DB->getRow("SELECT * FROM " . USERSINPROJECTS_TABLE . " WHERE UserID = :userID AND ProjectID = :projectID", $parameters))) {
				changeUserRole($userID, $projectID, $role);
			} else {
				$parameters[":role"] = $role;
				$this->DB->query("INSERT INTO " . USERSINPROJECTS_TABLE . " (UserID, ProjectID, Role) VALUES (:userID, :projectID, :role)", $parameters);
			}
		}

		public function changeUserRole ($userID, $projectID, $role) {
			$parameters = Array();
			$parameters[":userID"] = $userID;
			$parameters[":projectID"] = $projectID;
			$parameters[":role"] = $role;

			$this->DB->query("UPDATE " . USERSINPROJECTS_TABLE . " SET Role = :role WHERE UserID = :userID AND ProjectID = :projectID", $parameters);
		}

		public function removeUserFromProject ($userID, $projectID) {
			$parameters = Array();
			$parameters[":userID"] = $userID;
			$parameters[":projectID"] = $projectID;

			$this->DB->query("DELETE FROM " . USERSINPROJECTS_TABLE . " WHERE UserID = :userID AND ProjectID = :projectID");
		}
		
		public function removeAllUsersFromProject ($projectID) {
			$parameters = Array();
			$parameters[":projectID"] = $projectID;

			$this->DB->query("DELETE FROM " . USERSINPROJECTS_TABLE . " WHERE ProjectID = :projectID", $parameters);
		}

		public function getProject ($projectID) {
			$parameters = array();
			$parameters[":ProjectID"] = $projectID;

			return $this->DB->getRow("SELECT * FROM " . PROJECT_TABLE . " WHERE ID = :ProjectID", $parameters);
		}

		public function getAllProjects () {
			return $this->DB->getList("SELECT * FROM " . PROJECT_TABLE);
		}
		
		// Gets the projects the user is associated with and the roles he/she has
		public function getBelongedProjects ($userID) {
			$parameters = Array();
			$parameters[":userID"] = $userID;
			return $this->DB->getList("SELECT " . PROJECT_TABLE . ".*, " . USERSINPROJECTS_TABLE . ".Role FROM " . PROJECT_TABLE . " JOIN " . USERSINPROJECTS_TABLE . " ON Projects.ID = " . USERSINPROJECTS_TABLE . ".ProjectID WHERE UserID = :userID", $parameters);
		}
	}
?>
