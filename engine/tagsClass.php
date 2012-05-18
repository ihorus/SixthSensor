<?php
class Tag {
	public $uid;
	public $serial;
	public $datetime_entered;
	public $enabled;
	public $status;

	public $username;
	protected static $table_name = "tags";
	
	public static function find_by_sql($sql="") {
		global $database;
		
		$result_set = $database->query($sql);
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
			global $database;
			$object_array[] = self::instantiate($row);
		}
		return $object_array;
	}
	
	private static function instantiate($record) {
		
	$object = new self;
		foreach ($record as $attribute=>$value) {
			if ($object->has_attribute($attribute)) {
				$object->$attribute = $value;
			}
		}
		return $object;
	}
	
	private function has_attribute($attribute) {
		// get_object_vars returns as associative array with all attributes
		// (incl. private ones!) as the keys and their current values as the value
		$object_vars = $this->attributes($this) ;
		
		// we don't care about the value, we just want to know if the key exists
		// will return true or false
		return array_key_exists($attribute, $object_vars);
	}
	
	private function attributes($attribute) {
		return get_object_vars($this);
	}
	
	/* --------------------------------------------- */
	
	function allTags() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function registeredTags() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE status = 'registered' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function unregisteredTags() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE status = 'unknown' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function missingTags() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE status = 'missing' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function find_by_serial() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE serial = '" . $database->escape_value($this->serial) . "' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function isRegistered() {
		// locate if the tag is registered on the system
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE serial = '" . $database->escape_value($this->serial) . "' ";
		$sql .= "LIMIT 1";
				
		$result_array = self::find_by_sql($sql);
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		if ($result) {
			if ($result->status == "registered") {			
				$returnValue = TRUE;
			} else {
				$observation = new Observations();
				$observation->observationType = "tagIn";
				$observation->logType = "logAlert";
				$observation->tagUID = $result->uid;
				$observation->sensorUID = $_GET['sensorSerial'];
				$observation->description = "Disallowed tag '" . $this->serial ."' observed";
				$observation->create();
				
				$returnValue = FALSE;
			}
		} else {
			$this->createUnregistered();
			
			$returnValue = FALSE;
		}
		
		return $returnValue;
	}
	
	function createUnregistered() {
		global $database;
		
		$observation = new Observations();
		$observation->observationType = "tagCreate";
		
		// build the SQL string
		$sql  = "INSERT INTO " . self::$table_name . " (";
		$sql .= "datetime_entered, serial, enabled, status";
		$sql .= ") VALUES ('";
		$sql .= date('Y-m-d H:i:s') . "', '";
		$sql .= $database->escape_value($this->serial) . "', '";
		$sql .= "0', '";
		$sql .= "unknown')";
					
		if ($database->query($sql)) {
			$sensor = new Sensor();
			$sensor->serial = $_GET['sensorSerial'];
			$sensor = $sensor->find_by_serial();
			
			$observation->logType = "logInfo";
			$observation->tagUID = $database->insert_id();
			$observation->sensorUID = $sensor->uid;
			$observation->description = "Tag '" . $this->serial . "' added in an unregistered state to the database from sensor serial " . $sensor->serial;
			$observation->create();
	
			return true;
		} else {
			$observation->logType = "logError";
			$observation->description = "error when adding sensor to database";
			$observation->create();

			return false;
		}
	}
	
	function assignedTags() {
		global $database;
		
		$usersWithTags = User::usersWithTags();
		foreach ($usersWithTags AS $user) {
			$tagUIDS[] = "'" . $user->tagUID . "'";
		}
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid IN (" . implode(", ", $tagUIDS) . ") ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function unassignedTags() {
		global $database;
		
		$usersWithTags = User::usersWithTags();
		foreach ($usersWithTags AS $user) {
			$tagUIDS[] = "'" . $user->tagUID . "'";
		}
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid NOT IN (" . implode(", ", $tagUIDS) . ") ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function lastObservedDate() {
		$observation = new Observations();
		$observation->tagUID = $this->uid;
		
		$observedDate = $observation->lastObservedDateByTag();
		
		if ($observedDate) {
			return date('U', strtotime($observedDate));
		} else {
			return false;
		}
	}
	
	function lastAssignedDate() {
		$observation = new Observations();
		$observation->tagUID = $this->uid;
		
		$observedDate = $observation->lastAssignedDateByTag();
		
		if ($observedDate) {
			return date('U', strtotime($observedDate));
		} else {
			return false;
		}
	}
	
	function assigned() {
		global $database;
		
		$user = new User();
		$user->tagUID = $this->uid;
		$user = $user->findByTagUID();
		
		if ($user) {
			return true;
		} else {
			return false;
		}
	}
	
	function findbyUID() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $this->uid . "' ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function findByForm($form = NULL) {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		
		if ($form) {
			$usersInForm = new User;
			$usersInForm->form = $form;
			$usersInForm = $usersInForm->allUsersByForm();
			
			foreach ($usersInForm AS $user) {
				$tagUIDsArray[] = "'" . $user->tagUID . "'";
			}
			
			$sql .= "WHERE uid IN (" . implode(",", $tagUIDsArray) . ") ";
			$sql .= "ORDER BY serial DESC";
			
			$result_array = self::find_by_sql($sql);
		}
			
		return $result_array;
	}
	
	function displayBlock($displayLogs = TRUE) {	
		global $session;
				
		$observations = new Observations();
		$observations->tagUID = $this->uid;
		$content = "";
		$meta = ucfirst($this->status);
		
		// check if this tag is unregistered
		if ($this->status == "registered") {
			$tagSerial = $this->serial;
			
			// check if this tag is unassigned
			if (!self::assigned() == TRUE) {
				$meta = "Unassigned";
			} else {
				// we have a date stamp, display it with it's age
				$meta = "Assigned: " . dateDisplay($this->lastAssignedDate(), TRUE);
			}
		} else {
			// display 'register this tag' option
			$dropdownStatus = " disabled";
			$content .= "<div id=\"" . $this->uid . "\" class=\"registerTagButton\">";
			$content .= "Click here to register this tag on the system";
			$content .= "</div>";
			
			$dropdownStatus = "";
		}
		
		// fetch users ready for dropdown selection
		$dropdownStatus = " ";
		$content .= "<p>Assign Tag To: ";
		
		$elementID = $this->uid . "_yearSelect";
		$elementClass = "yearSelect";
		$content .= "<select id=\"" . $elementID . "\" class=\"" . $elementClass . "\">";
		$content .= optionDropDown("", "", "");
		
		foreach (scholasticYears() AS $value => $year) {
			$fullYearName = "Entry " . $year . " (" . $value . ")";
			$content .= optionDropDown($fullYearName, $fullYearName, "");
			}
		
		$content .= "</select>";
		
		$elementID = $this->uid . "_assignTagUser";
		$elementClass = "assignTagUser";
		$content .= "<select " . $dropdownStatus . " id=\"" . $elementID . "\" class=\"" . $elementClass . "\">";
		$content .= "</select>";
		
		if ($displayLogs) {
			$content .= $observations->displayTagLogsByTag();
		}
				
		return makeBlock("tag", $this->serial, $meta, $content);
	}
	
	function assignTag() {
		global $database;
		global $session;
		
		$sql  = "SELECT * FROM users WHERE ";
		$sql .= "username = '" . $database->escape_value($this->username) . "'";
		
		$result_array = self::find_by_sql($sql);
		
		$tagSerial = new Tag();
		$tagSerial->uid = $this->uid;
		$tagSerial = $tagSerial->findByUID();
		
		
		// check if this tag is registered, if not - register it!
		if($tagSerial->status != "registerd") {
			$tagSerial->registerTag();
		}
		
		// check if this tag is already assigned to someone else, if so, unassign it from them
		$this->unassignTag();
			
		if (count($result_array) == 0) {
			// user doesn't exist on the database yet - create them		
			$user = new User();
			$user->username = $this->username;
			$user->tagUID = $this->uid;
			$user->active = 1;
			$user->create();
			
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->tagUID = $this->uid;
			$observation->logType = "logInfo";
			$observation->observationType = "tagAssign";
			$observation->description = $session->serverUsername() . " assigned tag '" . $tagSerial->serial . "' to " . $this->username;
			$observation->create();
		} else {
			// user already exists in database, update them
			$this->unassignTag();
			
			$sqlUpdate  = "UPDATE users set ";
			$sqlUpdate .= "tagUID = '" . $this->uid . "' ";
			$sqlUpdate .= "WHERE username = '" . $database->escape_value($this->username) . "'";
			
			// insert the record to the database
			if ($database->query($sqlUpdate)) {
				$observation = new Observations();
				$observation->username = $session->serverUsername();
				$observation->tagUID = $this->uid;
				$observation->logType = "logInfo";
				$observation->observationType = "tagAssign";
				$observation->description = $session->serverUsername() . " assigned tag '" . $tagSerial->serial . "' to " . $this->username;
				$observation->create();
			}
		}
	}
	
	function unassignTag() {
		global $database;
		
		$sqlUpdate  = "UPDATE users set ";
		$sqlUpdate .= "tagUID = '' ";
		$sqlUpdate .= "WHERE tagUID = '" . $database->escape_value($this->uid) . "'";
				
		$database->query($sqlUpdate);
		
		return TRUE;
	}
	
	function registerTag() {
		global $database;
		global $session;
		
		$sqlUpdate  = "UPDATE " . self::$table_name . " set ";
		$sqlUpdate .= "status = 'registered', ";
		$sqlUpdate .= "enabled = '1' ";
		$sqlUpdate .= "WHERE uid = '" . $database->escape_value($this->uid) . "'";
		
		// insert the record to the database
		if ($database->query($sqlUpdate)) {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->tagUID = $this->uid;
			$observation->logType = "logInfo";
			$observation->observationType = "system";
			$observation->description = $session->serverUsername() . " registered tag '" . $this->serial . "'";
			$observation->create();
		} else {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->tagUID = $this->uid;
			$observation->logType = "logError";
			$observation->observationType = "tagRegister";
			$observation->description = $this->username . " attempted to register tag '" . $this->serial . "' (UID: " . $this->uid . ")";
			$observation->create();
		}
	}
	
	function update() {
		global $database;
		global $session;
		
		$sqlUpdate  = "UPDATE " . self::$table_name . " set ";
		$sqlUpdate .= "enabled = '" . $this->enabled . "', ";
		$sqlUpdate .= "status = '" . $this->status . "' ";
		$sqlUpdate .= "WHERE uid = '" . $database->escape_value($this->uid) . "'";
		
		// insert the record to the database
		if ($database->query($sqlUpdate)) {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->sensorUID = $this->uid;
			$observation->logType = "logInfo";
			$observation->observationType = "system";
			$observation->description = $session->serverUsername() . " updated tag '" . $this->serial . "' ";
			$observation->create();
		}
	}
} // end of userClass
?>